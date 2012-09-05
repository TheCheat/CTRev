<?php

/**
 * Project:            	CTRev
 * File:                groups.php
 *
 * @link 	  	http://ctrev.cyber-tm.com/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление группами пользователя
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class groups_man {

    /**
     * Инициализация управления группами пользователей
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get('admin/groups');
        $act = $_GET['act'];
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
                $this->save($_POST);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Вывод селектора параметров
     * @global lang $lang
     * @param array $row массив группы
     * @param int $v значение параметра
     * @return string HTML код
     */
    public function show_selector($row, $v) {
        global $lang;
        $m = $row['allowed'];
        $n = $row['perm'];
        $s = "";
        if (strpos($n, "edit_") === 0 || strpos($n, "del_") === 0)
            $a = "_e";
        for ($i = $m; $i >= 0; $i--) {
            $l = $lang->visset("groups_rule_" . $n . "_value_" . $i) ?
                    $lang->v("groups_rule_" . $n . "_value_" . $i) :
                    $lang->v("groups_value_" . $i . ($i ? $a : ""));
            $s .= "<input type='radio' name='can_" . $n . "'
                value='" . $i . "'" . ($v == $i ? " checked='checked'" : "") . ">&nbsp;" . $l . " ";
        }
        return $s;
    }

    /**
     * Добавление/редактирование группы
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @param int $id ID группы
     * @param bool $add добавление?
     * @param bool $onlyperms только права?
     * @return null
     * @throws EngineException
     */
    public function add($id, $add = false, $onlyperms = false) {
        global $db, $users, $tpl;
        if (is_array($id) && $onlyperms)
            $row = $id;
        else
            $row = $users->get_group($id);
        if (!$row)
            throw new EngineException;
        $users->acp_modules($row);
        $tpl->assign('id', $add ? 0 : $id);
        $tpl->assign('row', $row);
        $r = $db->query('SELECT cat FROM groups_perm GROUP BY cat');
        $tpl->assign('types', $db->fetch2array($r, null, array('cat')));
        $r = $db->query('SELECT cat, perm, allowed FROM groups_perm');
        $perms = null;
        while ($row = $db->fetch_assoc($r))
            $perms[$row["cat"]][] = $row;
        $tpl->assign('perms', $perms);
        $tpl->assign('allowed_modules', allowed::o()->get("acp_modules"));
        $tpl->register_modifier('show_selector', array($this, 'show_selector'));
        $tpl->display('admin/groups/' . ($onlyperms ? 'perms' : 'add') . '.tpl');
    }

    /**
     * Отображение всех групп
     * @global db $db
     * @global tpl $tpl
     * @global users $users
     * @return null
     */
    protected function show() {
        global $db, $tpl, $users;
        $r = $db->query('SELECT `group`, COUNT(*) AS c FROM users GROUP BY `group`');
        $tpl->assign('params', array('default', 'system', 'guest', 'bot'));
        $tpl->assign('rows', $db->fetch2array($r, null, array('group' => 'c')));
        $tpl->display('admin/groups/index.tpl');
    }

    /**
     * Сохранение группы
     * @global db $db
     * @global furl $furl
     * @global string $admin_file
     * @global cache $cache
     * @param array $data массив данных группы
     * @param array $fgroup изначальные права группы(для прав пользователя)
     * @return null
     * @throws EngineException 
     */
    public function save($data, $fgroup = null) {
        global $db, $furl, $admin_file, $cache;
        if (!$fgroup) {
            $cols = array('name',
                'color',
                'pm_count',
                'system',
                'default',
                'bot',
                'guest',
                'torrents_count',
                'karma_count',
                'acp_modules',
                'bonus_count');
            if ($data['id'])
                $id = (int) $data['id'];
            $update = rex($data, $cols);
            if (count($update) != count($cols) || !$update['name'] || !$update['color'])
                throw new EngineException('groups_invalid_input');
        }
        $r = $db->query('SELECT id, perm, allowed, dvalue FROM groups_perm');
        $perms = "";
        while ($row = $db->fetch_assoc($r)) {
            $p = 'can_' . $row['perm'];
            $dvalue = $fgroup ? $fgroup[$p] : $row['dvalue'];
            if (isset($data[$p]) && strval((int) $data[$p]) === $data[$p]
                    && $data[$p] <= $row['allowed']
                    && (int) $data[$p] !== (int) $dvalue)
                $perms .= ($perms ? ";" : "") . $row['id'] . ":" . $data[$p];
        }
        if ($fgroup)
            return $perms;
        $update['perms'] = $perms;
        $update['acp_modules'] = implode(';', array_map('trim', (array) $update['acp_modules']));
        if ($id) {
            $db->update($update, 'groups', 'WHERE id=' . $id . ' LIMIT 1');
            log_add('changed_group', 'admin', $id);
        } else {
            $db->insert($update, 'groups');
            log_add('added_group', 'admin');
        }
        $db->query('ALTER TABLE `groups` ORDER BY `sort`');
        $cache->remove('groups');
        $furl->location($admin_file);
    }

}

class groups_man_ajax {

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
            case "order":
                $this->save_order($_POST['groupid']);
                break;
        }
        $cache->remove('groups');
        die('OK!');
    }

    /**
     * Удаление группы пользователя
     * @global db $db
     * @param int $id ID группы
     * @return null
     */
    protected function delete($id) {
        global $db;
        $db->delete('groups', 'WHERE id=' . intval($id) . ' AND notdeleted="0" LIMIT 1');
        log_add('deleted_group', 'admin', $id);
    }

    /**
     * Сохранение порядка групп
     * @global db $db
     * @return null
     * @throws EngineException
     */
    protected function save_order($sort) {
        global $db;
        if (!$sort)
            throw new EngineException;
        foreach ($sort as $s => $id)
            $db->update(array('sort' => (int) $s), 'groups', 'WHERE id=' . intval($id) . ' LIMIT 1');
        $db->query('ALTER TABLE `groups` ORDER BY `sort`');
    }

}

?>