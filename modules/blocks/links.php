<?php

/**
 * Project:             CTRev
 * File:                links.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок ссылки
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class links_block {

    /**
     * Настройки блока
     * @var array
     */
    public $settings = array(
        "links[string]" => "string");

    /**
     * Инициализация блока ссылок
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $tpl;
        $tpl->assign("links", $this->settings ["links"]);
        $tpl->display("blocks/contents/links.tpl");
    }

}

?>