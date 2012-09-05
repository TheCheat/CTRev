<?php

/**
 * Project:             CTRev
 * File:                calendar.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок календарь
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class calendar_block {

    /**
     * Инициализация блока календаря
     * @global tpl $tpl
     * @global lang $lang
     * @global db $db
     * @return null
     */
    public function init() {
        global $tpl, $lang, $db;
        $day_of_week = implode(',', array_map(array($db, 'esc'), array_map('trim', explode(",", $lang->v('calendar_day_of_week')))));
        $months = input::$months;
        $monthes = array(); // lol'd
        foreach ($months as $month)
            $monthes [] = $db->esc($lang->v('month_' . $month));
        $torrents = $this->count_torrents();
        $tpl->assign("torrents_count", $torrents);
        $tpl->assign('day_of_week', $day_of_week);
        $tpl->assign("months", implode(",", $monthes));
        $tpl->display("blocks/contents/calendar.tpl");
    }

    /**
     * Подсчёт кол-ва торрентов в данном месяце и в данном году
     * @global db $db
     * @global display $display
     * @global cache $cache
     * @param int $month данный месяц
     * @param int $year данный год
     * @return string JS массив кол-ва торрентов по дням
     */
    public function count_torrents($month = null, $year = null) {
        global $db, $display, $cache;
        $month = (!$month ? date("n") : $month);
        $year = (!$year ? date("Y") : $year);
        if (!($r = $cache->read('calendar/c' . $month . '-' . $year))) {
            $year_after = ($month == 12 ? $year + 1 : $year);
            $month_after = ($month < 12 ? $month + 1 : 1);
            $from = mktime(null, null, null, $month, 1, $year);
            $to = mktime(null, null, null, $month_after, 1, $year_after);
            $datas = $db->query('SELECT posted_time FROM torrents WHERE posted_time BETWEEN ' . $from . ' AND ' . $to);
            //$count = count($datas);
            $torrents = array();
            while ($data = $db->fetch_assoc($datas)) {
                $day = date("j", $data ["posted_time"]);
                $torrents [$day]++;
            }
            $ntorrents = "";
            for ($i = 0; $i <= 31; $i++) {
                $ntorrents .= ( $ntorrents !== "" ? ", " : "") . longval($torrents [$i]);
            }
            $r = array("new Array(" . $ntorrents . ")");
            $cache->write($r);
        }
        return $r[0];
    }

}

?>