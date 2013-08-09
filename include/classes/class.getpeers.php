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
     * URL аннонсера
     * @var string $announce_url
     */
    protected $announce_url = "";

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
        $numwant = 50; // Кол-во пиров, которое мы хотим получить
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
        $r = n("remote", true);
        $this->remote = new $r(null, null, "uTorrent/1820");
    }

    /**
     * Посылка запроса по URL и получение результата
     * @param string $url URL
     * @param string $infohash инфохеш торрента
     * @return string контент
     */
    protected function send_request($url, $infohash) {
        $url = $url . (strpos($url, "?") ? "&" : "?") . $this->announce_url . '&info_hash=' . $infohash;
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