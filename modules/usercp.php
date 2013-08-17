<?php

/**
 * Project:             CTRev
 * @file                modules/usercp.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Панель управления профилем
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class usercp {

    /**
     * Заголовок модуля
     * @var string $title
     */
    public $title = "";

    /**
     * Верхнее меню панели
     * @var array $menu
     */
    protected $menu = array(
        "index",
        "invites",
        "friends",
        "bookmarks",
        "mailer");

    /**
     * Инициализация панели управления пользователя
     * @return null
     */
    public function init() {
        users::o()->check_perms();
        lang::o()->get("registration");
        lang::o()->get("usercp");
        $act = $_GET ['act'];
        if (($pos = array_search("invites", $this->menu)) &&
                !users::o()->perm("invite"))
            unset($this->menu [$pos]);
        if (($pos = array_search("bookmarks", $this->menu)) &&
                !users::o()->perm("content"))
            unset($this->menu [$pos]);
        if (($pos = array_search("mailer", $this->menu)) &&
                !config::o()->v('mailer_on'))
            unset($this->menu [$pos]);
        if (!in_array($act, $this->menu))
            $act = "index";
        tpl::o()->assign("menuacts", $this->menu);
        tpl::o()->assign("curact", $act);
        tpl::o()->display("usercp/header.tpl");
        $this->title = lang::o()->v("usercp_" . $act);
        switch ($act) {
            case "invites" :
                $this->show_invites();
                break;
            case "friends" :
                $this->show_friends();
                break;
            case "bookmarks" :
                $this->show_bookmarks();
                break;
            case "mailer":
                n("mailer")->show();
                break;
            default :
                $this->show_index();
                break;
        }
        tpl::o()->display("usercp/footer.tpl");
    }

    /**
     * Отображение основной части панели управления пользователя
     * @return null
     */
    public function show_index() {
        /* @var $uploader uploader */
        $uploader = n("uploader");
        display::o()->uploadify("ava", lang::o()->v('usercp_avatars_ext'), 'avatars', array(
            "module" => "usercp",
            "act" => "save_avatar"));
        //if (preg_match('/^' . display::url_pattern . '$/siu', users::o()->v('avatar')))
        //    tpl::o()->assign("current_avatar", users::o()->v('avatar'));
        users::o()->decode_settings();
        users::o()->unserialize('announce_pk');
        $pk = array();
        if (config::o()->v('get_pk')) {
            $pk = explode("\n", config::o()->v('get_pk'));
            $c = count($pk);
            for ($i = 0; $i < $c; $i++) {
                $pk[$i] = trim($pk[$i]);
                if (!$pk[$i])
                    unset($pk[$i]);
                $pk[$i] = preg_split('/\s+/siu', $pk[$i]);
            }
        }
        tpl::o()->assign('user_pk', $pk);
        $vars = tpl::o()->init_cfg(null, null);
        $ac = array_merge(array($vars['default_name']), explode("|", $vars['allowed_colors']));
        $dc = explode("|", $vars['display_colors']);
        tpl::o()->assign('allowed_colors', array_filter($ac, 'validword'));
        tpl::o()->assign('display_colors', $dc);
        n("userfields"); // для input_userfields
        tpl::o()->display("usercp/main.tpl");
    }

    /**
     * Отображение таблицы инвайтов
     * @return null
     */
    protected function show_invites() {
        $res = db::o()->p(users::o()->v('id'))->query('SELECT i.*, u.username, u.registered, u.confirmed, u.`group`
            FROM invites AS i
            LEFT JOIN users AS u ON i.to_userid=u.id
            WHERE i.user_id=?');
        tpl::o()->assign("row", db::o()->fetch2array($res));
        tpl::o()->display("usercp/invites.tpl");
    }

    /**
     * Отображение друзей
     * @param int $id ID записи
     * @return null
     */
    public function show_friends($id = null) {
        $id = (int) $id;
        $res = db::o()->p(users::o()->v('id'), $id)->query('SELECT z.*, u.username, u.group, u.registered, u.gender, 
            u.avatar FROM zebra AS z
            LEFT JOIN users AS u ON u.id=z.to_userid
            WHERE z.user_id=?' . ($id ? ' AND z.id=?' : ""));
        tpl::o()->assign("row", db::o()->fetch2array($res));
        if ($id)
            tpl::o()->assign("from_add", true);
        else
            tpl::o()->assign("from_add", false);
        tpl::o()->display("usercp/friends.tpl");
    }

    /**
     * Отображение закладок пользователя
     * @return null
     */
    public function show_bookmarks() {
        $res = db::o()->p(users::o()->v('id'))->query('SELECT b.*, t.title AS res_name FROM bookmarks AS b
            LEFT JOIN content AS t ON b.toid=t.id AND b.type="content"
            WHERE user_id=?
            ORDER BY b.added');
        tpl::o()->assign("row", db::o()->fetch2array($res));
        tpl::o()->display("usercp/bookmarks.tpl");
    }

}

class usercp_ajax {

    /**
     * Преинициализация AJAX части для объявления констант
     * @return null 
     */
    public function pre_init() {
        $act = $_GET ['act'];
        if ($act == "save_avatar")
            define('ALLOW_REQUEST_COOKIES', true);
    }

    /**
     * Инициализация Ajax-части панели управления пользователя
     * @return null
     */
    public function init() {
        lang::o()->get("registration");
        lang::o()->get("usercp");
        users::o()->check_perms();
        $act = $_GET ['act'];
        /* @var $mailer mailer */
        $mailer = n("mailer");
        switch ($act) {
            case "delete_mailer":
                $id = (int) $_POST["id"];
                $type = $_POST["type"];
                if (!$mailer->change_type($type)->remove($id))
                    throw new EngineException('unknown');
                break;
            case "make_mailer":
                $id = (int) $_POST["id"];
                $type = $_POST["type"];
                $interval = (int) $_POST["interval"];
                $upd = (bool) $_POST["upd"];
                if (!$mailer->change_type($type)->make($id, $interval, $upd))
                    throw new EngineException('unknown');
                break;
            case "add_bookmark" :
                users::o()->check_perms("content");
                $toid = $_POST ['toid'];
                $type = $_POST ['type'];
                $this->add_bookmark($toid, $type);
                break;
            case "delete_bookmark" :
                users::o()->check_perms("content");
                $id = $_POST ["id"];
                $type = $_POST ["type"];
                $this->delete_bookmark($id, $type);
                break;
            case "add_friend" :
                $username = $_POST ['username'];
                $type = $_POST ["type"];
                $this->add_friend($username, $type);
                break;
            case "delete_friend" :
                $id = $_POST ["id"];
                $this->delete_friend($id);
                break;
            case "change_tfriend" :
                $id = $_POST ["id"];
                $this->change_type_friend($id);
                break;
            case "add_invite" :
                users::o()->check_perms('invite');
                $this->create_invite();
                break;
            case "delete_invite" :
                users::o()->check_perms('invite');
                $invite_id = $_POST ['invite_id'];
                $this->delete_invite($invite_id);
                break;
            case "confirm_invite" :
                users::o()->check_perms('invite');
                $invite_id = $_POST ['invite_id'];
                $this->confirm_user($invite_id);
                break;
            case "index_ok" :
                $_POST['sid'] = $_GET['sid'];
                $_POST['uid'] = $_GET['id'];
                $this->save_main($_POST);
                break;
            case "save_avatar" :
                $this->save_avatar();
                break;
            case "clear_avatar" :
                $this->clear_avatar();
                break;
        }
        ok();
    }

    /**
     * Проверка сохраняемых полей
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @param bool $inadmin из АЦ?
     * @return null
     */
    protected function check_areas($data, &$error, $inadmin = false) {

        try {

            plugins::o()->pass_data(array('data' => &$data,
                'error' => &$error,
                'inadmin' => &$inadmin), true)->run_hook('usercp_check');

            extract(rex($data, array('oldpass',
                'password',
                'passagain',
                'email',
                'gender',
                'birthday_year',
                'interval',
                'username')));

            if ($birthday_year < 1930 || ($gender != "f" && $gender != "m"))
                $error [] = lang::o()->v('register_all_areas_must_be');
            if ($password || $oldpass || $passagain) {
                if (!$inadmin) {
                    $salt = users::o()->v('salt');
                    if (users::o()->generate_pwd_hash($oldpass, $salt) != users::o()->v('password'))
                        $error [] = lang::o()->v('usercp_false_oldpass');
                }
                if (!users::o()->check_password($password))
                    $error [] = lang::o()->v('register_len_pass');
                if ($passagain != $password)
                    $error [] = lang::o()->v('register_false_passagain');
            }
            if ($email != users::o()->v('email')) {
                $wbe = true;
                if (!users::o()->check_email($email, $wbe)) {
                    $error [] = lang::o()->v('register_false_email');
                    if ($wbe)
                        $error [] = $wbe;
                }
                if (db::o()->p($email)->count_rows("users", 'email=?'))
                    $error [] = lang::o()->v('register_email_exists');
            }
            if ($inadmin && mb_strtolower($username) != users::o()->v('username_lower')) {
                if (!users::o()->check_login($username))
                    $error [] = lang::o()->v('register_len_login');
                if (db::o()->p(mb_strtolower($username))->count_rows("users", 'username_lower=?'))
                    $error [] = lang::o()->v('register_user_exists');
            }
            /*
              if ($website)
              if (!preg_match('/' . display::url_pattern . '/siu', $website))
              $error [] = lang::o()->v('register_not_valid_website');
             */
            if (config::o()->v('mailer_on') && !mailer::$allowed_interval[$interval]) // пащимуто надо инициализировать, ну и хрен с ним
                $error [] = lang::o()->v('usercp_mailer_not_allowed_interval');
        } catch (PReturn $e) {
            return $e->r();
        }
    }

    /**
     * Сохранение настроек и данных пользователя
     * @param array $data данные юзера
     * @return null
     * @throws EngineException
     */
    protected function save_main($data) {
        $inadmin = users::o()->check_inadmin("users");
        /* @var $etc etc */
        $etc = n("etc");
        if ($inadmin) {
            $id = (int) $data['uid'];
            users::o()->set_tmpvars($etc->select_user($id));
            $sadmin = users::o()->perm("system");
            $gr = users::o()->get_group(users::o()->v('group'));
            $suser = $gr['system'];
            if ($suser && !$sadmin)
                throw new EngineException("access_denied");
        } else {
            $id = users::o()->v('id');
            users::o()->check_perms();
            check_formkey();
        }
        //$register = plugins::o()->get_module('registration');
        display::o()->remove_time_fields("his", "birthday");
        $birthday = display::o()->make_time("birthday", "ymd");
        $this->check_areas($data, $error, $inadmin);
        if ($error)
            throw new EngineException(implode("<br>", $error));
        extract(rex($data, array("email",
            "gid" => "group",
            "gender",
            "admin_email",
            "user_email",
            "use_dst",
            "timezone",
            "interval",
            "password",
            "email",
            "avatar_url",
            "username")));
        $update = array();
        if ($password) {
            $salt = users::o()->v('salt');
            $update ["password"] = users::o()->generate_pwd_hash($password, $salt);
            if (!$inadmin)
                users::o()->write_cookies(users::o()->v('username'), $update ["password"]);
        }
        if ($email != users::o()->v('email')) {
            if (config::o()->v('confirm_email') && !$inadmin) {
                $update ["new_email"] = $email;
                $update ["confirm_key"] = $etc->confirm_request($email, "confirm_email");
            }
            else
                $update ["email"] = $email;
        }
        $settings = rex($data, array("name_surname",
            "signature",
            'hidden',
            'announce_pk' => 'passkey',
            'show_age'));
        $settings["show_age"] = (bool) $settings["show_age"];
        $settings["hidden"] = users::o()->perm("behidden") || $inadmin ? (bool) $settings["hidden"] : 0;
        if (config::o()->v("torrents_on"))
            $settings['announce_pk'] = serialize($settings['announce_pk']);
        /* @var $uf userfields */
        $uf = n("userfields");
        $settings = array_merge($settings, $uf->change_type('profile')->save($data));

        if ($inadmin) {
            $gid = (int) $gid;
            if ($etc->change_group($id, $gid, true)) {
                $update['group'] = $gid;
                /* @var $groups groups_man */
                $groups = plugins::o()->get_module('groups', 1);
                $group = users::o()->get_group(users::o()->v('group'));
                $update ["add_permissions"] = $groups->save($data, $group);
            }
            $update['username'] = $username;
            $update['username_lower'] = mb_strtolower($username);
        }
        $update ["gender"] = $gender == "f" ? "f" : "m";
        $update ["admin_email"] = (bool) $admin_email;
        $update ["user_email"] = (bool) $user_email;
        $update ["timezone"] = (int) $timezone;
        $update ["dst"] = (bool) $use_dst;
        $update ["mailer_interval"] = (int) $interval;
        //print_r($update);
        if ($birthday)
            $update ["birthday"] = $birthday;
        if ($avatar_url && is(config::o()->v('allowed_avatar'), ALLOWED_AVATAR_URL)) {
            $this->clear_avatar(true);
            /* @var $uploader uploader */
            $uploader = n("uploader")->upload_via_url();
            $avatar_name = display::avatar_prefix . users::o()->v('id');
            $uploader->upload($avatar_url, config::o()->v('avatars_folder'), /* ссылка */ $tmp = 'avatars', $avatar_name);
            //$uploader->upload_via_url(true)->check($avatar_url, /* ссылка */ $tmp = 'avatars');
            $update ["avatar"] = $avatar_name;
        }

        try {
            plugins::o()->pass_data(array('update' => &$update,
                'settings' => &$settings), true)->run_hook('usercp_save_main');
        } catch (PReturn $e) {
            return $e->r();
        }

        $update ["settings"] = users::o()->make_settings($settings);
        users::o()->remove_tmpvars();
        db::o()->p($id)->update($update, "users", 'WHERE id=? LIMIT 1');
        if (!$inadmin) {
            users::o()->setcookie("theme", $data['theme']);
            users::o()->setcookie("lang", $data['lang']);
            users::o()->setcookie("theme_color", $data['theme_color']);
        }
        else
            log_add("changed_user", 'admin', null, $id);
    }

    /**
     * Создание инвайта
     * @return null
     */
    public function create_invite() {
        $row ["invite_id"] = users::o()->generate_salt();
        $row ["user_id"] = users::o()->v('id');
        db::o()->insert($row, "invites");
        tpl::o()->assign("row", array(
            $row));
        tpl::o()->display("usercp/invites.tpl");
        deny_ok();
    }

    /**
     * Подтверждение приглашённого пользователя
     * @param string $invite_id ключ инвайта
     * @return null
     * @throws EngineException
     */
    protected function confirm_user($invite_id) {
        /* @var $etc etc */
        $etc = n("etc");
        $res = db::o()->p($invite_id, users::o()->v('id'))->query('SELECT 
            i.to_userid, u.confirmed FROM invites AS i
            LEFT JOIN users AS u ON u.id=i.to_userid
            WHERE i.invite_id=? AND i.user_id=? LIMIT 1');
        $res = db::o()->fetch_assoc($res);
        if (!$res || !$res ["to_userid"])
            throw new EngineException;
        $ret = $etc->confirm_user(2, $res ["confirmed"], $res ["to_userid"]);
        if (!is_numeric($ret))
            throw new EngineException($ret);
        $this->delete_invite($invite_id);
        if (config::o()->v('bonus_per_invited'))
            $etc->add_res('bonus', config::o()->v('bonus_per_invited'));
    }

    /**
     * Удаление инвайта
     * @param string $invite_id ключ инвайта
     * @return null
     */
    public function delete_invite($invite_id) {
        db::o()->p($invite_id, users::o()->v('id'))->delete("invites", 'WHERE 
            invite_id=? AND user_id=? LIMIT 1');
    }

    /**
     * Добавление в друзья/враги пользователя
     * @param string $username имя добавляемого пользователя
     * @param string $type f - друг, b - враг
     * @return null
     * @throws EngineException
     */
    public function add_friend($username, $type = "f") {
        $type = ($type == "f" ? "f" : "b");
        /* @var $etc etc */
        $etc = n("etc");
        if (mb_strtolower($username) == users::o()->v("username_lower"))
            throw new EngineException('usercp_friends_yourself');
        $res = $etc->select_user(null, $username, "id,username,`group`,registered,gender,avatar");
        if (!$res)
            throw new EngineException('usercp_friends_not_exists');
        if (db::o()->p(users::o()->v('id'), $res ['id'])->count_rows("zebra", 'user_id=? AND to_userid=?'))
            throw new EngineException('usercp_friends_exists');
        $id = db::o()->insert(array(
            "user_id" => users::o()->v('id'),
            "to_userid" => $res ["id"],
            "type" => $type), "zebra");
        $res ["id"] = $id;
        $res ["user_id"] = users::o()->v('id');
        $res ["to_userid"] = $res ["id"];
        $res ["type"] = $type;
        tpl::o()->assign("row", array($res));
        tpl::o()->assign("from_add", true);
        ok(true);
        tpl::o()->display("usercp/friends.tpl");
        deny_ok();
    }

    /**
     * Удаление друга/врага
     * @param int $id ID записи друга/врага
     * @return null
     */
    public function delete_friend($id) {
        $id = (int) $id;
        db::o()->p($id, users::o()->v('id'))->delete("zebra", 'WHERE id=? AND user_id=? LIMIT 1');
    }

    /**
     * Смена типа друга/врага
     * @param int $id ID записи друга/врага
     * @return null
     */
    public function change_type_friend($id) {
        $id = (int) $id;
        db::o()->p($id, users::o()->v('id'))->update(array("_cb_type" => 'IF(type="f","b","f")'), "zebra", 'WHERE 
                id=? AND user_id=? LIMIT 1');
        /* @var $module usercp */
        $module = plugins::o()->get_module("usercp");
        ok(true);
        $module->show_friends($id);
        deny_ok();
    }

    /**
     * Функция добавления закладки
     * @param int $toid ID ресурса
     * @param string $type тип закладки
     * @return null
     * @throws EngineException
     */
    public function add_bookmark($toid, $type) {
        $toid = (int) $toid;
        if (!$toid || !$type)
            throw new EngineException;
        db::o()->no_error()->insert(array(
            "toid" => $toid,
            "type" => $type,
            "user_id" => users::o()->v('id'),
            "added" => time()), "bookmarks");
        if (db::o()->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException('usercp_bookmarks_this_already_exists');
    }

    /**
     * Функция удаления закладки
     * @param int $id ID закладки / ресурса
     * @param string $type тип ресурса
     * @return null
     */
    public function delete_bookmark($id, $type = null) {
        $id = (int) $id;
        db::o()->p(users::o()->v('id'), $id, $type)->delete("bookmarks", "WHERE 
            user_id=? AND " . (!$type ? 'id=?' : 'toid=? AND type=?') . ' LIMIT 1');
    }

    /**
     * Функция сохранения аватары
     * @return null
     * @throws EngineException
     */
    protected function save_avatar() {
        if (!is(config::o()->v('allowed_avatar'), ALLOWED_AVATAR_PC))
            return;
        $this->clear_avatar(true);
        $avatar_name = display::avatar_prefix . users::o()->v('id');
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $uploader->upload($_FILES ["Filedata"], config::o()->v('avatars_folder'), /* ссылка */ $tmp = 'avatars', $avatar_name);
        db::o()->p(users::o()->v('id'))->update(array(
            "avatar" => $avatar_name), "users", 'WHERE id=? LIMIT 1');
    }

    /**
     * Функция очистки аватары
     * @param bool $not_update не обновлять
     * @return null
     * @throws EngineException
     */
    protected function clear_avatar($not_update = false) {
        $avatar = users::o()->v('avatar');
        /* @var $etc etc */
        $etc = n("etc");
        $id = users::o()->v('id');
        if (!$not_update) {
            $inadmin = users::o()->check_inadmin("users");
            if (!$inadmin)
                check_formkey();
            else {
                $id = (int) $_GET['id'];
                $a = $etc->select_user($id, '', 'avatar');
                if (!$a)
                    throw new EngineException;
                $avatar = $a['avatar'];
            }
            db::o()->p($id)->update(array("avatar" => ""), "users", 'WHERE id=? LIMIT 1');
        }
        $etc->remove_user_avatar($id, $avatar);
    }

}

?>