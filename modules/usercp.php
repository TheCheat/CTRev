<?php

/**
 * Project:             CTRev
 * File:                usercp.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @var string
     */
    public $title = "";

    /**
     * Верхнее меню панели
     * @var array
     */
    protected $menu = array(
        "index",
        "invites",
        "friends",
        "bookmarks",
        "mailer");

    /**
     * Инициализация панели управления пользователя
     * @global lang $lang
     * @global tpl $tpl
     * @global users $users
     * @global mailer $mailer
     * @return null
     */
    public function init() {
        global $lang, $tpl, $users, $mailer;
        $users->check_perms();
        $lang->get("registration");
        $lang->get("usercp");
        $act = $_GET ['act'];
        if ($pos = array_search("invites", $this->menu) && !$users->perm("invite"))
            unset($this->menu [$pos]);
        if ($pos = array_search("bookmarks", $this->menu) && !$users->perm("torrents"))
            unset($this->menu [$pos]);
        if (!in_array($act, $this->menu))
            $act = "index";
        $tpl->assign("menuacts", $this->menu);
        $tpl->assign("curact", $act);
        if ($act != "bookmarks" || $users->perm('torrents'))
            $tpl->display("usercp/header.tpl");
        $this->title = $lang->v("usercp_" . $act);
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
                $mailer->show();
                break;
            default :
                $this->show_index();
                break;
        }
        $tpl->display("usercp/footer.tpl");
    }

    /**
     * Отображение основной части панели управления пользователя
     * @global tpl $tpl
     * @global uploader $uploader
     * @global config $config
     * @global users $users
     * @global display $display
     * @global lang $lang
     * @return null
     */
    public function show_index() {
        global $tpl, $uploader, $config, $users, $display, $lang;
        $uploader->init_ft();
        $display->display_uploadify("ava", $lang->v('usercp_avatars_ext'), 'avatars', array(
            "module" => "usercp",
            "act" => "save_avatar"));
        try {
            $uploader->check($users->v('avatar'), /* ссылка */ $tmp = 'avatars', false);
            $tpl->assign("current_avatar", $users->v('avatar'));
        } catch (EngineException $e) {
            
        }
        $users->decode_settings();
        $users->unserialize('announce_pk');
        $pk = array();
        if ($config->v('get_pk')) {
            $pk = explode("\n", $config->v('get_pk'));
            $c = count($pk);
            for ($i = 0; $i < $c; $i++) {
                $pk[$i] = trim($pk[$i]);
                if (!$pk[$i])
                    unset($pk[$i]);
                $pk[$i] = preg_split('/\s+/siu', $pk[$i]);
            }
        }
        $tpl->assign('user_pk', $pk);
        $tpl->display("usercp/main.tpl");
    }

    /**
     * Отображение таблицы инвайтов
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @return null
     */
    protected function show_invites() {
        global $tpl, $db, $users;
        $res = $db->query('SELECT i.*, u.username, u.registered, u.confirmed, u.`group`
            FROM invites AS i
            LEFT JOIN users AS u ON i.to_userid=u.id
            WHERE i.user_id=' . $users->v('id'));
        $tpl->assign("row", $db->fetch2array($res));
        $tpl->display("usercp/invites.tpl");
    }

    /**
     * Отображение друзей
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @param int $id ID записи
     * @return null
     */
    public function show_friends($id = null) {
        global $tpl, $db, $users;
        $id = (int) $id;
        $res = $db->query('SELECT z.*, u.username, u.group, u.registered, u.gender, u.avatar
            FROM zebra AS z
            LEFT JOIN users AS u ON u.id=z.to_userid
            WHERE z.user_id=' . $users->v('id') . ($id ? ' AND z.id=' . $id : ""));
        $tpl->assign("row", $db->fetch2array($res));
        if ($id)
            $tpl->assign("from_add", true);
        else
            $tpl->assign("from_add", false);
        $tpl->display("usercp/friends.tpl");
    }

    /**
     * Отображение закладок пользователя
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @return null
     */
    protected function show_bookmarks() {
        global $db, $tpl, $users;
        $res = $db->query('SELECT b.*, t.title AS res_name FROM bookmarks AS b
            LEFT JOIN torrents AS t ON b.toid=t.id AND b.type="torrents"
            WHERE user_id=' . $users->v('id') . '
            ORDER BY b.added');
        $tpl->assign("row", $db->fetch2array($res));
        $tpl->display("usercp/bookmarks.tpl");
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
     * @global lang $lang
     * @global users $users
     * @global mailer $mailer
     * @return null
     */
    public function init() {
        global $lang, $users;
        $lang->get("registration");
        $lang->get("usercp");
        $users->check_perms();
        $act = $_GET ['act'];
        switch ($act) {
            case "delete_mailer":
                global $mailer;
                $id = (int) $_POST["id"];
                $type = $_POST["type"];
                if ($mailer->change_type($type)->remove($id))
                    die("OK!");
                else
                    die('unknown');
                break;
            case "make_mailer":
                global $mailer;
                $id = (int) $_POST["id"];
                $type = $_POST["type"];
                $interval = (int) $_POST["interval"];
                $upd = (bool) $_POST["upd"];
                if ($mailer->change_type($type)->make($id, $interval, $upd))
                    die("OK!");
                else
                    throw new EngineException('unknown');
                break;
            case "add_bookmark" :
                $users->check_perms('torrents');
                $toid = $_POST ['toid'];
                $type = $_POST ['type'];
                $this->add_bookmark($toid, $type);
                die("OK!");
                break;
            case "delete_bookmark" :
                $users->check_perms('torrents');
                $id = $_POST ["id"];
                $type = $_POST ["type"];
                $this->delete_bookmark($id, $type);
                die("OK!");
                break;
            case "add_friend" :
                $username = $_POST ['username'];
                $type = $_POST ["type"];
                $this->add_friend($username, $type);
                die();
                break;
            case "delete_friend" :
                $id = $_POST ["id"];
                $this->delete_friend($id);
                die("OK!");
                break;
            case "change_tfriend" :
                $id = $_POST ["id"];
                $this->change_type_friend($id);
                die();
                break;
            case "add_invite" :
                $users->check_perms('invite');
                $this->create_invite();
                break;
            case "delete_invite" :
                $users->check_perms('invite');
                $invite_id = $_POST ['invite_id'];
                $this->delete_invite($invite_id);
                die("OK!");
                break;
            case "confirm_invite" :
                $users->check_perms('invite');
                $invite_id = $_POST ['invite_id'];
                $this->confirm_user($invite_id);
                break;
            case "index_ok" :
                $_POST['sid'] = $_GET['sid'];
                $_POST['uid'] = $_GET['id'];
                $this->save_main($_POST);
                die("OK!");
                break;
            case "save_avatar" :
                $this->save_avatar();
                die("OK!");
                break;
            case "clear_avatar" :
                $this->clear_avatar();
                die("OK!");
                break;
            default :
                break;
        }
    }

    /**
     * Проверка сохраняемых полей
     * @global db $db
     * @global users $users
     * @global lang $lang
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @param bool $inadmin из АЦ?
     * @return null
     */
    protected function check_areas($data, &$error, $inadmin = false) {
        global $db, $users, $lang;
        extract(rex($data, array('oldpass',
                    'password',
                    'passagain',
                    'email',
                    'gender',
                    'birthday_year',
                    'website',
                    'interval',
                    'username')));
        if ($birthday_year < 1930 || ($gender != "f" && $gender != "m"))
            $error [] = $lang->v('register_all_areas_must_be');
        if ($password || $oldpass || $passagain) {
            if (!$inadmin) {
                $salt = $users->v('salt');
                if ($users->generate_pwd_hash($oldpass, $salt) != $users->v('password'))
                    $error [] = $lang->v('usercp_false_oldpass');
            }
            if (!$users->check_password($password))
                $error [] = $lang->v('register_len_pass');
            if ($passagain != $password)
                $error [] = $lang->v('register_false_passagain');
        }
        if ($email != $users->v('email')) {
            $wbe = true;
            if (!$users->check_email($email, $wbe)) {
                $error [] = $lang->v('register_false_email');
                if ($wbe)
                    $error [] = $wbe;
            }
            if ($db->count_rows("users", ('email=' . $db->esc($email))))
                $error [] = $lang->v('register_email_exists');
        }
        if ($inadmin && mb_strtolower($username) != $users->v('username_lower')) {
            if (!$users->check_login($username))
                $error [] = $lang->v('register_len_login');
            if ($db->count_rows("users", ('username_lower=' . $db->esc(mb_strtolower($username)))))
                $error [] = $lang->v('register_user_exists');
        }
        if ($website)
            if (!preg_match('/' . display::url_pattern . '/siu', $website))
                $error [] = $lang->v('register_not_valid_website');
        if (!mailer::$allowed_interval[$interval]) // пащимуто надо инициализировать, ну и хрен с ним
            $error [] = $lang->v('usercp_mailer_not_allowed_interval');
    }

    /**
     * Сохранение настроек и данных пользователя
     * @global users $users
     * @global config $config
     * @global db $db
     * @global uploader $uploader
     * @global display $display
     * @global etc $etc
     * @global plugins $plugins
     * @param array $data данные юзера
     * @return null
     * @throws EngineException
     */
    protected function save_main($data) {
        global $users, $config, $db, $uploader, $display, $etc, $plugins;
        $inadmin = $users->check_inadmin("users");
        if ($inadmin) {
            $id = (int) $data['uid'];
            $users->set_tmpvars($etc->select_user($id));
            $sadmin = $users->perm("system");
            $gr = $users->get_group($users->v('group'));
            $suser = $gr['system'];
            if ($suser && !$sadmin)
                throw new EngineException("access_denied");
        } else {
            $id = $users->v('id');
            $users->check_perms();
            check_formkey();
        }
        //$register = $plugins->get_module('registration');
        $display->remove_time_fields("his", "birthday");
        $birthday = $display->make_time("birthday", "ymd");
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
            $salt = $users->v('salt');
            $update ["password"] = $users->generate_pwd_hash($password, $salt);
            if (!$inadmin)
                $users->write_cookies($users->v('username'), $update ["password"]);
        }
        if ($email != $users->v('email')) {
            if ($config->v('confirm_email') && !$inadmin) {
                $update ["new_email"] = $email;
                $update ["confirm_key"] = $etc->confirm_request($email, "confirm_email");
            } else
                $update ["email"] = $email;
        }
        $settings = rex($data, array("website",
            "icq",
            "skype",
            "country",
            "town",
            "name_surname",
            "signature",
            'hidden',
            'announce_pk' => 'passkey',
            'show_age'));
        $settings["show_age"] = (bool) $settings["show_age"];
        $settings["hidden"] = $users->perm("behidden") || $inadmin ? (bool) $settings["hidden"] : 0;
        $settings["country"] = (int) $settings["country"];
        $settings['announce_pk'] = serialize($settings['announce_pk']);

        if ($inadmin) {
            $gid = (int) $gid;
            if ($etc->change_group($id, $gid, true)) {
                $update['group'] = $gid;
                $groups = $plugins->get_module('groups', 1);
                $group = $users->get_group($users->v('group'));
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
        $update ["mailer_interval"] = $interval;
        //print_r($update);
        if ($birthday)
            $update ["birthday"] = $birthday;
        if ($avatar_url && is($config->v('allowed_avatar'), ALLOWED_AVATAR_URL)) {
            $uploader->init_ft();
            $uploader->check($avatar_url, /* ссылка */ $tmp = 'avatars', false);
            $this->clear_avatar(true);
            $update ["avatar"] = $avatar_url;
        }

        try {
            $plugins->pass_data(array('update' => &$update,
                'settings' => &$settings), true)->run_hook('usercp_save_main');
        } catch (PReturn $e) {
            return $e->r();
        }

        $update ["settings"] = $users->make_settings($settings);
        $users->remove_tmpvars();
        $db->update($update, "users", 'WHERE id=' . $id . " LIMIT 1");
        if (!$inadmin) {
            $users->setcookie("theme", $data['theme']);
            $users->setcookie("lang", $data['lang']);
        } else
            log_add("changed_user", 'admin', null, $id);
    }

    /**
     * Создание инвайта
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @return null
     */
    protected function create_invite() {
        global $db, $users, $tpl;
        $row ["invite_id"] = $users->generate_salt();
        $row ["user_id"] = $users->v('id');
        $db->insert($row, "invites");
        $tpl->assign("row", array(
            $row));
        $tpl->display("usercp/invites.tpl");
    }

    /**
     * Подтверждение приглашённого пользователя
     * @global db $db
     * @global users $users
     * @global etc $etc
     * @global config $config
     * @param string $invite_id ключ инвайта
     * @return null
     * @throws EngineException
     */
    protected function confirm_user($invite_id) {
        global $db, $users, $etc, $config;
        $res = $db->query('SELECT i.to_userid, u.confirmed FROM invites AS i
            LEFT JOIN users AS u ON u.id=i.to_userid
            WHERE i.invite_id=' . $db->esc($invite_id) . '
                AND i.user_id=' . $users->v('id') . ' LIMIT 1');
        $res = $db->fetch_assoc($res);
        if (!$res || !$res ["to_userid"])
            throw new EngineException;
        $ret = $etc->confirm_user(2, $res ["confirmed"], $res ["to_userid"]);
        if (!is_numeric($ret))
            throw new EngineException($ret);
        $this->delete_invite($invite_id);
        if ($config->v('bonus_per_invited'))
            $etc->add_res('bonus', $config->v('bonus_per_invited'));
    }

    /**
     * Удаление инвайта
     * @global db $db
     * @global users $users
     * @param string $invite_id ключ инвайта
     * @return null
     */
    protected function delete_invite($invite_id) {
        global $db, $users;
        $db->delete("invites", 'WHERE invite_id=' . $db->esc($invite_id) . ' AND user_id=' . $users->v('id') . " LIMIT 1");
    }

    /**
     * Добавление в друзья/враги пользователя
     * @global db $db
     * @global etc $etc
     * @global tpl $tpl
     * @global users $users
     * @param string $username имя добавляемого пользователя
     * @param string $type f - друг, b - враг
     * @return null
     * @throws EngineException
     */
    protected function add_friend($username, $type = "f") {
        global $db, $etc, $tpl, $users;
        $type = ($type == "f" ? "f" : "b");
        $res = $etc->select_user(null, $username, "id,username,`group`,registered,gender,avatar");
        if (!$res)
            throw new EngineException('usercp_friends_not_exists');
        if ($db->count_rows("zebra", ('user_id=' . $users->v('id') . ' AND to_userid=' . $res ['id'])))
            throw new EngineException('usercp_friends_exists');
        $id = $db->insert(array(
            "user_id" => $users->v('id'),
            "to_userid" => $res ["id"],
            "type" => $type), "zebra");
        $res ["id"] = $id;
        $res ["user_id"] = $users->v('id');
        $res ["to_userid"] = $res ["id"];
        $res ["type"] = $type;
        $tpl->assign("row", array($res));
        $tpl->assign("from_add", true);
        print ("OK!");
        $tpl->display("usercp/friends.tpl");
    }

    /**
     * Удаление друга\врага
     * @global db $db
     * @global users $users
     * @param int $id ID записи друга\врага
     * @return null
     */
    protected function delete_friend($id) {
        global $db, $users;
        $id = (int) $id;
        $db->delete("zebra", 'WHERE id=' . $id . ' AND user_id=' . $users->v('id') . " LIMIT 1");
    }

    /**
     * Смена типа друга\врага
     * @global db $db
     * @global users $users
     * @global plugins $plugins
     * @param int $id ID записи друга\врага
     * @return null
     */
    protected function change_type_friend($id) {
        global $db, $users, $plugins;
        $id = (int) $id;
        $db->update(array(
            "_cb_type" => 'IF(type="f","b","f")'), "zebra", 'WHERE id=' . $id . ' AND user_id=' . $users->v('id') . " LIMIT 1");
        $module = $plugins->get_module("usercp");
        print ("OK!");
        $module->show_friends($id);
    }

    /**
     * Функция добавления закладки
     * @global db $db
     * @global users $users
     * @param int $toid ID ресурса
     * @param string $type тип закладки
     * @return null
     * @throws EngineException
     */
    protected function add_bookmark($toid, $type) {
        global $db, $users;
        $toid = (int) $toid;
        if (!$toid || !$type)
            throw new EngineException;
        $db->no_error()->insert(array(
            "toid" => $toid,
            "type" => $type,
            "user_id" => $users->v('id'),
            "added" => time()), "bookmarks");
        //if ($db->count_rows ( "bookmarks", ('toid=' . $toid . ' AND user_id=' . $users->v('id') . ' AND type=' . $db->esc ( $type )) ))
        if ($db->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException('usercp_bookmarks_this_already_exists');
    }

    /**
     * Функция удаления закладки
     * @global db $db
     * @global users $users
     * @param int $id ID закладки / ресурса
     * @param string $type тип ресурса
     * @return null
     */
    protected function delete_bookmark($id, $type = null) {
        global $db, $users;
        $id = (int) $id;
        $db->delete("bookmarks", ("WHERE " . (!$type ? 'id=' . $id : 'toid=' . $id . '
		AND type=' . $db->esc($type)) . '
		AND user_id=' . $users->v('id')) . " LIMIT 1");
    }

    /**
     * Функция сохранения аватары
     * @global config $config
     * @global uploader $uploader
     * @global users $users
     * @global db $db
     * @return null
     * @throws EngineException
     */
    protected function save_avatar() {
        global $config, $uploader, $users, $db;
        if (!is($config->v('allowed_avatar'), ALLOWED_AVATAR_PC))
            return;
        $this->clear_avatar(true);
        $avatar_name = display::avatar_prefix . $users->v('id');
        $uploader->init_ft();
        $uploader->upload($_FILES ["Filedata"], $config->v('avatars_folder'), /* ссылка */ $tmp = 'avatars', $avatar_name);
        $db->update(array(
            "avatar" => $avatar_name), "users", 'WHERE id=' . $users->v('id') . " LIMIT 1");
    }

    /**
     * Функция очистки аватары
     * @global db $db
     * @global users $users
     * @global etc $etc
     * @param bool $not_update не обновлять
     * @return null
     * @throws EngineException
     */
    protected function clear_avatar($not_update = false) {
        global $db, $users, $etc;
        $avatar = $users->v('avatar');
        if (!$not_update) {
            $inadmin = $users->check_inadmin("users");
            if (!$inadmin) {
                $id = $users->v('id');
                check_formkey();
            } else {
                $id = (int) $_GET['id'];
                $a = $etc->select_user($id, '', 'avatar');
                if (!$a)
                    throw new EngineException;
                $avatar = $a['avatar'];
            }
            $db->update(array(
                "avatar" => ""), "users", 'WHERE id=' . $id . " LIMIT 1");
        }
        $etc->remove_user_avatar($id, $avatar);
    }

}

?>