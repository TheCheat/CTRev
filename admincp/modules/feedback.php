<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/feedback.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Обратная связь
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class feedback_man {

    /**
     * Сортировка
     * @var array $orderby
     */
    protected $orderby = array(
        'subject',
        'time',
        'uid',
        'ip');

    /**
     * Инициализация модуля обратной связи
     * @return null
     */
    public function init() {
        $admin_file = globals::g('admin_file');
        lang::o()->get('admin/feedback');
        $act = $_GET["act"];
        switch ($act) {
            case "clear":
                /* @var $o feedback_man_ajax */
                $o = plugins::o()->get_module('feedback', 1, true);
                $o->clear();
                furl::o()->location($admin_file);
                break;
            default:
                $this->show($_GET['sort'], $_GET['type']);
                break;
        }
    }

    /**
     * Отображение списка обратной связи
     * @param string $sort сортировка
     * @param string $type тип
     * @return null
     */
    protected function show($sort = null, $type = '') {
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
            $orderby = 'f.`time` DESC';
        $where = $type ? 'f.type=?' : "";
        $count = db::o()->p($type)->as_table('f')->count_rows("feedback", $where);
        list($pages, $limit) = display::o()->pages($count, config::o()->v('table_perpage'), 'switch_feedback_page', 'page', 5, true);
        $r = db::o()->p($type)->query('SELECT f.*, u.username, u.group
            FROM feedback AS f LEFT JOIN users AS u ON u.id=f.uid
            ' . ($where ? ' WHERE ' . $where : "") . '
            ' . ($orderby ? ' ORDER BY ' . $orderby : "") . '
            ' . ($limit ? ' LIMIT ' . $limit : ""));
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->assign('pages', $pages);
        tpl::o()->assign('type', $type);
        tpl::o()->display('admin/feedback/index.tpl');
    }

}

class feedback_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET["act"];
        switch ($act) {
            case "delete":
                $this->delete((int) $_POST['id']);
                break;
            case "clear":
                $this->clear($_GET['type']);
                break;
        }
        ok();
    }

    /**
     * Удаление сообщения обратной связи
     * @return null
     */
    public function delete($id) {
        $id = (int) $id;
        db::o()->p($id)->delete('feedback', 'WHERE id=? LIMIT 1');
    }

    /**
     * Очистка обратной связи
     * @param string $type тип
     * @return null
     */
    public function clear($type = '') {
        db::o()->p($type)->delete('feedback', $type ? 'WHERE type=?' : "");
        log_add('cleared_feedback', 'admin');
    }

}

?>