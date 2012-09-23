<?php

/**
 * Project:             CTRev
 * File:                login.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @var string
     */
    public $title = "";

    /**
     * Инициализация логина
     * @global tpl $tpl
     * @global users $users
     * @global furl $furl
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $tpl, $users, $furl, $lang;
        $ref = $_GET ['ref'];
        $act = $_GET ['act'];
        $tpl->assign('referer', $ref);
        if ($act != "out") {
            if ($users->v() && ((!$ref && !$act) || $act) && $users->v('confirmed') == 3)
                $furl->location('');
        }
        $lang->get('login');
        switch ($act) {
            case "out":
                $users->clear_cookies();
                $furl->location('');
            case "recover" :
                $this->title = $lang->v('recovery_page');
                if ($key = $_GET['key']) {
                    $email = $_GET ["email"];
                    $this->recover_save($key, $email);
                } else
                    $tpl->display('login/recover.tpl');
                break;
            default :
                $this->title = $lang->v('login_page');
                $tpl->display('login/login.tpl');
                break;
        }
    }

    /**
     * Форма смены пароля
     * @global tpl $tpl
     * @param string $key ключ подтверждения
     * @param string $email E-mail подтверждения
     * @return null
     */
    protected function recover_save($key, $email) {
        global $tpl;
        $tpl->assign("key", $key);
        $tpl->assign("email", $email);
        $tpl->display('login/recover_save.tpl');
    }

}

class login_ajax {

    /**
     * Инициализация логина, в качестве Ajax-модуля
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $act = $_GET ["act"];
        $lang->get('login');
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
        die("OK!");
    }

    /**
     * Непосредственно, логин пользователя
     * @global users $users
     * @global lang $lang
     * @global config $config
     * @global db $db
     * @param string $login логин пользователя
     * @param string $password пароль пользователя
     * @param bool $short_session короткая сессия?
     * @return null
     * @throws Excpetion
     */
    protected function login($login, $password, $short_session = false) {
        global $users, $lang, $config, $db;
        $max_enter = (int) $config->v('max_trylogin') - 1;
        $interval = (int) $config->v('logintime_interval');
        //if ($users->v())
        //	return;
        $sid = session_id();
        if ($max_enter >= 0) {
            $res = $db->query('SELECT trying_time, login_trying FROM sessions WHERE sid=' . $db->esc($sid) . ' OR ip=' . $users->get_ip() . '
                ORDER BY login_trying DESC LIMIT 1');
            $res = $db->fetch_assoc($res);
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
        $short_session = (bool) $short_session;
        $passhash = $users->check_data($login, $password, $error, $id);
        if ($error) {
            if ($max_enter >= 0) {
                $error .= ("&nbsp; " . sprintf($lang->v('login_trying_of'), $login_trying + 1, $max_enter + 1));
                $db->update(array(
                    "login_trying" => ++$login_trying,
                    "trying_time" => time()), "sessions", 'WHERE sid=' . $db->esc($sid) . ' OR ip=' . $users->get_ip() . ' ORDER BY login_trying DESC LIMIT 1');
                $users->setcookie("login_trying", $login_trying);
                $users->setcookie("login_time", time());
            }
            throw new EngineException($error);
        } else {
            if ($max_enter >= 0) {
                $db->update(array(
                    "login_trying" => 0,
                    "trying_time" => 0), "sessions", 'WHERE sid=' . $db->esc($sid) . ' OR ip=' . $users->get_ip() . ' ORDER BY login_trying DESC LIMIT 1');
                $users->setcookie("login_trying", 0);
                $users->setcookie("login_time", 0);
            }
        }
        $users->write_cookies($login, $passhash, $short_session);
    }

    /**
     * Функция восстановления пароля
     * @global users $users
     * @global db $db
     * @global furl $furl
     * @global etc $etc
     * @param string $login логин пользователя
     * @param string $email E-mail пользователя
     * @return null
     */
    protected function recover($login, $email) {
        global $users, $db, $furl, $etc;
        if ($users->v())
            return;
        $where = 'username_lower=' . $db->esc(mb_strtolower($login)) . ' AND email=' . $db->esc($email);
        $count = $db->count_rows("users", $where);
        if (!$count)
            throw new EngineException("recover_user_not_exists");
        $key = $users->generate_salt();
        $ok = $db->update(array(
            "confirm_key" => $key,
            "new_email" => ""), "users", 'WHERE ' . $where);
        $link = $furl->construct('login', array(
            "act" => "recover",
            "key" => $key,
            "email" => $email));
        $etc->send_mail($email, "recover_pass", array(
            "link" => $link));
    }

    /**
     * Сохранение нового пароля
     * @global db $db
     * @global users $users\
     * @param string $key ключ подтверждения
     * @param string $email E-mail пользователя
     * @param string $password новый пароль
     * @param string $passagain новый пароль(повтор)
     * @return null
     */
    protected function recover_save($key, $email, $password, $passagain) {
        global $db, $users;
        if ($users->v())
            return;
        if ($password != $passagain || !$users->check_password($password))
            throw new EngineException("recover_pass_not_equals");
        $salt = $users->generate_salt();
        $password = $users->generate_pwd_hash($password, $salt);
        $count = $db->update(array(
            "confirm_key" => "",
            "new_email" => "",
            "password" => $password,
            "salt" => $salt), "users", ('WHERE confirm_key=' . $db->esc($key) . ' AND email=' . $db->esc($email)));
        if (!$count)
            throw new EngineException("recover_user_not_exists");
    }

}

?>