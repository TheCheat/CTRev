<?php

/**
 * Project:             CTRev
 * File:                downm.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Нижник блок
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class downm_block {

    /**
     * Инициализация нижнего блока
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get("blocks/downm");
        print("Down block inited");
    }

}

?>