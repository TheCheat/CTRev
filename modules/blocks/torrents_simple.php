<?php

/**
 * Project:             CTRev
 * File:                torrents_simple.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		"Лёгкий" блок торрентов
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class torrents_simple_block {

    /**
     * Настройки блока
     * @var array
     */
    public $settings = array(
        "cats[string]" => "string",
        "check_children" => 'enum[1;0]',
        'limit' => 'integer',
        'max_title_symb' => 'integer');

    /**
     * Языковой файл для настроек
     * @var string
     */
    public $settings_lang = "torrents";

    /**
     * Инициализация блока-торрентов
     * @return null
     */
    public function init() {
        lang::o()->get("blocks/torrents");
        if (!users::o()->perm('torrents'))
            return;
        $curcats = $this->settings['cats'];
        if (!$curcats)
            return;
        if ($this->settings['check_children']) {
            /* @var $cats categories */
            $cats = n("categories");
            foreach ($curcats as $name => $cat) {
                $cat = explode('|', $cat);
                $c = count($cat);
                $ids = array();
                for ($i = 0; $i < $c; $i++) {
                    $ic = longval($cat[$i]);
                    if (!$ic || !$cats->get($ic))
                        continue;
                    $ids[] = $ic;
                    $cats->get_children_ids($ic, $ids);
                }
                if (!$ids) {
                    unset($curcats[$name]);
                    continue;
                }
                $ids = array_unique($ids);
                $curcats[$name] = implode('|', $ids);
            }
        }
        print("Torrents block inited");
        tpl::o()->assign('curcats', array_reverse($curcats));
    }

}