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
     * Объект remote
     * @var remote $remote
     */
    protected $remote = null;

    /**
     * Данные для аннонсера
     * @var array $data
     */
    protected $data = array();

    /**
     * URL аннонсера
     * @var string $announce_url
     */
    protected $announce_url = "";

    /**
     * Конструктор запроса для аннонсеров
     * @return null
     */
    public function __construct() {
        $this->data["peer_id"] = "-UT1820-z8\xea\xe9gt\xeb\xad~v\x0f\x0b";
        $this->data["downloaded"] = 0;
        $this->data["left"] = 1024; // Килобайт остался, ага
        $this->data["uploaded"] = 0;
        $this->data["event"] = "started";
        $this->data["key"] = 7920230;
        $this->data["numwant"] = 50; // Кол-во пиров, которое мы хотим получить
        $this->data["port"] = rand(40000, 50000);
        $this->data["corrupt"] = 0;
        $this->data["compact"] = 1; // Чтобы быстрее. И некоторые движки не поддерживают др. режим.
        $this->data["no_peer_id"] = 1;
        $this->announce_url = http_build_query($this->data);
        $this->data["connect_id"] = "\x00\x00\x04\x17\x27\x10\x19\x80";
        $this->bt = n("bittorrent");
        $r = n("remote", true);
        $this->remote = new $r(null, null, "uTorrent/1820");
    }

    /**
     * Попытка получения списка через скрейп
     * @param resid $r ID запроса
     * @param string $connect_id ID соединения
     * @param int $transact_id ID транзакции
     * @param string $infohash инфохеш торрента
     * @return bool|array false в случае ошибки, массив из кол-ва сидеров и личеров
     * в случае удачи
     */
    protected function udp_scrape($r, $connect_id, $transact_id, $infohash) {
        $scrape = $connect_id . pack('N2', 2, $transact_id) . $infohash;
        if (!@fwrite($r, $scrape))
            return false;
        $ret = fread($r, 20);
        @$ret = unpack("N5", $ret);
        if (!$ret || $ret[1] != 2 || $ret[2] != $transact_id)
            return false;
        return array((int) $ret[3], (int) $ret[5]);
    }

    /**
     * Попытка получения списка через аннонсер
     * @param resid $r ID запроса
     * @param string $connect_id ID соединения
     * @param int $transact_id ID транзакции
     * @param string $infohash инфохеш торрента
     * @return array массив из кол-ва сидеров и личеров
     */
    protected function udp_announce($r, $connect_id, $transact_id, $infohash) {
        $announce = $connect_id . pack('N2', 1, $transact_id) . $infohash . $this->data['peer_id'];
        $announce .= pack('N10n', 0, 0, 0, $this->data['left'], 0, 0, 2, 0, $this->data['key'], $this->data['numwant'], $this->data['port']);
        if (!@fwrite($r, $announce))
            return false;
        $ret = fread($r, 20);
        @$ret = unpack("N5", $ret);
        if (!$ret || $ret[1] != 1 || $ret[2] != $transact_id)
            return false;
        return array((int) $ret[5], (int) $ret[4]);
    }

    /**
     * Получение статистики по протоколу UDP
     * @param string $url URL страницы
     * @param string $infohash инфохеш торрента
     * @return int|array данные о пирах
     * @see http://www.bittorrent.org/beps/bep_0015.html
     */
    protected function udp_peers($url, $infohash) {
        $p = parse_url($url);
        $host = $p['host'];
        $port = $p['port'];
        if (!$port)
            $port = 2710;
        $r = fsockopen('udp://' . $host, $port, $errno, $errstr, DEFAULT_SOCKET_TIMEOUT);
        if (!$r)
            return 0;
        $transact_id = rand(0, 32000);
        $connect = $this->data["connect_id"] . pack('N2', 0, $transact_id);
        if (!@fwrite($r, $connect))
            return 0;
        $resp = fread($r, 16);
        @$resp = unpack("N4", $resp);
        if (!$resp || $resp[2] != $transact_id)
            return 0;
        $connect_id = pack("N2", $resp[3], $resp[4]); // присвоенный
        if (($ret = $this->udp_scrape($r, $connect_id, $transact_id, $infohash)) == false)
            $ret = $this->udp_announce($r, $connect_id, $transact_id, $infohash);
        fclose($r);
        return $ret;
    }

    /**
     * Посылка запроса по URL и получение результата
     * @param string $url URL
     * @param string $infohash инфохеш торрента
     * @return string контент
     */
    protected function send_request($url, $infohash) {
        $url = $url . (strpos($url, "?") ? "&" : "?") . $this->announce_url . '&info_hash=' . urlencode($infohash);
        $r = $this->remote->send_request($url);
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
            return array();
        $peers = null;
        $infohash = pack("H*", $infohash);
        $stat = array();
        foreach ($announces as $announce) {
            $peers = 0;
            if (strpos($announce, "udp://") === 0)
                $peers = $this->udp_peers($announce, $infohash);
            else {
                $scrape = str_replace('/announce', '/scrape', $announce);
                $c = '';
                if ($scrape != $announce && $c = $this->send_request($scrape, $infohash))
                    $peers = $this->parse_scrape($c);
                if (!$peers) {
                    $c = $this->send_request($announce, $infohash);
                    $peers = $this->parse_announcer($c);
                }
            }
            $stat[$announce] = $peers;
        }
        $stat['last_update'] = time();
        if ($update)
            db::o()->p($tid)->update(array('announce_stat' => serialize($stat)), 'content_torrents', 'WHERE cid=? LIMIT 1');
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
     * @return array|int массив из кол-ва сидов и личеров или 0
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