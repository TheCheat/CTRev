<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/content.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок контента
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class content_block {

    /**
     * Инициализация блока контента
     * @return null
     */
    public function init() {
        lang::o()->get("content");
        if (!users::o()->perm('content'))
            return;
        /* @var $content content */
        $content = plugins::o()->get_module("content");
        if (!is_callable(array($content, "show")))
            return;
        tpl::o()->assign('content_in_block', true);
        $content->show();
    }

}