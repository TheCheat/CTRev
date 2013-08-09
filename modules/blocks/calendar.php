<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/calendar.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        $day_of_week = implode(',', array_map(array(db::o(), 'esc'), array_map('trim', explode(",", lang::o()->v('calendar_day_of_week')))));
        $months = input::$months;
        $monthes = array(); // lol'd
        foreach ($months as $month)
            $monthes [] = db::o()->esc(lang::o()->v('month_' . $month));
        $content = $this->count_content();
        tpl::o()->assign("content_count", $content);
        tpl::o()->assign('day_of_week', $day_of_week);
        tpl::o()->assign("months", implode(",", $monthes));
        tpl::o()->display("blocks/contents/calendar.tpl");
    }

    /**
     * Подсчёт кол-ва контента в данном месяце и в данном году
     * @param int $month данный месяц
     * @param int $year данный год
     * @return string JS массив кол-ва торрентов по дням
     */
    public function count_content($month = null, $year = null) {
        $month = (!$month ? date("n") : $month);
        $year = (!$year ? date("Y") : $year);
        if (!($r = cache::o()->read('calendar/c' . $month . '-' . $year))) {
            $year_after = ($month == 12 ? $year + 1 : $year);
            $month_after = ($month < 12 ? $month + 1 : 1);
            $from = mktime(null, null, null, $month, 1, $year);
            $to = mktime(null, null, null, $month_after, 1, $year_after);
            $datas = db::o()->p($from, $to)->query('SELECT posted_time FROM content 
                WHERE posted_time BETWEEN ? AND ?');
            //$count = count($datas);
            $content = array();
            while ($data = db::o()->fetch_assoc($datas)) {
                $day = date("j", $data ["posted_time"]);
                $content [$day]++;
            }
            $ncontent = "";
            for ($i = 0; $i <= 31; $i++) {
                $ncontent .= ( $ncontent !== "" ? ", " : "") . longval($content [$i]);
            }
            $r = array("new Array(" . $ncontent . ")");
            cache::o()->write($r);
        }
        return $r[0];
    }

}

?>