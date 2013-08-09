<?php

if (!defined('INSITE'))
    die('Remote access denied!');

include 'recaptchalib.php';

class recaptcha implements captcha_interface {

    /**
     * Функция вызова recaptcha
     * @return string HTML код
     */
    public function init() {
        return self::show();
    }

    /**
     * Отображение recaptcha
     * @return string HTML код
     */
    public static function show() {
        return recaptcha_get_html(config::o()->v('recaptcha_public_key'));
    }

    /**
     * Функция проверки кода recaptcha
     * @param array $error массив ошибок
     * @param string $var $_POST переменная для проверки введённого кода
     * @return null
     */
    public function check(&$error, $var = "recaptcha_challenge_field") {
        $posted_code = $_POST[$var];
        if (!$posted_code) {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
        $r = recaptcha_check_answer(config::o()->v('recaptcha_private_key'), $_SERVER["REMOTE_ADDR"], $posted_code, $_POST["recaptcha_response_field"]);
        if (!$r || !is_object($r) || !$r->is_valid) {
            $error [] = lang::o()->v('captcha_false_captcha');
            return;
        }
        return true;
    }

}

?>