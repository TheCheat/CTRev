<?php

/**
 * Project:         	CTRev
 * @file                modules/blocks/html.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Простой блок для вывода HTML и BBCode
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class html_block {

    /**
     * Настройки блока
     * @var array $settings
     */
    public $settings = array(
        "content" => "text",
        "type" => "enum[html;bbcode]");

    /**
     * Настройки по-умолчанию
     * @var array $defaults
     */
    public $defaults = array("type" => 'bbcode');

    /**
     * Функция для инициализации блока
     * @return null
     */
    public function init() {
        $content = $this->settings['content'];
        if ($this->settings['type'] == 'bbcode')
            $content = bbcodes::o()->format_text($content);
        else
            $content = display::o()->html_decode($content);
        print($content);
    }

}

?>