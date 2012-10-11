<?php

/**
 * Project:            	CTRev
 * File:                class.captcha.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Captcha
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class captcha implements captcha_interface {
    /**
     * Время очистки капч(в сек.)
     */

    const time = 1800;

    /**
     * Массив возможных бэкграундов
     * @var array $bckgrnds
     */
    private $bckgrnds = array(
        "captcha1.jpg");

    /**
     * Функция вызова captcha
     * @return null
     */
    public function init() {
        $uid = users::o()->v('id');
        $insert = array();
        $created = time();
        $insert ["created"] = $created;
        if ($uid) {
            $insert ["user_id"] = longval($uid);
            $where = 'user_id = ' . longval($uid);
        } else {
            $sid = session_id();
            $insert ["session_id"] = $sid;
            $where = 'session_id = ' . db::o()->esc($sid);
        }
        $background = 'include/backgrounds/' . $this->bckgrnds [rand(0, count($this->bckgrnds) - 1)];
        $code = mb_strtoupper(users::o()->generate_salt(6));
        $insert ["key"] = $code;
        db::o()->delete("captcha", ("WHERE " . $where));
        db::o()->insert($insert, "captcha");
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $uploader->watermark($background, $code, 'auto', false, '', 'cc', true, false);
    }    

    /**
     * Функция проверки кода captcha
     * @param array $error массив ошибок
     * @param string $var $_POST переменная для проверки введённого кода
     * @return null
     */
    public function check(&$error, $var = "captcha_code") {
        $uid = users::o()->v('id');
        $posted_code = $_POST [$var];
        if (!$posted_code) {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
        if ($uid) {
            $where = 'user_id = ' . longval($uid);
        } else {
            $sid = session_id();
            $where = 'session_id = ' . db::o()->esc($sid);
        }
        $code = db::o()->fetch_assoc(db::o()->query("SELECT `key` FROM captcha WHERE " . $where . " LIMIT 1"));
        if (!$code) {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
        $code = $code["key"];
        if ($code == mb_strtoupper($posted_code)) {
            return true;
        } else {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
    }

    /**
     * Функция очистки старых капч
     * @param string $only удаление только(old - старые, user пользователя)
     * @return null
     */
    public function clear($only = "") {
        $where = "";
        $uid = users::o()->v('id');
        if (!$only || $only == "user")
            if ($uid) {
                $where = 'user_id = ' . longval($uid);
            } else {
                $sid = session_id();
                $where = 'session_id = ' . db::o()->esc($sid);
            }
        if (!$only || $only == "old")
            if (longval(self::time))
                $where .= ( $where ? " OR " : "") . 'created < (' . time() . ' - ' . self::time . ')';
        if (!$where)
            return;
        db::o()->delete("captcha", ("WHERE " . $where));
    }

}

?>