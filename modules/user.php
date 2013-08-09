<?php

/**
 * Project:             CTRev
 * @file                modules/users.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var string $title
     */
    public $title = "";

    /**
     * Массив эл-в меню
     * @var array $menu
     */
    protected $menu = array(
        "content",
        "comments",
        "friends",
        "stats");

    /**
     * Инициализация функций профиля
     * @return null
     */
    public function init() {
        lang::o()->get('profile');
        lang::o()->get('usercp');
        $act = $_GET ['act'];
        $username = $_GET ['user'];
        $this->title = lang::o()->v('users_page');
        users::o()->check_inadmin('users', true);
        $this->show_userinfo($username, $act);
    }

    /**
     * Функция получения возраста пользователя
     * @param int $birthdate дата рождения
     * @return int возраст пользователя
     */
    public function get_age($birthdate) {
        display::o()->time_diff($birthdate);
        $age = explode('.', date('Y.m.d', $birthdate));
        $current = explode('.', date('Y.m.d', time()));
        return $current[0] - $age[0] - ($age[1] > $current[1] || ($age[1] == $current[1] && $age[2] > $current[2]) ? 1 : 0);
    }

    /**
     * Отображение профиля пользователя
     * @param string $username имя пользователя
     * @param string $act запускаемый сабмодуль
     * @return null
     * @throws EngineException
     */
    protected function show_userinfo($username, $act) {
        users::o()->check_perms('profile', 1, 2);
        $lj = $cols = "";
        if (users::o()->v()) {
            db::o()->p(users::o()->v('id'));
            $cols = ", z.id AS zebra_id, z.type AS zebra_type";
            $lj = 'LEFT JOIN zebra AS z ON z.user_id=? AND z.to_userid=u.id';
        }
        $row = db::o()->p(mb_strtolower($username))->query('SELECT u.* ' . $cols . '
                FROM users AS u ' . $lj . '
                WHERE u.username_lower=?
                AND u.id>0 LIMIT 1');
        $row = db::o()->fetch_assoc($row);
        if (!$row)
            throw new EngineException("users_profile_not_exists", $username);
        $row = users::o()->decode_settings($row);
        /*
          if ((int) $row["country"]) {
          $r = db::o()->p($row["country"])->query("SELECT name, image FROM countries WHERE id=? LIMIT 1");
          $r = db::o()->fetch_assoc($r);
          $row["country_name"] = $r["name"];
          $row["country_image"] = $r["image"];
          } */
        $this->title .= ' "' . $username . '"';
        $id = $row ['id'];
        $karma = $row["karma_count"];
        $row ['age'] = $this->get_age($row ['birthday']);

        try {
            plugins::o()->pass_data(array('row' => &$row), true)->run_hook('user_profile');
        } catch (PReturn $e) {
            return $e->r();
        }

        tpl::o()->assign("row", $row);
        tpl::o()->assign("karma", $karma);
        tpl::o()->assign("act", $act);
        tpl::o()->assign("menu", $this->menu);
        n("comments"); // для display_comments
        n("display_userfields"); // для display_userfields
        tpl::o()->display('profile/user.tpl');
    }

}

class user_ajax {

    /**
     * Инициализация AJAX функций профиля
     * @return null
     */
    public function init() {
        users::o()->check_perms('profile', 1, 2);
        lang::o()->get('profile');
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
                if (!users::o()->perm("comment"))
                    die(lang::o()->v('users_you_cant_view_this'));
                /* @var $comments comments */
                $comments = n("comments");
                $comments->usertable($id);
                break;
            case "show_content" :
                if (!users::o()->perm("content"))
                    die(lang::o()->v('users_you_cant_view_this'));
                $this->show_last_content($id);
                break;
            default :
                break;
        }
    }

    /**
     * Отображение последнего контента пользователя
     * @param int $id ID пользователя
     * @param string $where доп. условие
     * @return null
     */
    public function show_last_content($id = null, $where = null) {
        $id = (int) $id;
        $select = "t.id,t.category_id,t.posted_time,t.title";
        if (!$id)
            $select .= ",t.poster_id";
        $where = ($id ? 'poster_id=' . $id : $where);
        $tr = db::o()->no_parse()->query('SELECT ' . $select . (!$id ? ',u.username,u.group' : '') . ' 
            FROM ' . db::table('content') . ' AS t
            ' . (!$id ? 'LEFT JOIN ' . db::table('users') . ' AS u ON u.id=t.poster_id' : '') . '
            ' . ($where ? ' WHERE ' . $where : "") . '
            ORDER BY t.posted_time DESC
            ' . (config::o()->v('last_profile_content') ? "LIMIT " . config::o()->v('last_profile_content') : ""));
        $trs = array();
        /* @var $cats categories */
        $cats = n("categories");
        $cats->change_type("content");
        while ($rows = db::o()->fetch_assoc($tr)) {
            $categs = $cats->cid2arr($rows ["category_id"]);
            $rows ["category_id"] = $categs [1];
            $trs [] = $rows;
        }
        tpl::o()->assign("content_row", $trs);
        tpl::o()->display("profile/last_content.tpl");
    }

    /**
     * Отображение статистики пользователя
     * @param int $id ID пользователя
     * @return null
     */
    public function show_user_stats($id) {
        $id = (int) $id;
        /* @var $etc etc */
        $etc = n("etc");
        $row = $etc->select_user($id);
        tpl::o()->assign("row", $row);
        tpl::o()->display("profile/stats.tpl");
    }

    /**
     * Отображение друзей пользователя
     * @param int $id ID пользователя
     * @return null
     */
    public function show_user_friends($id) {
        $id = (int) $id;
        lang::o()->get("usercp");
        $res = db::o()->p($id)->query('SELECT u.username,u.group,u.registered,u.gender,u.avatar,z.* FROM zebra AS z
            LEFT JOIN users AS u ON u.id=z.to_userid
            WHERE z.user_id=?');
        tpl::o()->assign("row", db::o()->fetch2array($res));
        tpl::o()->assign("from_profile", true);
        tpl::o()->display("usercp/friends.tpl");
    }

}

?>