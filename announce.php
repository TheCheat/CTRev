<?php

/**
 * Project:             CTRev
 * File:                announce.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Аннонсер
 * @version             1.00
 */
require_once './include/include_announce.php';

require_once ROOT . '/include/classes/class.etc.php';
$etc = new etc();
$a = array("passkey", "peer_id", "port", "uploaded", "ip",
    "left", "compact", "event", "info_hash", "no_peer_id");
$c = count($a);
extract(rex($_REQUEST, $a));
if ($_REQUEST['num want'])
    $_REQUEST['numwant'] = $_REQUEST['num want'];
if ($_REQUEST['num_want'])
    $_REQUEST['numwant'] = $_REQUEST['num_want'];
$numwant = (int) $_REQUEST['numwant'];
$ip = ip2ulong($ip ? $ip : $_SERVER ['REMOTE_ADDR']);
if (!$ip)
    $bt->err('Invalid IP.');
$info_hash = bin2hex($info_hash);
$seeder = $left > 0 ? '0' : '1';
$area = $seeder ? 'seeders' : 'leechers';

//$bt->err('Unknown user. Passkey - ' . print_r($_SERVER, true));

$itime = $config->v('announce_interval') * 60;
if (!$itime)
    $bt->err('There\'s not an announce interval o_O.');

list($torrent, $seedleech,
        $downloaded) = $db->fetch_row($db->query('SELECT id, ' . $area . ',downloaded FROM torrents WHERE
    info_hash=' . $db->esc($info_hash) . ' AND banned="0" LIMIT 1'));
if (!$torrent)
    $bt->err('Unknown torrent. Infohash - ' . $info_hash);
list($user) = $db->fetch_row($db->query('SELECT id FROM users WHERE
    passkey=' . $db->esc($passkey) . ' AND `group`>0 LIMIT 1'));
$where = "WHERE tid=" . $torrent . ' AND uid=' . $user . " LIMIT 1";
if (!$user)
    $bt->err('Unknown user. Passkey - ' . print_r($_REQUEST, true));

if (!$numwant)
    $numwant = 50;

$r = $db->query('SELECT peer_id, ip, port, uid, uploaded, time FROM peers WHERE tid=' . $torrent . ($numwant ? ' LIMIT ' . $numwant : ""));
$was = false;
$seeders = 0;
$leechers = 0;
if ($event != 'stopped') {
    $plist = $compact ? '' : array();
    while ($peer = $db->fetch_assoc($r)) {
        $peer["ip"] = long2ip($peer["ip"]);
        if ($peer['seeder'])
            $seeders++;
        else
            $leechers++;
        if ($peer["uid"] == $user) {
            peer_bonus($uploaded - $peer["uploaded"], $peer["time"], $user);
            $was = true;
            continue;
        }
        if ($compact) {
            $peer_ip = explode('.', $peer["ip"]);
            $plist .= pack("C*", $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3]) . pack("n*", (int) $peer["port"]);
        } else {
            $arr = array('ip' => $peer['ip'], 'port' => (int) $peer['port']);
            if (!$no_peer_id)
                $plist[] = $bt->benc($arr);
        }
    }
} else
    $numwant = 1;

if (!$was && $numwant) {
    list($was, $puploaded, $time) = $db->fetch_row($db->query('SELECT uid, uploaded, time FROM peers ' . $where));
    if ($was)
        peer_bonus($uploaded - $puploaded, $time, $user);
}

if ($event == 'completed') {
    $db->update(array('finished' => '1'), 'downloaded', $where);
    if ($db->affected_rows())
        $db->update(array('last_active' => time(),
            'downloaded' => (string) ($downloaded + 1)), 'torrents', 'WHERE id=' . $torrent . ' LIMIT 1');
}

if ($event == 'stopped' && $was) {
    $db->delete('peers', $where);
    if ($seedleech > 0)
        $db->update(array('last_active' => time(),
            $area => (string) ($seedleech - 1)), 'torrents', 'WHERE id=' . $torrent . ' LIMIT 1');
} elseif ($event != 'stopped') {
    $update = array(
        'peer_id' => $peer_id,
        'ip' => $ip,
        'port' => $port,
        'uploaded' => $uploaded,
        'seeder' => $seeder,
        'time' => time());
    if ($was)
        $db->update($update, "peers", $where);
    else {
        $update["tid"] = $torrent;
        $update["uid"] = $user;
        $db->insert($update, "peers");
        $db->update(array('last_active' => time(),
            $area => (string) ($seedleech + 1)), 'torrents', 'WHERE id=' . $torrent . ' LIMIT 1');
    }
}

if ($event != 'stopped')
    $bt->benc_resp_raw($bt->benc(
                    array('interval' => $itime,
                        'min interval' => $itime,
                        'complete' => $seeders,
                        'incomplete' => $leechers,
                        'peers' => $plist)));
?>