<?php

/**
 * Project:             CTRev
 * File:                cyberhype.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Методы получения и обработки данных
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

require_once ROOT . 'include/classes/class.display.php';
require_once ROOT . 'include/classes/class.fbenc.php';
require_once ROOT . 'include/classes/class.bittorrent.php';
require_once ROOT . 'include/classes/class.users.php';

class get_convert {

    /**
     * Конвертируемая база данных
     * @var string $db
     */
    private $db = 'cyberhype';

    /**
     * Сопоставление групп
     * @var array $groups
     */
    private $groups = array();

    /**
     * Конструктор класса
     * @param string $db имя БД
     * @param array $groups список групп
     * @return null
     */
    public function __construct($db, $groups) {
        $this->db = $db;
        $this->groups = (array) $groups;
    }

    /**
     * Получение ID подкатегории
     * @param int $id старый ID подкатегории
     * @return int новый ID подкатегории
     */
    public function get_incatid($id) {
        if (!($max = stats::o()->read(convert::stfield)))
            stats::o()->write(convert::stfield, ($max = db::o()->act_row('categories', 'id', 'MAX')));
        return $max + $id;
    }

    /**
     * Изменение имени файла торрента в соотв. с паттерном CTRev
     * @param array $row массив данных торрента
     * @return int ID торрента
     */
    public function get_tfile($row) {
        $fname = bittorrent::get_filename($row['posted_time'], $row['poster_id']);
        $path = ROOT . config::o()->v('torrents_folder') . '/';
        $nname = $path . bittorrent::torrent_prefix . $fname . ".torrent";
        $oname = $path . $row['id'] . ".torrent";
        if (!file_exists($nname) && file_exists($oname))
            rename($oname, $nname);
        return $row['id'];
    }

    /**
     * Получение имени ЧПУ для категории
     * @param int $id ID категории
     * @param string $name имя категории
     * @param string $sname имя категории для подкатегории
     * @return string транслитерованное имя
     */
    public function get_catname($id, $name, $sname = '') {
        return display::o()->translite($id . '-' . $name . ($sname ? "-" . $sname : ""));
    }

    /**
     * Получение списка файлов для торрента
     * @param int $id ID торрента
     * @return string список файлов
     */
    public function get_filelist($id) {
        $r = db::o()->query('SELECT filename, size FROM `' . $this->db . '`.`files` 
            WHERE torrent=' . $id . ' LIMIT 0, ' . (bittorrent::max_filelist + 1));
        $arr = db::o()->fetch2array($r, "row");
        $c = count($arr);
        if ($c > bittorrent::max_filelist)
            $arr[$c - 1] = array('...', 0);
        return serialize($arr);
    }

    /**
     * Оболочка для db::o()->count_rows в конвертере
     * @param string $table имя таблицы
     * @param string $where условие
     * @return int кол-во значений, удовл. условию
     */
    public function get_countrows($table, $where) {
        return db::o()->count_rows($table, $where);
    }

    /**
     * Получение списка скриншотов
     * @param string $poster постер
     * @param string $screenshots список скриншотов
     * @return string список скриншотов
     */
    public function get_screenshots($poster, $screenshots) {
        $screenshots = $poster . "\n" . $screenshots;
        $screenshots = explode("\n", $screenshots);
        $scrs = array();
        foreach ($screenshots as $scr) {
            $scr = trim($scr);
            if (!$scr)
                continue;
            if (preg_match('/[\/\\\]/', $scr))
                $scrs[] = $scr;
            elseif (file_exists(ROOT . config::o()->v('screenshots_folder') . '/' . $scr))
                $scrs[] = array($scr);
        }
        return serialize($scrs);
    }

    /**
     * Получение списка категорий
     * @param int $category ID категории
     * @param string $incat ID'ы подкатегорий
     * @return string список категорий
     */
    public function get_catid($category, $incat) {
        $mid = stats::o()->read(convert::stfield);
        $incat = unserialize($incat);
        if (!$incat)
            return ',' . $category . ',';
        $r = '';
        foreach ($incat as $ic)
            if ($ic)
                $r .= "," . ($mid + $ic);
        return ($r ? $r : ',' . $category) . ',';
    }

    /**
     * Получение беззнакового целого представления IP
     * @param string $ip строка с IP
     * @return int беззнаковое целое представление IP
     */
    public function get_ip($ip) {
        return ip2ulong($ip);
    }

    /**
     * Получение аватары пользователя и изменение её имени в соотв. с паттерном CTRev
     * @param string $avatar аватар пользователя
     * @param int $id ID пользователя
     * @return string автара пользователя
     */
    public function get_avatar($avatar, $id) {
        if (preg_match('/\/(' . $id . '\.\w+)$/siu', $avatar, $matches)) {
            $path = ROOT . config::o()->v('avatars_folder') . '/';
            $oname = $matches[1];
            $nname = display::avatar_prefix . $oname;
            $npname = $path . $nname;
            $opname = $path . $oname;
            if (!file_exists($npname) && file_exists($opname))
                rename($opname, $npname);
            if (file_exists($npname))
                return $nname;
            return '';
        } else
            return $avatar;
    }

    /**
     * Получение нового ID группы
     * @param int $group старый ID группы
     * @return int новый ID группы
     */
    public function get_group($group) {
        $group = (int) $group;
        if (!$this->groups || !is_array($this->groups) || !$this->groups[$group]) {
            printf(lang::o()->v('convert_cant_find_group'), $group);
            die();
        }
        return $this->groups[$group];
    }

    /**
     * Получение пасскея пользователя
     * @param string $passkey пасскей пользователя
     * @return string пасскей пользователя или, в случае отсутствия, рандомно сгенерированная строка
     */
    public function get_passkey($passkey) {
        if (!$passkey)
            $passkey = users::o()->generate_salt();
        return $passkey;
    }

}

?>