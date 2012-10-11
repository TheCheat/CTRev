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
     * Общий шаблон для блоков, в случае отсутсвия иных
     */

    const allblock = "all_blocks";
    /**
     * Путь к блокам в теме(без слеша в конце и в начале)
     */
    const blocks_path = "blocks";
    /**
     * Постфикс к стандартному блоку
     */
    const block_standart = "_block_standart";

    /**
     * Сохранённый массив всех блоков
     * @var array $blocks
     */
    private $blocks = array();

    /**
     * Массив настроек
     * @var array $settings
     */
    private $settings = array();

    /**
     * Загруженный модуль
     * @var string $module
     */
    private static $module = "";

    /**
     * Статус блочной системы
     * @var bool $state
     */
    private $state = true;

    /**
     * Конструктор класса
     * @return null 
     */
    public function __construct() {
        $this->state = config::o()->v('use_blocks');
    }

    /**
     * Метод показывания блока
     * @param string $pos положение блока(left, right, top, bottom)
     * @return null
     */
    public function display($pos) {
        if (!$this->state)
            return;
        if (is_array($pos)) {
            $spos = current($pos);
            if (!$spos)
                $spos = $pos ["pos"];
            $pos = $spos;
        }
        if (!$this->blocks)
            $this->blocks = db::o()->query('SELECT * FROM blocks WHERE enabled = true', 'blocks');
        foreach ($this->blocks as $index => $row) {
            if ($row ['module']) {
                $row ['module'] = explode(';', $row ['module']);
                if (!in_array(self::$module, $row ['module']))
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
     * @return null
     */
    public static function set_module($module) {
        self::$module = $module;
    }

    /**
     * Метод покпазывания блока
     * @param array $row массив данных блока
     * @return null
     */
    public function show_single($row) {
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
            if (!in_array(users::o()->v('group'), $groups))
                return;
        }
        if (!$title)
            return;
        $object = plugins::o()->get_module($file, true);
        if (!$object)
            return;
        $f = self::blocks_path . "/";
        $f1 = $f . $template . ".tpl";
        $f2 = $f . self::allblock . ".tpl";
        if (tpl::o()->template_exists($f1))
            $template = $f1;
        elseif (tpl::o()->template_exists($f2))
            $template = $f2;
        else
            return;
        modsettings::o()->change_type('blocks')->parse($id, $object, $settings);
        db::o()->nt_error();
        ob_start();
        try {
            $content = plugins::o()->call_init($object);
        } catch (EngineException $e) {
            message($e->getEMessage(), null, "error", false);
        }
        if (!$content)
            $content = ob_get_contents();
        ob_end_clean();
        db::o()->nt_error(true);
        if (!trim($content))
            return;
        tpl::o()->assign("title", $title);
        tpl::o()->assign("content", $content);
        tpl::o()->display($template);
    }

    /**
     * Получение настроек блока
     * @param string $file файл
     * @return array массив настроек
     */
    public function get_settings($file) {
        if (!$this->state)
            return;
        if ($this->settings[$file])
            return $this->settings[$file];
        if (!validword($file))
            return;
        $r = db::o()->query('SELECT id,settings FROM blocks WHERE file=' . db::o()->esc($file) . ' LIMIT 1');
        list($id, $settings) = db::o()->fetch_row($r);
        if (!$id)
            return;
        $settings = unserialize($settings);
        $object = plugins::o()->get_module($file, true);
        if (!$object || !isset($object->settings))
            return;
        modsettings::o()->change_type('blocks')->parse($id, $object, $settings);
        $this->settings[$file] = $object->settings;
        return $this->settings[$file];
    }

}

?>