<?php

/**
 * Project:             CTRev
 * File:                chat.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок чата
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class chat_block {

    /**
     * Инициализация чата
     * @return null
     */
    public function init() {
        lang::o()->get("blocks/chat");
        tpl::o()->display('chat/index.tpl');
    }

}

?>