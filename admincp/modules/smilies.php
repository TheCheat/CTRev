<?php

/**
 * Project:            	CTRev
 * File:                smilies.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление смайлами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class smilies_man {

    /**
     * Разрешённые типы изображений
     * @var array
     */
    protected $allowed_types = array('gif', 'png', 'jpg', 'jpeg');

    /**
     * Инициализация модуля смайлов
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get('admin/smilies');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $this->save($_POST);
                break;
            case "edit":
            case "add":
                try {
                    $this->add($_GET['file']);
                } catch (EngineException $e) {
                    $e->defaultCatch(true);
                }
                break;
            case "files":
                $this->files($_REQUEST['folder']);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Выбор смайлов
     * @global display $display
     * @global config $config
     * @param string $folder выбранная дирректория
     * @return null
     */
    protected function files($folder = null) {
        global $display, $config;
        $display->filechooser(null, $config->v('smilies_folder'), $folder);
    }

    /**
     * Отображение списка смайлов
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID смайла(для редактирования)
     * @return null
     */
    protected function show($id = null) {
        global $db, $tpl;
        $id = (int) $id;
        $r = $db->query('SELECT * FROM smilies' . ($id ? ' WHERE id=' . $id . ' LIMIT 1' : ""));
        $tpl->assign('res', $db->fetch2array($r));
        $tpl->display('admin/smilies/index.tpl');
    }

    /**
     * Получение кода и имени смайла
     * @param string $name имя смайла
     * @return array код и имя смайла
     */
    protected function get_smilie_name($name) {
        preg_match('/(\w+)\.([a-z]+)$/si', $name, $matches);
        if (!$matches)
            return array('', '');
        $name = $matches[1];
        $code = ':' . $name . ':';
        $name = mb_strtoupper(s($name, 0)) . mb_strtolower(mb_substr($name, 1));
        return array($code, $name);
    }

    /**
     * Добавление смайлов
     * @global db $db
     * @global tpl $tpl
     * @global file $file
     * @global config $config
     * @param string $f путь к файлу/дирректории
     * @return null
     * @throws EngineException
     */
    protected function add($f = null) {
        global $db, $tpl, $file, $config;
        $f = rtrim(validpath($f), '/');
        $path = $config->v('smilies_folder') . ($f ? '/' . $f : '');
        if (is_dir(ROOT . $path)) {
            $r = $file->open_folder($path, false, '^.*\.(' . implode('|', array_map('mpc', $this->allowed_types)) . ')$');
            $nr = array();
            foreach ($r as $k => $v) {
                $k = ($f ? $f . '/' : '') . $v;
                if ($db->count_rows('smilies', 'image = ' . $db->esc($k)))
                    continue;
                $nr[$k] = $this->get_smilie_name($v);
            }
            $tpl->assign('smilies', $nr);
        } elseif (file_exists(ROOT . $path) && in_array($file->get_filetype($path), $this->allowed_types))
            $tpl->assign('smilies', array($f => $this->get_smilie_name($f)));
        else
            throw new EngineException;
        $tpl->display('admin/smilies/add.tpl');
    }

    /**
     * Сохранение бота
     * @global db $db
     * @global furl $furl
     * @global string $admin_file
     * @global config $config
     * @global cache $cache
     * @global file $file
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $db, $furl, $admin_file, $config, $cache, $file;
        $cols = array(
            'id',
            'name',
            'code',
            'image',
            'sb' => 'show_bbeditor');
        extract(rex($data, $cols));
        $id = (int) $id;
        $name = (array) $name;
        $code = (array) $code;
        $image = (array) $image;
        $sb = (array) $sb;
        $c = count($name);
        if ($id && $c != 1)
            throw new EngineException('smilies_data_not_entered');
        if (!$name || $c != count($code) || $c != count($image))
            throw new EngineException('smilies_data_not_entered');
        foreach ($name as $i => $iname) {
            $icode = trim($code[$i]);
            $iname = trim($iname);
            $iimage = trim($image[$i]);
            $isb = (bool) $sb[$i];
            if (!$icode || !$iname || !$iimage)
                continue;
            if (!file_exists(ROOT . $config->v('smilies_folder') . '/' . $iimage) || !in_array($file->get_filetype($iimage), $this->allowed_types))
                continue;
            if ($db->count_rows('smilies', 'code = ' . $db->esc($icode) . ($id ? ' AND id<>' . $id : '')))
                continue;
            $update = array(
                'code' => $icode,
                'name' => $iname,
                'image' => $iimage,
                'show_bbeditor' => $isb);
            if (!$id)
                $db->insert($update, 'smilies', true);
            else
                $db->update($update, 'smilies', 'WHERE id=' . $id . ' LIMIT 1');
        }
        $cache->remove('smilies');
        if (!$id) {
            $db->save_last_table();
            $furl->location($admin_file);
        } else {
            $this->show($id);
            return;
        }
    }

}

class smilies_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global cache $cache
     * @return null
     */
    public function init() {
        global $cache;
        $act = $_GET["act"];
        $id = (int) $_POST["id"];
        switch ($act) {
            case "edit":
                $this->edit($id);
                break;
            case "delete":
                $this->delete($id);
                break;
            case "switch":
                $this->switch_state((int) $_POST['id']);
                break;
            case "order":
                $this->save_order($_POST['smilieid']);
                break;
        }
        $cache->remove('smilies');
        die("OK!");
    }

    /**
     * Редактирование смайла
     * @global db $db
     * @global tpl $tpl
     * @param int $id ID смайла
     * @return null
     * @throws EngineException
     */
    protected function edit($id) {
        global $db, $tpl;
        $id = (int) $id;
        $r = $db->query('SELECT * FROM smilies WHERE id=' . $id . ' LIMIT 1');
        if (!$db->num_rows($r))
            throw new EngineException;
        $tpl->assign('row', $db->fetch_assoc($r));
        $tpl->display('admin/smilies/edit.tpl');
        throw new EngineException;
    }

    /**
     * Включение/выключение отображение смайла в редакторе
     * @global db $db
     * @param int $id ID смайла
     * @return null
     */
    protected function switch_state($id) {
        global $db;
        $db->update(array('_cb_show_bbeditor' => 'IF(show_bbeditor="1","0","1")'), 'smilies', 'WHERE id=' . intval($id) . ' LIMIT 1');
    }

    /**
     * Удаление смайла
     * @global db $db
     * @param int $id ID смайла
     * @return null
     */
    protected function delete($id) {
        global $db;
        $id = (int) $id;
        $db->delete('smilies', 'WHERE id=' . $id . ' LIMIT 1');
    }

    /**
     * Сохранение порядка смайлов
     * @global db $db
     * @return null
     * @throws EngineException
     */
    protected function save_order($sort) {
        global $db;
        if (!$sort)
            throw new EngineException;
        foreach ($sort as $s => $id)
            $db->update(array('sort' => (int) $s), 'smilies', 'WHERE id=' . intval($id) . ' LIMIT 1');
        $db->query('ALTER TABLE `smilies` ORDER BY `sort`');
    }

}

?>