<?php

/**
 * Project:            	CTRev
 * File:                cats.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление категориями
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class cats_man {

    /**
     * Инициализация управления категориями
     * @global lang $lang
     * @global categories $cats
     * @global tpl $tpl
     * @global string $admin_file
     * @return null
     */
    public function init() {
        global $lang, $cats, $tpl, $admin_file;
        $lang->get('admin/cats');
        $act = $_GET['act'];
        $type = $_GET['type'];
        if (!$type || !$cats->change_type($type))
            $type = 'torrents';
        $tpl->assign('oldadmin_file', $admin_file);
        $admin_file .= '&type=' . $type;
        $tpl->assign('admin_file', $admin_file);
        $tpl->assign('cat_type', $type);
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
     * @global db $db
     * @global input $input
     * @param int $cur ID данного шаблона
     * @return string HTML код селектора
     */
    protected function pattern_selector($cur = null) {
        global $db, $input;
        $r = $db->query('SELECT id,name FROM patterns');
        $arr = $db->fetch2array($r, null, array('id' => 'name'));
        return $input->simple_selector('pattern', $arr, true, $cur, 1, true);
    }

    /**
     * Добавление/редактирование категории
     * @global categories $cats
     * @global tpl $tpl
     * @param int $id ID категории
     * @param bool $add добавление?
     * @return null
     * @throws EngineException
     */
    protected function add($id = null, $add = false) {
        global $cats, $tpl;
        $row = array();
        if (!$add) {
            $row = $cats->get($id);
            if (!$row && !$add)
                throw new EngineException;
        }
        if ($add)
            $row['parent_id'] = $id;
        else {
            $parent = $cats->get($id, 'p');
            $row['parent_id'] = $parent['id'];
        }
        $tpl->assign('id', $add ? 0 : $id);
        $tpl->assign('row', $row);
        $tpl->assign('pattern_selector', $this->pattern_selector($row['pattern']));
        $tpl->display('admin/cats/add.tpl');
    }

    /**
     * Вывод списка категорий
     * @global tpl $tpl
     * @global categories $cats
     * @return null 
     */
    protected function show() {
        global $tpl, $cats;
        $types = $cats->get(null, 'z');
        if (!count($types) > 1) {
            $tpl->assign('cat_types', $types);
            $selector = $tpl->fetch('admin/cats/types.tpl');
        }
        $tpl->assign('cat_tselector', $selector);
        $tpl->assign('cat_tree', $this->build_tree());
        $tpl->display('admin/cats/index.tpl');
    }

    /**
     * Построение дерева категорий
     * @global categories $cats
     * @global tpl $tpl
     * @param int $pid ID родителя
     * @return string HTML код дерева
     */
    protected function build_tree($pid = null) {
        global $cats, $tpl;
        if (!$pid)
            $elements = $cats->get(null, 't');
        else
            $elements = $cats->get($pid, 'c');
        $c = count($elements);
        if (!$c)
            return;
        $html = "<ol" . (!$pid ? " class='sortable' id='cats_order'" : '') . ">";
        for ($i = 0; $i < $c; $i++) {
            $tpl->assign('row', $elements[$i]);
            $id = $elements[$i]['id'];
            $element = $tpl->fetch('admin/cats/element.tpl');
            $element .= $this->build_tree($id);
            $html .= '<li id="catid_' . $id . '">' . $element . '</li>';
        }
        return $html . "</ol>";
    }

    /**
     * Сохранение категории
     * @global db $db
     * @global categories $cats
     * @global cache $cache
     * @global furl $furl
     * @global string $admin_file
     * @param array $data массив данных категории
     * @param array $type тип категории
     * @return null
     * @throws EngineException 
     */
    protected function save($type, $data) {
        global $db, $cats, $cache, $furl, $admin_file;
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
        if (!$cats->get($update['parent_id']))
            $update['parent_id'] = 0;
        else
            $update['parent_id'] = (int) $update['parent_id'];
        $update['post_allow'] = (bool) $update['post_allow'];
        if ($id) {
            $db->update($update, 'categories', 'WHERE id=' . $id . ' LIMIT 1');
            log_add('changed_cat', 'admin', $id);
        } else {
            $db->insert($update, 'categories');
            log_add('added_cat', 'admin');
        }
        $db->query('ALTER TABLE `categories` ORDER BY `sort`');
        $cache->remove('categories');
        $furl->location($admin_file);
    }

}

class cats_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global cache $cache
     * @return null
     */
    public function init() {
        global $cache;
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
        $cache->remove('categories');
        die('OK!');
    }

    /**
     * Удаление категории
     * @global db $db
     * @global categories $cats
     * @param int $id ID категории
     * @return null
     * @throws EngineException
     */
    protected function delete($id) {
        global $db, $cats;
        $id = (int) $id;
        if (!$cats->get($id))
            throw new EngineException;
        $ids = array();
        $cats->get_children_ids($id, $ids);
        $ids [] = $id;
        $db->delete('categories', 'WHERE id IN(' . implode(', ', $ids) . ')');
        log_add('deleted_cat', 'admin', $id);
    }

    /**
     * Сохранение порядка категорий
     * @global db $db
     * @global categories $cats
     * @return null
     * @throws EngineException
     */
    protected function save_order($sort) {
        global $db, $cats;
        if (!$sort)
            throw new EngineException;
        $i = 0;
        foreach ($sort as $id => $parent) {
            if (!$parent || !$cats->get($parent))
                $parent = 0;
            $db->update(array('sort' => $i,
                'parent_id' => (int) $parent), 'categories', 'WHERE id=' . intval($id) . ' LIMIT 1');
            $i++;
        }
        $db->query('ALTER TABLE `categories` ORDER BY `sort`');
    }

    /**
     * Включение/выключение возможности постить в категорию
     * @global db $db
     * @param int $id ID категории
     * @return null
     */
    protected function switch_state($id) {
        global $db;
        $db->update(array('_cb_post_allow' => 'IF(post_allow="1","0","1")'), 'categories', 'WHERE id=' . intval($id) . ' LIMIT 1');
    }

}

?>