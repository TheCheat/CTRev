<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/links.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var array $settings
     */
    public $settings = array(
        "links[string]" => "string");

    /**
     * Инициализация блока ссылок
     * @return null
     */
    public function init() {
        tpl::o()->assign("links", $this->settings ["links"]);
        tpl::o()->display("blocks/contents/links.tpl");
    }

}

?>