<?php

/**
 * Project:            	CTRev
 * File:                class.blocks.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс блочной системы
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class blocks {
    /**
     * @const allblock - общий шаблон для блоков, в случае отсутсвия иных
     */

    const allblock = "all_blocks";
    /**
     * @const blocks_path - путь к блокам в теме(без слеша в конце и в начале)
     */
    const blocks_path = "blocks";
    /**
     * @const blocks_standart - постфикс к стандартному блоку
     */
    const block_standart = "_block_standart";

    /**
     * Сохранённый массив всех блоков
     * @var array
     */
    private $blocks = array();

    /**
     * Массив настроек
     * @var array
     */
    private $settings = array();

    /**
     * Загруженный модуль
     * @var string
     */
    private $module = "";

    /**
     * Статус блочной системы
     * @var bool
     */
    private $state = true;

    /**
     * Конструктор класса
     * @global config $config
     * @return null 
     */
    public function __construct() {
        global $config;
        $this->state = $config->v('use_blocks');
    }

    /**
     * Метод показывания блока
     * @global db $db
     * @param string $pos положение блока(left, right, top, bottom)
     * @return null
     */
    public function display($pos) {
        global $db;
        if (!$this->state)
            return;
        if (is_array($pos)) {
            $spos = current($pos);
            if (!$spos)
                $spos = $pos ["pos"];
            $pos = $spos;
        }
        if (!$this->blocks)
            $this->blocks = $db->query('SELECT * FROM blocks WHERE enabled = true', 'blocks');
        foreach ($this->blocks as $index => $row) {
            if ($row ['module']) {
                $row ['module'] = explode(';', $row ['module']);
                if (!in_array($this->module, $row ['module']))
                    continue;
            }
            if ($row ['type'] != $pos)
                continue;
            unset($this->blocks [$index]);
            $this->show_single($row);
        }
    }

    /**
     * Установка отображемого модуля страницы для проверки в блоке
     * @param string $module имя модуля
     * @return blocks $this
     */
    public function set_module($module) {
        if (!$this->state)
            return $this;
        $this->module = $module;
        return $this;
    }

    /**
     * Метод покпазывания блока
     * @global tpl $tpl
     * @global db $db
     * @global plugins $plugins
     * @global users $users
     * @global modsettings $modsettings
     * @param array $row массив данных блока
     * @return null
     */
    public function show_single($row) {
        global $tpl, $db, $plugins, $users, $modsettings;
        if (!$this->state)
            return;
        if (!$row)
            return;
        $file = $row ['file'];
        $title = $row['title'];
        $id = $row['id'];
        $settings = unserialize($row ['settings']);
        $template = ($row ['tpl'] ? $row ['tpl'] : $row["type"] . self::block_standart);
        if ($row['group_allowed']) {
            $groups = explode(';', $row['group_allowed']);
            if (!in_array($users->v('group'), $groups))
                return;
        }
        if (!$title)
            return;
        $object = $plugins->get_module($file, true);
        if (!$object)
            return;
        $f = self::blocks_path . "/";
        $f1 = $f . $template . ".tpl";
        $f2 = $f . self::allblock . ".tpl";
        if ($tpl->template_exists($f1))
            $template = $f1;
        elseif ($tpl->template_exists($f2))
            $template = $f2;
        else
            return;
        $modsettings->change_type('blocks')->parse($id, $object, $settings);
        $db->nt_error();
        ob_start();
        try {
            $content = $plugins->call_init($object);
        } catch (EngineException $e) {
            mess($e->getEMessage(), null, "error", false);
        }
        if (!$content)
            $content = ob_get_contents();
        ob_end_clean();
        $db->nt_error(true);
        if (!trim($content))
            return;
        $tpl->assign("title", $title);
        $tpl->assign("content", $content);
        $tpl->display($template);
    }

    /**
     * Получение настроек блока
     * @global plugins $plugins
     * @global db $db
     * @global modsettings $modsettings
     * @param string $file файл
     * @return array массив настроек
     */
    public function get_settings($file) {
        global $plugins, $db, $modsettings;
        if (!$this->state)
            return;
        if ($this->settings[$file])
            return $this->settings[$file];
        if (!validword($file))
            return;
        $r = $db->query('SELECT id,settings FROM blocks WHERE file=' . $db->esc($file) . ' LIMIT 1');
        list($id, $settings) = $db->fetch_row($r);
        if (!$id)
            return;
        $settings = unserialize($settings);
        $object = $plugins->get_module($file, true);
        if (!$object || !isset($object->settings))
            return;
        $modsettings->change_type('blocks')->parse($id, $object, $settings);
        $this->settings[$file] = $object->settings;
        return $this->settings[$file];
    }

}

?>