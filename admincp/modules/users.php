<?php

/**
 * Project:            	CTRev
 * File:                users.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление пользователями
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class users_man {

    /**
     * Инициализация управления пользователями
     * @global lang $lang
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $tpl;
        $lang->get('admin/users');
        $lang->get("search");
        $act = $_GET['act'];
        $unco = false;
        $no_search = false;
        switch ($act) {
            case "edit":
                $id = (int) $_GET['id'];
                $this->edit($id);
                break;
            case "unconfirmed":
                $unco = true;
            default:
                $no_search = true;
            case "search":
                $tpl->assign('s_unco', $unco);
                $tpl->assign('s_nosearch', $no_search);
                $tpl->display('profile/search_user.tpl');
                break;
        }
    }

    /**
     * Редактирование пользователя
     * @global etc $etc
     * @global lang $lang
     * @global users $users
     * @global plugins $plugins
     * @global tpl $tpl
     * @param int $id ID пользователя
     * @return null
     */
    protected function edit($id) {
        global $etc, $lang, $users, $plugins, $tpl;
        $id = (int) $id;
        $lang->get("registration");
        $lang->get("usercp");
        $lang->get('admin/groups');
        $users->set_tmpvars($etc->select_user($id));
        if ($users->v('confirmed') != 3)
            $tpl->assign('unco', true);
        $tpl->assign('inusercp', true);
        $usercp = $plugins->get_module('usercp');
        $groups = $plugins->get_module('groups', 1);
        ob_start();
        $group = $users->get_group($users->v('old_group') ? $users->v('old_group') : $users->v('group'));
        $users->alter_perms($group);
        $groups->add($group, false, true);
        $c = ob_get_contents();
        ob_end_clean();
        $tpl->assign('perms', $c);
        $usercp->show_index();
        $users->remove_tmpvars();
    }

}

class users_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global lang $lang
     * @global users $users
     * @return null
     */
    public function init() {
        global $lang, $users;
        $lang->get('admin/users');
        //$act = $_GET['act'];
        $users->admin_mode();
        $mode = $_POST['mode'];
        $items = (array) $_POST['item'];
        unset($_POST['mode']);
        unset($_POST['item']);
        $this->massact($mode, $items, $_POST);
        die('OK!');
    }

    /**
     * Действие над несколькими пользователям
     * @global etc $etc
     * @global lang $lang
     * @param string $mode режим
     * @param array $items ID'ы пользователей
     * @param array $data другие данные
     * @return null
     */
    public function massact($mode, $items, $data) {
        global $etc, $lang;
        if (!$items || !is_array($items))
            throw new EngineException('nothing_selected');
        $error = array();
        foreach ($items as $uid)
            switch ($mode) {
                case "confirm":
                    $etc->confirm_user(3, 0, $uid);
                    break;
                case "ban":
                    if (!$data['reason'])
                        throw new EngineException('useract_no_ban_reason');
                    $etc->ban_user($uid, $data['period'], $data['reason']);
                    break;
                case "unban":
                    $etc->unban_user($uid);
                    break;
                case "change_group":
                    if (!$etc->change_group($uid, $data['group']))
                        throw new EngineException('useract_cant_change_group');
                    break;
                case "delete_content":
                    $this->delete_content($uid, $data, $error);
                    break;
                case "delete":
                    if (!$etc->delete_user($uid))
                        $error[] = sprintf($lang->v('useract_cant_delete_user'), $uid);
                    break;
            }
        if ($error)
            throw new EngineException(implode("; ", $error));
    }

    /**
     * Удаление контента пользователя
     * @global db $db
     * @global comments $comments
     * @global polls $polls
     * @global etc $etc
     * @global lang $lang
     * @param int $uid ID пользователя
     * @param array $data данные формы
     * @param array $error массив ошибок
     * @return null
     */
    protected function delete_content($uid, $data, &$error = null) {
        global $db, $comments, $polls, $etc, $lang;
        $c = $data['content'];
        $ct = $c['torrents'];
        $cc = $c['comments'];
        $cp = $c['polls'];
        if ($ct) {
            $res = $db->query('SELECT id FROM torrents WHERE poster_id=' . $uid);
            while (list($id) = $db->fetch_row($res)) {
                $r = $etc->delete_torrent($id, $e);
                if (!$r)
                    $error[] = sprintf($lang->v('useract_cant_delete_torrent'), $id, $e->getEMessage());
            }
        }
        if ($cc) {
            $res = $db->query('SELECT id FROM comments WHERE poster_id=' . $uid);
            while (list($id) = $db->fetch_row($res)) {
                try {
                    $comments->delete($id, true);
                } catch (EngineException $e) {
                    $error[] = sprintf($lang->v('useract_cant_delete_comment'), $id, $e->getEMessage());
                }
            }
        }
        if ($cp) {
            $res = $db->query('SELECT id FROM polls WHERE poster_id=' . $uid);
            while (list($id) = $db->fetch_row($res)) {
                try {
                    $polls->delete($id);
                } catch (EngineException $e) {
                    $error[] = sprintf($lang->v('useract_cant_delete_poll'), $id, $e->getEMessage());
                }
            }
        }
    }

}

?>