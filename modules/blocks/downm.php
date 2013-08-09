<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/downm.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @return null
     */
    public function init() {
        if (!config::o()->mstate('downm'))
            return;
        lang::o()->get("blocks/downm");
        print("Down block inited");
    }

}

?>