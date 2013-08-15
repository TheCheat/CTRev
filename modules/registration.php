<?php

/**
 * Project:             CTRev
 * @file                modules/registration.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
        $step = $_REQUEST ['step'];
        if (!$step && $_GET['act'])
            $step = $_GET['act'];
        if ($ckey = $_GET['ckey'])
            $step = 'confirm';
        lang::o()->get('registration');
        if (users::o()->v() && $step != 'captcha')
            furl::o()->location('');
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
        db::o()->p(users::o()->v('id'))->update($update, 'users', 'WHERE id=? LIMIT 1');
        furl::o()->location('', 5);
        n("message")->stype("success")->info("successfull_confirmed_email");
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
        try {
            plugins::o()->pass_data(array('data' => &$data,
                'error' => &$error,
                'step' => &$step,
                'referer_id' => &$referer_id), true)->run_hook('registration_check_begin');

            $cols = array('login' => 'username',
                'password',
                'passagain',
                'email',
                'gender',
                'invite',
                'birthday_year');
            extract(rex($data, $cols));

            if (!$login || !$password || !$passagain || !$email)
                $error [] = lang::o()->v('register_all_areas_must_be');
            if (db::o()->p(mb_strtolower($login))->count_rows("users", 'username_lower=?'))
                $error [] = lang::o()->v('register_user_exists');
            if (db::o()->p($email)->count_rows("users", 'email=?'))
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

            plugins::o()->run_hook('registration_check_steps');

            if ($step >= 2) {
                $birthday = display::o()->make_time("birthday", "ymd");
                if (!$birthday || $birthday_year < 1930 || ($gender != "f" && $gender != "m"))
                    $error [] = lang::o()->v('register_all_areas_must_be');
            }
            /*
              if ($step >= 3) {
              if ($website)
              if (!preg_match('/' . display::url_pattern . '/siu', $website))
              $error [] = lang::o()->v('register_not_valid_website');
              }
             */
            if ($step >= 4) {
                if (config::o()->v('allowed_invite')) {
                    $referer_id = db::o()->fetch_assoc(db::o()->p($invite)->query('SELECT
                user_id FROM invites WHERE invite_id=?
                AND to_userid=0 LIMIT 1'));
                    $referer_id = $referer_id ["user_id"];
                    if (longval($referer_id) == 0 && ($invite || !config::o()->v('allowed_register')))
                        $error [] = lang::o()->v('register_invalid_invite_code');
                }
            }
        } catch (PReturn $e) {
            return $e->r();
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
        /* @var $uf userfields */
        $uf = n("userfields")->change_type('register'); // для input_userfields и метода save
        if ($data ['to_check'] && is_numeric($step)) {
            $this->check_steps($error, $step, $data);
            if ($step >= 3)
                try {
                    $uf->save($data);
                } catch (EngineException $e) {
                    $error[] = $e->getEMessage();
                }
            if (!$error)
                ok();
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
                "refered_by" => (int) $refered_by,
                "confirm_key" => (config::o()->v('confirm_email') ? $etc->confirm_request($email, "confirm_register") : ""));
            if (config::o()->v('bonus_by_default'))
                $update['bonus_count'] = config::o()->v('bonus_by_default');
            $update ["gender"] = $gender == "f" ? "f" : "m";
            $update ["admin_email"] = (bool) $admin_email;
            $update ["user_email"] = (bool) $user_email;
            $update ["dst"] = (bool) $use_dst;
            $update ["timezone"] = (int) $timezone;
            $cols = array("name_surname" => 'name');
            $settings = rex($data, $cols);
            $settings["show_age"] = (bool) $data ['show_age'];
            $settings = array_merge($settings, $uf->save($data));

            try {
                plugins::o()->pass_data(array('update' => &$update,
                    'settings' => &$settings), true)->run_hook('register_user');

                $update['settings'] = users::o()->make_settings($settings);
                $id = db::o()->insert($update, "users");

                plugins::o()->pass_data(array('id' => $id))->run_hook('register_user_finish');
            } catch (PReturn $e) {
                return $e->r();
            }
            if ($invite)
                db::o()->p($invite)->update(array(
                    "to_userid" => $id), "invites", 'WHERE invite_id=? LIMIT 1');
            elseif (!config::o()->v('confirm_email') && !config::o()->v('confirm_admin'))
                users::o()->write_cookies($username, $password);
            ok();
        }
        tpl::o()->display("register/main_step.tpl");
    }

}

?>