<?php

/**
 * Project:             CTRev
 * @file                modules/blocks/torrents.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Блок торрентов
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class torrents_block {

    /**
     * Настройки блока
     * @var array $settings
     */
    public $settings = array(
        "cats[string]" => "string",
        "check_children" => 'enum[1;0]',
        'limit' => 'integer',
        'max_title_symb' => 'integer');

    /**
     * Языковой файл для настроек
     * @var string $settings_lang
     */
    public $settings_lang = "torrents";

    /**
     * Получение ID подкатегорий, если необходимо
     * @param array $curcats массив выбранных категорий
     * @return null
     */
    protected function get_children(&$curcats) {
        if (!$this->settings['check_children'])
            return;
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

    /**
     * Инициализация блока-торрентов
     * @return null
     */
    public function init() {
        if (!config::o()->v('torrents_on'))
            return;
        lang::o()->get("blocks/content");
        if (!users::o()->perm('content'))
            return;
        $curcats = $this->settings['cats'];
        if (!$curcats)
            return;
        print("Torrents block inited");
        $this->get_children($curcats);
        tpl::o()->assign('curcats', array_reverse($curcats));
    }

}