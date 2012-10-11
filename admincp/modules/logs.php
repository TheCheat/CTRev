<?php

/**
 * Project:            	CTRev
 * File:                logs.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление банами и предупреждениями блокировками
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class logs_man {

    /**
     * Типы логов
     * @var array $types
     */
    protected $types = array('system',
        'admin',
        'user',
        'other');

    /**
     * Сортировка
     * @var array $orderby
     */
    protected $orderby = array(
        'subject',
        'type',
        'time',
        'byuid',
        'touid');

    /**
     * Инициализация модуля логов
     * @return null
     */
    public function init() {
        $admin_file = globals::g('admin_file');
        lang::o()->get('admin/logs');
        $act = $_GET["act"];
        $type = $_GET["type"];
        switch ($act) {
            case "clear":
                /* @var $o logs_man_ajax */
                $o = plugins::o()->get_module('logs', 1, true);
                $o->clear();
                furl::o()->location($admin_file);
                break;
            default:
                $this->show($type, $_GET['sort']);
                break;
        }
    }

    /**
     * Отображение списка логов
     * @param string $type тип логов
     * @param string $sort сортировка
     * @return null
     */
    protected function show($type = null, $sort = null) {
        tpl::o()->assign('curtype', $type);
        if ($type)
            $type = db::o()->esc($type);
        $orderby = '';
        if ($sort) {
            $sort = explode(",", $sort);
            $c = count($sort);
            for ($i = 0; $i < $c; $i += 2) {
                if (!$this->orderby [$sort[$i]])
                    continue;
                $orderby .= ($orderby ? ', ' : '') .
                        "`" . $this->orderby [$sort[$i]] . "` " . ($sort[$i + 1] ? "asc" : "desc");
            }
        }
        if (!$orderby)
            $orderby = 'l.`time` DESC';
        $count = db::o()->count_rows("logs", $type ? 'type=' . $type : '');
        list($pages, $limit) = display::o()->pages($count, config::o()->v('table_perpage'), 'switch_logs_page', 'page', 5, true);
        $r = db::o()->query('SELECT l.*, u.username, u.group, u2.username AS tusername, u2.group AS tgroup
            FROM logs AS l LEFT JOIN users AS u ON u.id=l.byuid LEFT JOIN users AS u2 ON u2.id=l.touid
            ' . ($type ? ' WHERE l.type=' . $type : "") . '
            ' . ($orderby ? ' ORDER BY ' . $orderby : "") . '
            ' . ($limit ? ' LIMIT ' . $limit : ""));
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->assign('log_types', $this->types);
        tpl::o()->assign('pages', $pages);
        tpl::o()->display('admin/logs/index.tpl');
    }

}

class logs_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET["act"];
        $type = $_POST["type"];
        switch ($act) {
            case "clear":
                $this->clear($type);
                break;
        }
        die("OK!");
    }

    /**
     * Очистка логов
     * @param string $type тип логов
     * @return null
     */
    public function clear($type = null) {
        db::o()->delete('logs', ($type ? 'WHERE type=' . db::o()->esc($type) : ""));
        log_add('cleared_logs' . ($type ? '_' . $type : ''), 'admin');
    }

}

?>