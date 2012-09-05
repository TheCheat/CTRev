<?php

/**
 * Project:             CTRev
 * File:                users.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Профиль пользователя
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class user {

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Массив эл-в меню
     * @var array
     */
    protected $menu = array(
        "torrents",
        "comments",
        "friends",
        "stats");

    /**
     * Инициализация функций профиля
     * @global lang $lang
     * @global users $users
     * @return null
     */
    public function init() {
        global $lang, $users;
        $lang->get('profile');
        $lang->get('usercp');
        $act = $_GET ['act'];
        $username = $_GET ['user'];
        $this->title = $lang->v('users_page');
        $users->check_inadmin('users', true);
        $this->show_userinfo($username, $act);
    }

    /**
     * Функция получения возраста пользователя
     * @global display $display
     * @param int $birthdate дата рождения
     * @return int возраст пользователя
     */
    public function get_age($birthdate) {
        global $display;
        $display->time_diff($birthdate);
        $age = explode('.', date('Y.m.d', $birthdate));
        $current = explode('.', date('Y.m.d', time()));
        return $current[0] - $age[0] - ($age[1] > $current[1] || ($age[1] == $current[1] && $age[2] > $current[2]) ? 1 : 0);
    }

    /**
     * Отображение профиля пользователя
     * @global db $db
     * @global tpl $tpl
     * @global users $users
     * @global lang $lang
     * @global plugins $plugins
     * @param string $username имя пользователя
     * @param string $act запускаемый сабмодуль
     * @return null
     * @throws EngineException
     */
    protected function show_userinfo($username, $act) {
        global $db, $tpl, $users, $lang, $plugins;
        $users->check_perms('profile', 1, 2);
        $row = $db->query('SELECT u.*' .
                ($users->v() ? ', z.id AS zebra_id, z.type AS zebra_type' : "") . '
                FROM users AS u
                ' . ($users->v() ? 'LEFT JOIN zebra AS z ON z.user_id=' . $users->v('id') . ' 
                AND z.to_userid=u.id' : "") . '
                WHERE u.username_lower=' . $db->esc(mb_strtolower($username)) . '
                AND u.id>0 LIMIT 1');
        $row = $db->fetch_assoc($row);
        if (!$row)
            throw new EngineException("users_profile_not_exists", $username);
        $row = $users->decode_settings($row);
        if ((int) $row["country"]) {
            $r = $db->query("SELECT name, image FROM countries WHERE id=" . $row["country"] . " LIMIT 1");
            $r = $db->fetch_assoc($r);
            $row["country_name"] = $r["name"];
            $row["country_image"] = $r["image"];
        }
        $this->title .= ' "' . $username . '"';
        $id = $row ['id'];
        $karma = $row["karma_count"];
        $row ['age'] = $this->get_age($row ['birthday']);
        
        try {
            $plugins->pass_data(array('row' => &$row), true)->run_hook('user_profile');
        } catch (PReturn $e) {
            return $e->r();
        }
        
        $tpl->assign("row", $row);
        $tpl->assign("karma", $karma);
        $tpl->assign("act", $act);
        $tpl->assign("menu", $this->menu);
        $tpl->display('profile/user.tpl');
    }

}

class user_ajax {

    /**
     * Инициализация AJAX функций профиля
     * @global lang $lang
     * @global users $users
     * @global comments $comments
     * @return null
     */
    public function init() {
        global $lang, $users, $comments;
        $users->check_perms('profile', 1, 2);
        $lang->get('profile');
        $act = $_GET ['act'];
        $id = (int) $_POST ['id'];
        switch ($act) {
            case "show_stats" :
                $this->show_user_stats($id);
                break;
            case "show_friends" :
                $this->show_user_friends($id);
                break;
            case "show_comments" :
                if (!$users->perm("comment"))
                    die($lang->v('users_you_cant_view_this'));
                $comments->usertable($id);
                break;
            case "show_torrents" :
                if (!$users->perm("torrents"))
                    die($lang->v('users_you_cant_view_this'));
                $this->show_last_torrents($id);
                break;
            default :
                break;
        }
    }

    /**
     * Отображение последних торрентов пользователя
     * @global db $db
     * @global categories $cats
     * @global tpl $tpl
     * @global config $config
     * @param int $id ID пользователя
     * @param string $where доп. условие
     * @return null
     */
    public function show_last_torrents($id = null, $where = null) {
        global $db, $cats, $tpl, $config;
        $id = (int) $id;
        $select = "t.id,t.category_id,t.posted_time,t.title";
        if (!$id)
            $select .= ",t.poster_id";
        $where = ($id ? 'poster_id=' . $id : $where);
        $tr = $db->query('SELECT ' . $select . (!$id ? ',u.username,u.group' : '') . ' FROM torrents AS t
            ' . (!$id ? 'LEFT JOIN users AS u ON u.id=t.poster_id' : '') . '
            ' . ($where ? ' WHERE ' . $where : "") . '
            ORDER BY t.posted_time DESC
            ' . ($config->v('last_profile_torrents') ? "LIMIT " . $config->v('last_profile_torrents') : ""));
        $trs = array();
        while ($rows = $db->fetch_assoc($tr)) {
            $categs = $cats->cid2arr($rows ["category_id"]);
            $rows ["category_id"] = $categs [1];
            $trs [] = $rows;
        }
        $tpl->assign("torrents_row", $trs);
        $tpl->display("profile/last_torrents.tpl");
    }

    /**
     * Отображение статистики пользователя
     * @global tpl $tpl
     * @global etc $etc
     * @param int $id ID пользователя
     * @return null
     */
    public function show_user_stats($id) {
        global $tpl, $etc;
        $id = (int) $id;
        $row = $etc->select_user($id);
        $tpl->assign("row", $row);
        $tpl->display("profile/stats.tpl");
    }

    /**
     * Отображение друзей пользователя
     * @global tpl $tpl
     * @global db $db
     * @global lang $lang
     * @param int $id ID пользователя
     * @return null
     */
    public function show_user_friends($id) {
        global $tpl, $db, $lang;
        $id = (int) $id;
        $lang->get("usercp");
        $res = $db->query('SELECT u.username,u.group,u.registered,u.gender,u.avatar,z.* FROM zebra AS z
            LEFT JOIN users AS u ON u.id=z.to_userid
            WHERE z.user_id=' . $id);
        $tpl->assign("row", $db->fetch2array($res));
        $tpl->assign("from_profile", true);
        $tpl->display("usercp/friends.tpl");
    }

}

?>