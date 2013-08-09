<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/spages.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление стат. страницами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class spages_man {

    /**
     * Инициализация модуля стат. страниц
     * @return null
     */
    public function init() {
        $POST = globals::g('POST');
        lang::o()->get('admin/static');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $_POST['html'] = $POST['html'];
                $this->save($_POST);
                break;
            case "add":
            case "edit":
                $this->add($_GET['id']);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Отображение списка стат. страниц
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT * FROM static');
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->display('admin/static/index.tpl');
    }

    /**
     * Добавление/редактирование стат. страницы
     * @param int $id ID стат. страницы
     * @return null
     */
    protected function add($id = null) {
        $id = (int) $id;
        if ($id) {
            $r = db::o()->p($id)->query('SELECT * FROM static WHERE id=? LIMIT 1');
            tpl::o()->assign("row", db::o()->fetch_assoc($r));
        }
        tpl::o()->assign("id", $id);
        tpl::o()->display('admin/static/add.tpl');
    }

    /**
     * Сохранение стат. страницы
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    public function save($data) {
        $admin_file = globals::g('admin_file');
        $cols = array(
            'url',
            'title',
            'content',
            'type');
        $update = rex($data, $cols);
        $id = (int) $data['id'];
        if (!validword($update['url']))
            throw new EngineException('static_empty_url');
        if (!$update['title'])
            throw new EngineException('static_empty_title');
        if ($update['type'] == 'html')
            $update['content'] = $data['html'];
        elseif ($update['type'] == 'tpl') {
            $update['content'] = $data['tpl'];
            if (!validpath($update['content']) || !tpl::o()->template_exists($update['content']))
                throw new EngineException('static_tpl_not_exists');
        }
        if (!$update['content'])
            throw new EngineException('static_empty_content');
        if (!$id) {
            db::o()->insert($update, 'static');
            log_add('added_static', 'admin', $data['url']);
        } else {
            db::o()->p($id)->update($update, 'static', 'WHERE id=? LIMIT 1');
            log_add('changed_static', 'admin', $data['url']);
        }
        furl::o()->location($admin_file);
    }

}

class spages_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET["act"];
        $id = (int) $_POST["id"];
        switch ($act) {
            case "delete":
                $this->delete($id);
                break;
        }
        ok();
    }

    /**
     * Удаление стат. страницы
     * @param int $id ID страницы
     * @return null
     */
    public function delete($id) {
        $id = (int) $id;
        db::o()->p($id)->delete('static', 'WHERE id=? LIMIT 1');
        log_add('deleted_static', 'admin', $id);
    }

}

?>