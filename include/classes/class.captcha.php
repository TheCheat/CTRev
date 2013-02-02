<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.captcha.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Captcha
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class captcha implements captcha_interface {

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
        $background = 'include/backgrounds/' . $this->bckgrnds [rand(0, count($this->bckgrnds) - 1)];
        $code = mb_strtoupper(users::o()->generate_salt(6));
        $_SESSION['captcha_key'] = $code;
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
        $posted_code = $_POST [$var];
        if (!$posted_code) {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
        $code = $_SESSION['captcha_key'];
        if ($code == mb_strtoupper($posted_code)) {
            return true;
        } else {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
    }

}

?>