<?php

/**
 * Project:             CTRev
 * @file                /scrape.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Скрейп
 * @version             1.00
 */
require_once './include/include_announce.php';
$info_hash = $_GET['info_hash'];
if (!$info_hash)
    $bt->err('Multi-scrape denied!');
$infohash = bin2hex($info_hash);
$r = db::o()->p($infohash)->query('SELECT seeders, leechers, downloaded 
    FROM content_torrents WHERE info_hash=? LIMIT 1');
$row = db::o()->fetch_assoc($r);
if (!$row)
    $bt->err('Unknown torrent. Infohash - ' . $infohash);
$bt->benc_resp_raw($bt->benc(array('files' => array(
                $info_hash => array('complete' => (int) $row['seeders'],
                    'downloaded' => (int) $row['downloaded'],
                    'incomplete' => (int) $row['leechers'])))));
?>