<?php

/**
 * Project:             CTRev
 * @file                modules/login.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Логин на сайт
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class login {

    /**
     * Заголовок модуля
     * @var string $title
     */
    public $title = "";

    /**
     * Инициализация логина
     * @return null
     */
    public function init() {
        $ref = $_GET ['ref'];
        $act = $_GET ['act'];
        tpl::o()->assign('referer', $ref);
        if ($act != "out") {
            if (users::o()->v() && ((!$ref && !$act) || $act) && users::o()->v('confirmed') == 3)
                furl::o()->location('');
        }
        lang::o()->get('login');
        switch ($act) {
            case "out":
                users::o()->clear_cookies();
                furl::o()->location('');
            case "recover" :
                $this->title = lang::o()->v('recovery_page');
                if ($key = $_GET['key']) {
                    $email = $_GET ["email"];
                    $this->recover_save($key, $email);
                }
                else
                    tpl::o()->display('login/recover.tpl');
                break;
            default :
                $this->title = lang::o()->v('login_page');
                tpl::o()->display('login/login.tpl');
                break;
        }
    }

    /**
     * Форма смены пароля
     * @param string $key ключ подтверждения
     * @param string $email E-mail подтверждения
     * @return null
     */
    protected function recover_save($key, $email) {
        tpl::o()->assign("key", $key);
        tpl::o()->assign("email", $email);
        tpl::o()->display('login/recover_save.tpl');
    }

}

class login_ajax {

    /**
     * Инициализация логина, в качестве Ajax-модуля
     * @return null
     */
    public function init() {
        $act = $_GET ["act"];
        lang::o()->get('login');
        switch ($act) {
            case "recover_save" :
                $key = $_POST ["key"];
                $email = $_POST ["email"];
                $password = $_POST ['password'];
                $passagain = $_POST ['passagain'];
                $this->recover_save($key, $email, $password, $passagain);
                break;
            case "recover" :
                $login = $_POST ["login"];
                $email = $_POST ["email"];
                $this->recover($login, $email);
                break;
            default :
                $login = $_POST ['login'];
                $password = $_POST ['password'];
                $short_sess = $_POST ['short_sess'];
                $this->login($login, $password, $short_sess);
                break;
        }
        ok();
    }

    /**
     * Проверка попыток входа
     * @return int кол-во попыток входа
     * @throws EngineException
     */
    protected function login_tries() {
        $max_enter = (int) config::o()->v('max_trylogin') - 1;
        $interval = (int) config::o()->v('logintime_interval');
        $sid = session_id();
        $login_trying = 0;
        if ($max_enter >= 0) {
            $res = db::o()->p($sid, users::o()->get_ip())->query('SELECT trying_time, login_trying 
                FROM sessions WHERE sid=? OR ip=?
                ORDER BY login_trying DESC LIMIT 1');
            $res = db::o()->fetch_assoc($res);
            $login_trying = $res ["login_trying"];
            $time = $res["trying_time"];
            if ($login_trying < longval($_COOKIE ["login_trying"]))
                $login_trying = (int) $_COOKIE ["login_trying"];
            if ($time < longval($_COOKIE ["login_time"]))
                $time = (int) $_COOKIE ["login_time"];
            if ($login_trying >= $max_enter && $time + $interval > time())
                throw new EngineException("login_more_than_maybe", array(
            $login_trying + 1,
            $max_enter + 1));
        }
        return $login_trying;
    }

    /**
     * Запись кол-ва попыток входа
     * @param string $error ошибка входа
     * @param int $login_trying кол-во попыток входа
     * @throws EngineException
     */
    protected function login_tries_record($error, $login_trying) {
        $max_enter = (int) config::o()->v('max_trylogin') - 1;
        $sid = session_id();
        if ($error) {
            if ($max_enter >= 0) {
                $error .= ("&nbsp; " . sprintf(lang::o()->v('login_trying_of'), $login_trying + 1, $max_enter + 1));
                db::o()->p($sid, users::o()->get_ip())->update(array(
                    "login_trying" => ++$login_trying,
                    "trying_time" => time()), "sessions", 'WHERE sid=? OR ip=? ORDER BY login_trying DESC LIMIT 1');
                users::o()->setcookie("login_trying", $login_trying);
                users::o()->setcookie("login_time", time());
            }
            throw new EngineException($error);
        } else {
            if ($max_enter >= 0) {
                db::o()->p($sid, users::o()->get_ip())->update(array(
                    "login_trying" => 0,
                    "trying_time" => 0), "sessions", 'WHERE sid=? OR ip=? ORDER BY login_trying DESC LIMIT 1');
                users::o()->setcookie("login_trying", 0);
                users::o()->setcookie("login_time", 0);
            }
        }
    }

    /**
     * Непосредственно, логин пользователя
     * @param string $login логин пользователя
     * @param string $password пароль пользователя
     * @param bool $short_session короткая сессия?
     * @return null
     * @throws Excpetion
     */
    protected function login($login, $password, $short_session = false) {
        //if (users::o()->v())
        //	return;
        $login_trying = $this->login_tries();
        $short_session = (bool) $short_session;
        $passhash = users::o()->check_data($login, $password, $error, $id);
        $this->login_tries_record($error, $login_trying);
        users::o()->write_cookies($login, $passhash, $short_session);
    }

    /**
     * Функция восстановления пароля
     * @param string $login логин пользователя
     * @param string $email E-mail пользователя
     * @return null
     */
    protected function recover($login, $email) {
        if (users::o()->v())
            return;
        $params = array(mb_strtolower($login), $email);
        $where = 'username_lower=? AND email=?';
        $count = db::o()->p($params)->count_rows("users", $where);
        if (!$count)
            throw new EngineException("recover_user_not_exists");
        $key = users::o()->generate_salt();
        $ok = db::o()->p($params)->update(array(
            "confirm_key" => $key,
            "new_email" => ""), "users", 'WHERE ' . $where);
        $link = furl::o()->construct('login', array(
            "act" => "recover",
            "key" => $key,
            "email" => $email));
        /* @var $etc etc */
        $etc = n("etc");
        $etc->send_mail($email, "recover_pass", array(
            "link" => $link));
    }

    /**
     * Сохранение нового пароля
     * @param string $key ключ подтверждения
     * @param string $email E-mail пользователя
     * @param string $password новый пароль
     * @param string $passagain новый пароль(повтор)
     * @return null
     */
    protected function recover_save($key, $email, $password, $passagain) {
        if (users::o()->v())
            return;
        if ($password != $passagain || !users::o()->check_password($password))
            throw new EngineException("recover_pass_not_equals");
        $salt = users::o()->generate_salt();
        $password = users::o()->generate_pwd_hash($password, $salt);
        $update = array(
            "confirm_key" => "",
            "new_email" => "",
            "password" => $password,
            "salt" => $salt);
        plugins::o()->pass_data(array("update" => &$update), true)->run_hook('login_recover_save');
        $count = db::o()->p($key, $email)->update($update, "users", 'WHERE confirm_key=? AND email=?');
        if (!$count)
            throw new EngineException("recover_user_not_exists");
    }

}

?>