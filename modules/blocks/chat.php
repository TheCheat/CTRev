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
     * @global lang $lang
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $tpl;
        $lang->get("blocks/chat");
        $tpl->display('chat/index.tpl');
    }

}

?>