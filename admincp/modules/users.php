<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/users.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        lang::o()->get('admin/users');
        lang::o()->get("search");
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
                tpl::o()->assign('s_unco', $unco);
                tpl::o()->assign('s_nosearch', $no_search);
                tpl::o()->display('profile/search_user.tpl');
                break;
        }
    }

    /**
     * Редактирование пользователя
     * @param int $id ID пользователя
     * @return null
     */
    protected function edit($id) {
        $id = (int) $id;
        lang::o()->get("registration");
        lang::o()->get("usercp");
        lang::o()->get('admin/groups');
        /* @var $etc etc */
        $etc = n("etc");
        users::o()->set_tmpvars($etc->select_user($id));
        if (users::o()->v('confirmed') != 3)
            tpl::o()->assign('unco', true);
        tpl::o()->assign('inusercp', true);
        /* @var $usercp usercp */
        $usercp = plugins::o()->get_module('usercp');
        /* @var $groups groups_man */
        $groups = plugins::o()->get_module('groups', 1);
        ob_start();
        $group = users::o()->get_group(users::o()->v('old_group') ? users::o()->v('old_group') : users::o()->v('group'));
        users::o()->alter_perms($group);
        $groups->add($group, false, true);
        $c = ob_get_contents();
        ob_end_clean();
        tpl::o()->assign('perms', $c);
        $usercp->show_index();
        users::o()->remove_tmpvars();
    }

}

class users_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        lang::o()->get('admin/users');
        //$act = $_GET['act'];
        users::o()->admin_mode();
        $mode = $_POST['mode'];
        $items = (array) $_POST['item'];
        unset($_POST['mode']);
        unset($_POST['item']);
        $this->massact($mode, $items, $_POST);
        ok();
    }

    /**
     * Действие над несколькими пользователям
     * @param string $mode режим
     * @param array $items ID'ы пользователей
     * @param array $data другие данные
     * @return null
     */
    protected function massact($mode, $items, $data) {
        if (!$items || !is_array($items))
            throw new EngineException('nothing_selected');
        $error = array();
        /* @var $etc etc */
        $etc = n("etc");
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
                        $error[] = sprintf(lang::o()->v('useract_cant_delete_user'), $uid);
                    break;
            }
        if ($error)
            throw new EngineException(implode("; ", $error));
    }

    /**
     * Удаление контента пользователя
     * @param int $uid ID пользователя
     * @param array $data данные формы
     * @param array $error массив ошибок
     * @return null
     */
    protected function delete_content($uid, $data, &$error = null) {
        $c = $data['content'];
        $ct = $c['content'];
        $cc = $c['comments'];
        $cp = $c['polls'];
        /* @var $polls polls */
        $polls = n("polls");
        /* @var $comments comments */
        $comments = n("comments");
        /* @var $etc etc */
        $etc = n("etc");
        if ($ct) {
            $res = db::o()->p($uid)->query('SELECT id FROM content WHERE poster_id=?');
            while (list($id) = db::o()->fetch_row($res))
                try {
                    $etc->delete_content($id);
                } catch (EngineException $e) {
                    $error[] = sprintf(lang::o()->v('useract_cant_delete_content'), $id, $e->getEMessage());
                }
        }
        if ($cc) {
            $res = db::o()->p($uid)->query('SELECT id FROM comments WHERE poster_id=?');
            while (list($id) = db::o()->fetch_row($res))
                try {
                    $comments->delete($id, true);
                } catch (EngineException $e) {
                    $error[] = sprintf(lang::o()->v('useract_cant_delete_comment'), $id, $e->getEMessage());
                }
        }
        if ($cp) {
            $res = db::o()->p($uid)->query('SELECT id FROM polls WHERE poster_id=?');
            while (list($id) = db::o()->fetch_row($res))
                try {
                    $polls->delete($id);
                } catch (EngineException $e) {
                    $error[] = sprintf(lang::o()->v('useract_cant_delete_poll'), $id, $e->getEMessage());
                }
        }
    }

}

?>