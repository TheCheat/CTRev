<?php

/**
 * Project:            	CTRev
 * File:                interface.captcha.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Интерфейс для Captcha
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

interface captcha_interface {

    public function init();

    public function check(&$error, $var = "captcha_code");

    public function clear($only = "");
}

?>