<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.getpeers.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс, содержащий методы для получения "левых" пиров
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class getpeers {
    
    /**
     * Объект bittorrent
     * @var bittorrent $bt 
     */
    protected $bt = null;

    /**
     * Параметры, отсылаемые аннонсеру
     * @var string $announce_url
     */
    protected $announce_url = "";

    /**
     * Ограничение на запрос по времени в секундах
     * По-умолчанию DEFAULT_SOCKET_TIMEOUT
     * @var int $time_limit
     */

    protected $time_limit = 0;

    /**
     * Конструктор запроса для аннонсеров
     * @return null
     */
    public function __construct() {
        $peer_id = "-UT1820-z8%ea%e9gt%eb%ad~v%0f%0b";
        $port = rand(40000, 50000);
        $uploaded = 0;
        $downloaded = 0;
        $left = 1024; // Килобайт остался, ага
        $corrupt = 0;
        $key = "79202A30";
        $numwant = 200; // Кол-во пиров, которое мы хотим получить
        $compact = 1; // Чтобы быстрее. И некоторые движки не поддерживают др. режим.
        $no_peer_id = 1;
        $event = "started";
        $announce_url = "peer_id=" . $peer_id;
        $announce_url .= "&port=" . $port;
        $announce_url .= "&uploaded=" . $uploaded;
        $announce_url .= "&downloaded=" . $downloaded;
        $announce_url .= "&left=" . $left;
        $announce_url .= "&corrupt=" . $corrupt;
        $announce_url .= "&key=" . $key;
        $announce_url .= "&numwant=" . $numwant;
        $announce_url .= "&compact=" . $compact;
        $announce_url .= "&no_peer_id=" . $no_peer_id;
        $announce_url .= "&event=" . $event;
        $this->announce_url = $announce_url;
        $this->bt = n("bittorrent");
        if (!$this->time_limit)
            $this->time_limit = DEFAULT_SOCKET_TIMEOUT;
            
    }

    /**
     * Получение контента страницы посредством сокетов
     * @param string $url URL страницы
     * @param string $ua User-Agent
     * @return string контент страницы
     */
    public function content_via_sockets($url, $ua = "uTorrent/1820") {
        $p = parse_url($url);
        $host = $p['host'];
        $path = $p['path'];
        if (!$path)
            $path = "/";
        $port = $p['port'];
        if (!$port) {
            switch ($p['scheme']) {
                case "https":
                    $port = 443;
                    $host = "ssl://" . $host;
                    break;
                case "udp":
                    $port = 13;
                    $host = "udp://" . $host;
                    break;
                default:
                    $port = 80;
                    break;
            }
        }
        $query = $p['query'];
        $r = @fsockopen($host, $port, $errno, $errstr, $this->time_limit);
        if (!$r)
            return;
        $out = "GET " . $path . "?" . $query . " HTTP/1.1\r\n";
        $out .= "Host: " . $host . "\r\n";
        $out .= "User-Agent: " . $ua . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if (!@fwrite($r, $out))
            return; // для UDP
        $c = '';
        while (!feof($r))
            $c .= fgets($r, 1024);
        return $this->parse_headers($c);
    }

    /**
     * Обрезание заголовков и парсинг чанков
     * @param string $c контент с заголовками
     * @return string спарсенный контент
     */
    protected function parse_headers($c) {
        $hend = "\r\n\r\n";
        $p = strpos($c, $hend);
        $h = substr($c, 0, $p);
        $c = trim(substr($c, $p + strlen($hend)));
        if (preg_match('/Transfer-Encoding\:\s*chunked\r\n/siu', $h)) {
            $r = '';
            $nl = "\r\n";
            $nll = strlen($nl);
            do {
                $p = strpos($c, $nl);
                $clen = hexdec(substr($c, 0, $p));
                $p += $nll;
                $r .= substr($c, $p, $clen);
                $c = substr($c, $p + $clen + 2); // + 2 ибо перевод на новую строку
                if (!$clen)
                    break;
            } while ($c);
            $c = $r;
        }
        return $c;
    }

    /**
     * Посылка запроса по URL и получение результата
     * @param string $url URL
     * @param string $infohash инфохеш торрента
     * @return string контент
     */
    protected function send_request($url, $infohash) {
        $url = $url . (strpos($url, "?") ? "&" : "?") . $this->announce_url . '&info_hash=' . $infohash;
        $ua = "uTorrent/1820";
        if (function_exists("curl_init")) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->time_limit);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->time_limit);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $ua);

            @$r = curl_exec($ch);

            curl_close($ch);
        } else
            $r = $this->content_via_sockets($url, $ua);
        return $r;
    }

    /**
     * Получение списка пиров
     * @param int $tid ID торрента
     * @param string $announces сериализованный массив аннонсеров
     * @param string $infohash инфохеш торрента
     * @param bool $update обновить?
     * @return array массив полученной статистики по трекерам
     */
    public function get_peers($tid, $announces, $infohash, $update = true) {
        $announces = unserialize($announces);
        $tid = (int) $tid;
        if (!$announces || !is_array($announces))
            return;
        $peers = null;
        $infohash = urlencode(pack("H*", $infohash));
        $stat = array();
        foreach ($announces as $announce) {
            $peers = 0;
            $scrape = str_replace('/announce', '/scrape', $announce);
            $c = '';
            if ($scrape != $announce && $c = $this->send_request($scrape, $infohash))
                $peers = $this->parse_scrape($c);
            if (!$peers) {
                $c = $this->send_request($announce, $infohash);
                $peers = $this->parse_announcer($c);
            }
            $stat[$announce] = $peers;
        }
        $stat['last_update'] = time();
        if ($update)
            db::o()->update(array('announce_stat' => serialize($stat)), 'torrents', 'WHERE id=' . $tid . ' LIMIT 1');
        return $stat;
    }

    /**
     * Парсинг аннонсера
     * @param string $content контент аннонсера
     * @return int|array кол-во пиров, либо массив из кол-ва сидов и личеров
     */
    protected function parse_announcer($content) {
        $c = $this->bt->bdec($content);
        if (!is_array($c))
            return 0;
        if (isset($c["complete"]) && isset($c["incomplete"]))
            return array((int) $c["complete"], (int) $c["incomplete"]);
        if (is_array($c['peers']))
            return count($c['peers']);
        else
            return (int) (strlen($c['peers']) / 6);
    }

    /**
     * Парсинг скрейпа
     * @param string $content контент скрейпа
     * @return array массив из кол-ва сидов и личеров
     */
    protected function parse_scrape($content) {
        $c = $this->bt->bdec($content);
        if (!is_array($c))
            return 0;
        if (isset($c["complete"]) && isset($c["incomplete"]))
            return array((int) $c["complete"], (int) $c["incomplete"]);
    }

}

?>