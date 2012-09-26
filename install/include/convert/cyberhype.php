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

$GLOBALS["display"] = new display();
$GLOBALS["bt"] = new bittorrent();
$GLOBALS["users"] = new users();

class get_convert {

    /**
     * Конвертируемая база данных
     * @var string
     */
    private $db = 'cyberhype';

    /**
     * Сопоставление групп
     * @var array
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
     * @global stats $stats
     * @global db $db
     * @param int $id старый ID подкатегории
     * @return int новый ID подкатегории
     */
    public function get_incatid($id) {
        global $stats, $db;
        if (!($max = $stats->read(convert::stfield)))
            $stats->write(convert::stfield, ($max = $db->act_row('categories', 'id', 'MAX')));
        return $max + $id;
    }

    /**
     * Изменение имени файла торрента в соотв. с паттерном CTRev
     * @global bittorrent $bt
     * @param array $row массив данных торрента
     * @return int ID торрента
     */
    public function get_tfile($row) {
        global $bt, $config;
        $fname = $bt->get_filename($row['posted_time'], $row['poster_id']);
        $path = ROOT . $config->v('torrents_folder') . '/';
        $nname = $path . bittorrent::torrent_prefix . $fname . ".torrent";
        $oname = $path . $row['id'] . ".torrent";
        if (!file_exists($nname) && file_exists($oname))
            rename($oname, $nname);
        return $row['id'];
    }

    /**
     * Получение имени ЧПУ для категории
     * @global display $display
     * @param int $id ID категории
     * @param string $name имя категории
     * @param string $sname имя категории для подкатегории
     * @return string транслитерованное имя
     */
    public function get_catname($id, $name, $sname = '') {
        global $display;
        return $display->translite($id . '-' . $name . ($sname ? "-" . $sname : ""));
    }

    /**
     * Получение списка файлов для торрента
     * @global db $db
     * @param int $id ID торрента
     * @return string список файлов
     */
    public function get_filelist($id) {
        global $db;
        $r = $db->query('SELECT filename, size FROM `' . $this->db . '`.`files` 
            WHERE torrent=' . $id . ' LIMIT 0, ' . (bittorrent::max_filelist + 1));
        $arr = $db->fetch2array($r, "row");
        $c = count($arr);
        if ($c > bittorrent::max_filelist)
            $arr[$c - 1] = array('...', 0);
        return serialize($arr);
    }

    /**
     * Оболочка для $db->count_rows в конвертере
     * @global db $db
     * @param string $table имя таблицы
     * @param string $where условие
     * @return int кол-во значений, удовл. условию
     */
    public function get_countrows($table, $where) {
        global $db;
        return $db->count_rows($table, $where);
    }

    /**
     * Получение списка скриншотов
     * @global config $config
     * @param string $poster постер
     * @param string $screenshots список скриншотов
     * @return string список скриншотов
     */
    public function get_screenshots($poster, $screenshots) {
        global $config;
        $screenshots = $poster . "\n" . $screenshots;
        $screenshots = explode("\n", $screenshots);
        $scrs = array();
        foreach ($screenshots as $scr) {
            $scr = trim($scr);
            if (!$scr)
                continue;
            if (preg_match('/[\/\\\]/', $scr))
                $scrs[] = $scr;
            elseif (file_exists(ROOT . $config->v('screenshots_folder') . '/' . $scr))
                $scrs[] = array($scr);
        }
        return serialize($scrs);
    }

    /**
     * Получение списка категорий
     * @global stats $stats
     * @param int $category ID категории
     * @param string $incat ID'ы подкатегорий
     * @return string список категорий
     */
    public function get_catid($category, $incat) {
        global $stats;
        $mid = $stats->read(convert::stfield);
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
     * @param string $avatar URL аватары
     * @param int $id ID пользователя
     * @return string автара пользователя
     */
    public function get_avatar($avatar, $id) {
        global $config;
        if (preg_match('/\/(' . $id . '\.\w+)$/siu', $avatar, $matches)) {
            $path = ROOT . $config->v('avatars_folder') . '/';
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
     * @global lang $lang
     * @param int $group старый ID группы
     * @return int новый ID группы
     */
    public function get_group($group) {
        global $lang;
        $group = (int) $group;
        if (!$this->groups || !is_array($this->groups) || !$this->groups[$group]) {
            printf($lang->v('convert_cant_find_group'), $group);
            die();
        }
        return $this->groups[$group];
    }

    /**
     * Получение пасскея пользователя
     * @global users $users
     * @param string $passkey пасскей пользователя
     * @return string пасскей пользователя или, в случае отсутствия, рандомно сгенерированная строка
     */
    public function get_passkey($passkey) {
        global $users;
        if (!$passkey)
            $passkey = $users->generate_salt();
        return $passkey;
    }

}

?>
