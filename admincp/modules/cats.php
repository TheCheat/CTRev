<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/cats.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление категориями
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class cats_man {

    /**
     * Объект категорий
     * @var categories $cats
     */
    protected $cats = null;

    /**
     * Конструктор
     * @return null
     */
    public function __construct() {
        $this->cats = n("categories");
    }

    /**
     * Инициализация управления категориями
     * @return null
     */
    public function init() {
        $admin_file = globals::g('admin_file');
        lang::o()->get('admin/cats');
        $act = $_GET['act'];
        $type = $_GET['type'];
        if (!$type || !$this->cats->change_type($type))
            $type = 'content';
        tpl::o()->assign('oldadmin_file', $admin_file);
        $admin_file .= '&type=' . $type;
        globals::s('admin_file', $admin_file);
        tpl::o()->assign('admin_file', $admin_file);
        tpl::o()->assign('cat_type', $type);
        switch ($act) {
            case "add":
            case "edit":
                try {
                    $this->add((int) $_GET['id'], $act == "add");
                } catch (EngineException $e) {
                    $e->defaultCatch(true);
                }
                break;
            case "save":
                $this->save($type, $_POST);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Выборка шаблона
     * @param int $cur ID данного шаблона
     * @return string HTML код селектора
     */
    public function pattern_selector($cur = null) {
        $r = db::o()->query('SELECT id,name FROM patterns');
        $arr = db::o()->fetch2array($r, null, array('id' => 'name'));
        return input::o()->scurrent($cur)->snull()->skeyed()->simple_selector('pattern', $arr);
    }

    /**
     * Добавление/редактирование категории
     * @param int $id ID категории
     * @param bool $add добавление?
     * @return null
     * @throws EngineException
     */
    protected function add($id = null, $add = false) {
        $row = array();
        if (!$add) {
            $row = $this->cats->get($id);
            if (!$row && !$add)
                throw new EngineException;
        }
        if ($add)
            $row['parent_id'] = $id;
        else {
            $parent = $this->cats->get($id, 'p');
            $row['parent_id'] = $parent['id'];
        }
        tpl::o()->assign('id', $add ? 0 : $id);
        tpl::o()->assign('row', $row);
        tpl::o()->assign('pattern_selector', $this->pattern_selector($row['pattern']));
        tpl::o()->display('admin/cats/add.tpl');
    }

    /**
     * Вывод списка категорий
     * @return null 
     */
    protected function show() {
        $types = $this->cats->get(null, 'z');
        if (count($types) > 1) {
            tpl::o()->assign('cat_types', $types);
            $selector = tpl::o()->fetch('admin/cats/types.tpl');
        }
        tpl::o()->assign('cat_tselector', $selector);
        tpl::o()->assign('cat_tree', $this->build_tree());
        tpl::o()->display('admin/cats/index.tpl');
    }

    /**
     * Построение дерева категорий
     * @param int $pid ID родителя
     * @return string HTML код дерева
     */
    protected function build_tree($pid = null) {
        if (!$pid)
            $elements = $this->cats->get(null, 't');
        else
            $elements = $this->cats->get($pid, 'c');
        $c = count($elements);
        if (!$c)
            return;
        $html = "<ol" . (!$pid ? " class='sortable' id='cats_order'" : '') . ">";
        for ($i = 0; $i < $c; $i++) {
            tpl::o()->assign('row', $elements[$i]);
            $id = $elements[$i]['id'];
            $element = tpl::o()->fetch('admin/cats/element.tpl');
            $element .= $this->build_tree($id);
            $html .= '<li id="catid_' . $id . '">' . $element . '</li>';
        }
        return $html . "</ol>";
    }

    /**
     * Сохранение категории
     * @param array $data массив данных категории
     * @param array $type тип категории
     * @return null
     * @throws EngineException 
     */
    public function save($type, $data) {
        $admin_file = globals::g('admin_file');
        $cols = array(
            'parent_id',
            'name',
            'transl_name',
            'descr',
            'post_allow',
            'pattern');
        if ($data['id'])
            $id = (int) $data['id'];
        $update = rex($data, $cols);
        $update['type'] = $type;
        if (!$update['name'] || !$update['transl_name'])
            throw new EngineException("cats_invalid_input");
        if (!validword($update['transl_name']))
            throw new EngineException("cats_invalid_transl_name");
        $update['pattern'] = (int) $update['pattern'];
        if (!$this->cats->get($update['parent_id']))
            $update['parent_id'] = 0;
        else
            $update['parent_id'] = (int) $update['parent_id'];
        $update['post_allow'] = (bool) $update['post_allow'];
        try {
            plugins::o()->pass_data(array("update" => &$update,
                "id" => $id), true)->run_hook('admin_cats_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($id) {
            db::o()->p($id)->update($update, 'categories', 'WHERE id=? LIMIT 1');
            log_add('changed_cat', 'admin', $id);
        } else {
            db::o()->insert($update, 'categories');
            log_add('added_cat', 'admin');
        }
        db::o()->query('ALTER TABLE `categories` ORDER BY `sort`');
        cache::o()->remove('categories');
        furl::o()->location($admin_file);
    }

}

class cats_man_ajax {

    /**
     * Объект категорий
     * @var categories $cats
     */
    protected $cats = null;

    /**
     * Конструктор
     * @return null
     */
    public function __construct() {
        $this->cats = n("categories");
    }

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        $act = $_GET['act'];
        switch ($act) {
            case "delete":
                $this->delete((int) $_POST['id']);
                break;
            case "switch":
                $this->switch_state((int) $_POST['id']);
                break;
            case "order":
                $this->save_order($_POST['catid']);
                break;
        }
        cache::o()->remove('categories');
        ok();
    }

    /**
     * Удаление категории
     * @param int $id ID категории
     * @return null
     * @throws EngineException
     */
    public function delete($id) {
        $id = (int) $id;
        if (!$this->cats->get($id))
            throw new EngineException;
        $ids = array();
        $this->cats->get_children_ids($id, $ids);
        $ids [] = $id;
        db::o()->p($ids)->delete('categories', 'WHERE id IN(@' . count($ids) . '?)');
        log_add('deleted_cat', 'admin', $id);
    }

    /**
     * Сохранение порядка категорий
     * @return null
     * @throws EngineException
     */
    public function save_order($sort) {
        if (!$sort)
            throw new EngineException;
        $i = 0;
        foreach ($sort as $id => $parent) {
            $id = (int) $id;
            if (!$parent || !$this->cats->get($parent))
                $parent = 0;
            db::o()->p($id)->update(array('sort' => $i,
                'parent_id' => (int) $parent), 'categories', 'WHERE id=? LIMIT 1');
            $i++;
        }
        db::o()->query('ALTER TABLE `categories` ORDER BY `sort`');
    }

    /**
     * Включение/выключение возможности постить в категорию
     * @param int $id ID категории
     * @return null
     */
    public function switch_state($id) {
        db::o()->p($id)->update(array('_cb_post_allow' => 'IF(post_allow="1","0","1")'), 'categories', 'WHERE id=? LIMIT 1');
    }

}

?>