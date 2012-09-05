<?php

/**
 * Project:             CTRev
 * File:                registration.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Регистрация нового аккаунта
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class registration {

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Инициализация регистрации
     * @global users $users
     * @global tpl $tpl
     * @global lang $lang
     * @global furl $furl
     * @global string $BASEURL
     * @return null
     */
    public function init() {
        global $users, $tpl, $lang, $furl, $BASEURL;
        if ($users->v())
            $furl->location('');
        $step = $_REQUEST ['step'];
        if (!$step && $_GET['act'])
            $step = $_GET['act'];
        if ($ckey = $_GET['ckey'])
            $step = 'confirm';
        $lang->get('registration');
        switch ($step) {
            case 'captcha':
                $this->captcha();
                break;
            case 'confirm':
                $this->confirm($ckey);
                break;
            case 'main':
                $step = 1;
            case 1 :
            case 2 :
            case 3 :
            case 4 :
            case 'last' :
                $this->title = $lang->v('register_page');
                $this->step_by_step($step, $_POST);
                break;
            default :
                $this->title = $lang->v('uagree_page');
                $tpl->display("register/user_agreements.tpl");
                break;
        }
    }

    /**
     * Инициализация капчи
     * @global captcha $captcha
     * @return null
     */
    protected function captcha() {
        global $captcha;
        ob_end_clean();
        try {
            $captcha->init();
            die();
        } catch (EngineException $e) {
            $e->defaultCatch(true);
        }
    }

    /**
     * Подтверждение пользователя
     * @global users $users
     * @global db $db
     * @global furl $furl
     * @global etc $etc
     * @param string $key ключ подтверждения
     * @return null
     * @throws EngineException
     */
    protected function confirm($key) {
        global $users, $db, $furl, $etc;
        $users->check_perms();
        if ($users->v('confirm_key') != $key)
            throw new EngineException('false_confirm_key');
        if ($users->v('confirmed') != 3)
            $update ['confirmed'] = $etc->confirm_user(1, $users->v('confirmed'));
        if ($users->v('new_email')) {
            $update ['email'] = $users->v('new_email');
            $update ['new_email'] = "";
        }
        $update ['confirm_key'] = "";
        $db->update($update, 'users', 'WHERE id=' . $users->v('id') . " LIMIT 1");
        $furl->location('', 5);
        mess("successfull_confirmed_email", null, "success", false);
    }

    /**
     * Проверка полей при регистрации
     * @global db $db
     * @global lang $lang
     * @global users $users
     * @global captcha $captcha
     * @global display $display
     * @global config $config
     * @param array $error массив возникших ошибок
     * @param integer|string $step стадия регистрации
     * @param array $data массив данных
     * @param int $referer_id ID реферера
     * @return null
     */
    protected function check_steps(&$error, $step, $data, &$referer_id = null) {
        global $db, $lang, $users, $captcha, $display, $config;
        if ($step == "last")
            $step = 9001; // Максимально-возможная стадия ;)
        $cols = array('login' => 'username',
            'password',
            'passagain',
            'email',
            'gender',
            'website',
            'invite',
            'birthday_year');
        extract(rex($data, $cols));
        if (!$login || !$password || !$passagain || !$email)
            $error [] = $lang->v('register_all_areas_must_be');
        if ($db->count_rows("users", ( 'username_lower=' . $db->esc(mb_strtolower($login)))))
            $error [] = $lang->v('register_user_exists');
        if ($db->count_rows("users", ( 'email=' . $db->esc($email))))
            $error [] = $lang->v('register_email_exists');
        if (!$users->check_login($login))
            $error [] = $lang->v('register_len_login');
        if (!$users->check_password($password))
            $error [] = $lang->v('register_len_pass');
        if ($passagain != $password)
            $error [] = $lang->v('register_false_passagain');
        $wbe = true;
        if (!$users->check_email($email, $wbe)) {
            $error [] = $lang->v('register_false_email');
            if ($wbe)
                $error [] = $wbe;
        }
        ref($captcha)->check($error);
        if ($step >= 2) {
            $birthday = $display->make_time("birthday", "ymd");
            if (!$birthday || $birthday_year < 1930 || ($gender != "f" && $gender != "m"))
                $error [] = $lang->v('register_all_areas_must_be');
        }
        if ($step >= 3) {
            if ($website)
                if (!preg_match('/' . display::url_pattern . '/siu', $website))
                    $error [] = $lang->v('register_not_valid_website');
        }
        if ($step >= 4) {
            if ($config->v('allowed_invite')) {
                $referer_id = $db->fetch_assoc($db->query('SELECT
                user_id FROM invites WHERE invite_id=' . $db->esc($invite) . '
                AND to_userid=0 LIMIT 1'));
                $referer_id = $referer_id ["user_id"];
                if (longval($referer_id) == 0 && ($invite || !$config->v('allowed_register')))
                    $error [] = $lang->v('register_invalid_invite_code');
            }
        }
    }

    /**
     * Переход по степеням в регистрации
     * @global tpl $tpl
     * @global db $db
     * @global etc $etc
     * @global users $users
     * @global config $config
     * @global captcha $captcha
     * @global display $display
     * @global plugins $plugins
     * @param integer|string $step текущая стадия регистрации
     * @param array $data массив данных
     * @return null
     * @throws EngineException
     */
    protected function step_by_step($step, $data) {
        global $tpl, $db, $etc, $users, $config, $captcha, $display, $plugins;
        $error = array();
        $captcha->clear("old");
        if ($data ['to_check'] && is_numeric($step)) {
            $this->check_steps($error, $step, $data);
            if (!$error)
                $error = "OK!";
            else
                $error = implode("<br>", $error);
            throw new EngineException($error);
        } elseif ($step == "last") {
            if (!$config->v('allowed_register') && !$config->v('allowed_invite'))
                die("ERROR!");
            $refered_by = 0;
            $this->check_steps($error, $step, $data, $refered_by);
            if ($error)
                throw new EngineException(implode("<br>", $error));
            $salt = $users->generate_salt();
            $display->remove_time_fields("his", "birthday");
            $birthday = $display->make_time("birthday", "ymd");
            $cols = array('username',
                'password',
                'email',
                'gender',
                'timezone',
                'admin_email',
                'user_email',
                'use_dst',
                'invite');
            extract(rex($data, $cols));
            $password = $users->generate_pwd_hash($password, $salt);
            $update = array(
                "username" => $username,
                "username_lower" => mb_strtolower($username),
                "passkey" => $users->generate_salt(),
                "password" => $password,
                "salt" => $salt,
                "registered" => time(),
                "birthday" => $birthday,
                "email" => $email,
                "confirmed" => longval($etc->confirm_user(0, 0)),
                "group" => $users->find_group('default'),
                "refered_by" => $refered_by,
                "confirm_key" => ($config->v('confirm_email') ? $etc->confirm_request($email, "confirm_register") : ""));
            $update ["gender"] = $gender == "f" ? "f" : "m";
            $update ["admin_email"] = (bool) $admin_email;
            $update ["user_email"] = (bool) $user_email;
            $update ["dst"] = (bool) $use_dst;
            $update ["timezone"] = (int) $timezone;
            $cols = array("website",
                "icq",
                "skype",
                "country",
                "town",
                "name_surname" => 'name');
            $settings = rex($data, $cols);
            $settings["country"] = (int) $settings["country"];
            $settings["show_age"] = (bool) $data ['show_age'];
            
            try {
                $plugins->pass_data(array('update' => &$update,
                    'settings' => &$settings), true)->run_hook('register_user');
            } catch (PReturn $e) {
                return $e->r();
            }
            
            $update['settings'] = $users->make_settings($settings);
            $id = $db->insert($update, "users");
            if ($invite)
                $db->update(array(
                    "to_userid" => $id), "invites", ( 'WHERE invite_id=' . $db->esc($invite) . ' LIMIT 1'));
            elseif (!$config->v('confirm_email') && !$config->v('confirm_admin'))
                $users->write_cookies($username, $password);
            $captcha->clear("user");
            die('OK!');
        }
        $tpl->display("register/main_step.tpl");
    }

}

?>