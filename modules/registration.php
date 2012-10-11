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
     * @var string $title
     */
    public $title = "";

    /**
     * Инициализация регистрации
     * @return null
     */
    public function init() {
        if (users::o()->v())
            furl::o()->location('');
        $step = $_REQUEST ['step'];
        if (!$step && $_GET['act'])
            $step = $_GET['act'];
        if ($ckey = $_GET['ckey'])
            $step = 'confirm';
        lang::o()->get('registration');
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
                $this->title = lang::o()->v('register_page');
                $this->step_by_step($step, $_POST);
                break;
            default :
                $this->title = lang::o()->v('uagree_page');
                tpl::o()->display("register/user_agreements.tpl");
                break;
        }
    }

    /**
     * Инициализация капчи
     * @return null
     */
    protected function captcha() {
        ob_end_clean();
        try {
            n("captcha")->init();
            die();
        } catch (EngineException $e) {
            $e->defaultCatch(true);
        }
    }

    /**
     * Подтверждение пользователя
     * @param string $key ключ подтверждения
     * @return null
     * @throws EngineException
     */
    protected function confirm($key) {
        users::o()->check_perms();
        if (users::o()->v('confirm_key') != $key)
            throw new EngineException('false_confirm_key');
        if (users::o()->v('confirmed') != 3) {
            /* @var $etc etc */
            $etc = n("etc");
            $update ['confirmed'] = $etc->confirm_user(1, users::o()->v('confirmed'));
        }
        if (users::o()->v('new_email')) {
            $update ['email'] = users::o()->v('new_email');
            $update ['new_email'] = "";
        }
        $update ['confirm_key'] = "";
        db::o()->update($update, 'users', 'WHERE id=' . users::o()->v('id') . " LIMIT 1");
        furl::o()->location('', 5);
        message("successfull_confirmed_email", null, "success", false);
    }

    /**
     * Проверка полей при регистрации
     * @param array $error массив возникших ошибок
     * @param integer|string $step стадия регистрации
     * @param array $data массив данных
     * @param int $referer_id ID реферера
     * @return null
     */
    protected function check_steps(&$error, $step, $data, &$referer_id = null) {
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
            $error [] = lang::o()->v('register_all_areas_must_be');
        if (db::o()->count_rows("users", ( 'username_lower=' . db::o()->esc(mb_strtolower($login)))))
            $error [] = lang::o()->v('register_user_exists');
        if (db::o()->count_rows("users", ( 'email=' . db::o()->esc($email))))
            $error [] = lang::o()->v('register_email_exists');
        if (!users::o()->check_login($login))
            $error [] = lang::o()->v('register_len_login');
        if (!users::o()->check_password($password))
            $error [] = lang::o()->v('register_len_pass');
        if ($passagain != $password)
            $error [] = lang::o()->v('register_false_passagain');
        $wbe = true;
        if (!users::o()->check_email($email, $wbe)) {
            $error [] = lang::o()->v('register_false_email');
            if ($wbe)
                $error [] = $wbe;
        }
        n("captcha")->check($error);
        if ($step >= 2) {
            $birthday = display::o()->make_time("birthday", "ymd");
            if (!$birthday || $birthday_year < 1930 || ($gender != "f" && $gender != "m"))
                $error [] = lang::o()->v('register_all_areas_must_be');
        }
        if ($step >= 3) {
            if ($website)
                if (!preg_match('/' . display::url_pattern . '/siu', $website))
                    $error [] = lang::o()->v('register_not_valid_website');
        }
        if ($step >= 4) {
            if (config::o()->v('allowed_invite')) {
                $referer_id = db::o()->fetch_assoc(db::o()->query('SELECT
                user_id FROM invites WHERE invite_id=' . db::o()->esc($invite) . '
                AND to_userid=0 LIMIT 1'));
                $referer_id = $referer_id ["user_id"];
                if (longval($referer_id) == 0 && ($invite || !config::o()->v('allowed_register')))
                    $error [] = lang::o()->v('register_invalid_invite_code');
            }
        }
    }

    /**
     * Переход по степеням в регистрации
     * @param integer|string $step текущая стадия регистрации
     * @param array $data массив данных
     * @return null
     * @throws EngineException
     */
    protected function step_by_step($step, $data) {
        $error = array();
        /* @var $captcha captcha */
        $captcha = n("captcha");
        $captcha->clear("old");
        if ($data ['to_check'] && is_numeric($step)) {
            $this->check_steps($error, $step, $data);
            if (!$error)
                $error = "OK!";
            else
                $error = implode("<br>", $error);
            throw new EngineException($error);
        } elseif ($step == "last") {
            if (!config::o()->v('allowed_register') && !config::o()->v('allowed_invite'))
                die("ERROR!");
            $refered_by = 0;
            /* @var $etc etc */
            $etc = n("etc");
            $this->check_steps($error, $step, $data, $refered_by);
            if ($error)
                throw new EngineException(implode("<br>", $error));
            $salt = users::o()->generate_salt();
            display::o()->remove_time_fields("his", "birthday");
            $birthday = display::o()->make_time("birthday", "ymd");
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
            $password = users::o()->generate_pwd_hash($password, $salt);
            $update = array(
                "username" => $username,
                "username_lower" => mb_strtolower($username),
                "passkey" => users::o()->generate_salt(),
                "password" => $password,
                "salt" => $salt,
                "registered" => time(),
                "birthday" => $birthday,
                "email" => $email,
                "confirmed" => longval($etc->confirm_user(0, 0)),
                "group" => users::o()->find_group('default'),
                "refered_by" => $refered_by,
                "confirm_key" => (config::o()->v('confirm_email') ? $etc->confirm_request($email, "confirm_register") : ""));
            if (config::o()->v('bonus_by_default'))
                $update['bonus_count'] = config::o()->v('bonus_by_default');
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
                plugins::o()->pass_data(array('update' => &$update,
                    'settings' => &$settings), true)->run_hook('register_user');
            } catch (PReturn $e) {
                return $e->r();
            }

            $update['settings'] = users::o()->make_settings($settings);
            $id = db::o()->insert($update, "users");
            if ($invite)
                db::o()->update(array(
                    "to_userid" => $id), "invites", ( 'WHERE invite_id=' . db::o()->esc($invite) . ' LIMIT 1'));
            elseif (!config::o()->v('confirm_email') && !config::o()->v('confirm_admin'))
                users::o()->write_cookies($username, $password);
            $captcha->clear("user");
            die('OK!');
        }
        tpl::o()->display("register/main_step.tpl");
    }

}

?>