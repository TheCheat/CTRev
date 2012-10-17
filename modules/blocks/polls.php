<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/polls.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок опросов
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class polls_block {

    /**
     * Инициализация блока опросов
     * @return null
     */
    public function init() {
        /* @var $polls polls */
        $polls = n("polls");
        $polls->display(0, 0, false, true);
    }

}

?>