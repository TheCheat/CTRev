<?php

/**
 * Project:            	CTRev
 * File:                allowed.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Разрешённые классы, модули, переменные
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class allowed {

    /**
     * Разрешённые модули 
     * @var array $allowed
     */
    private $allowed = array(
        "acp_modules" => array("bans",
            "warnings",
            "config",
            "groups",
            "users",
            "cats",
            "patterns",
            "blocks",
            "lang",
            "styles",
            "bots",
            "logs",
            "spages",
            "smilies",
            "plugins"),
        "acp_pages" => array("main"),
        "modules" => array("ajax_index",
            "login",
            "registration",
            "messages",
            "comments_manage",
            "rating_manage",
            "usercp",
            "user",
            "search_module",
            "polls_manage",
            "downm",
            "chat",
            "bans",
            "torrents",
            "news",
            "statics"));

    /**
     * Разрешённые классы
     * @var array $classes
     */
    private $classes = array(
        "cache",
        "db",
        "tpl",
        "lang",
        "config",
        "file",
        "modsettings",
        "plugins",
        "users",
        "input",
        "bbcodes",
        "display",
        "fbenc",
        "bittorrent",
        "blocks",
        "captcha",
        "cleanup",
        "comments",
        "categories",
        "image",
        "uploader",
        "furl",
        "polls",
        "getpeers",
        "rating",
        "search",
        "mailer",
        "stats",
        "etc",
        "smtp");
    /**
     * Добавление модуля/класса/переменных в список разрешённых
     * @param string $what что добавляем
     * @param string $type тип
     * @return bool|allowed false, если не добавили, иначе - $this
     */
    public function add($what, $type = "modules") {
        switch ($type) {
            case 'modules':
            case 'acp_modules':
            case 'acp_pages':
                $v = &$this->allowed[$type];
                break;
            case 'classes':
                $v = &$this->$type;
                break;
            default:
                return false;
        }
        if (!validword($what))
            return false;
        $v[] = $what;
        return $this;
    }

    /**
     * Проверка на наличие модуля в списках разрешённых
     * @param string $what что ищем
     * @param string $type тип(modules|acp_modules|acp_pages|classes)
     * @return bool true, если присутствует
     */
    public function is($what, $type = "modules") {
        $v = $this->get($type);
        return in_array($what, $v);
    }

    /**
     * Получение полного списка разрешённых модулей/классов/переменных
     * @param string $type тип(modules|acp_modules|acp_pages|classes)
     * @return array список
     */
    public function get($type = "modules") {
        switch ($type) {
            case 'modules':
            case 'acp_modules':
            case 'acp_pages':
                return $this->allowed[$type];
                break;
            case 'classes':
                return $this->$type;
                break;
            default:
                return false;
        }
    }
    
    // Реализация Singleton
    
    /**
     * Объект данного класса
     * @var allowed $o
     */
    protected static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        
    }

    /**
     * Не клонируем
     * @return null 
     */
    private function __clone() {
        
    }

    /**
     * И не десериализуем
     * @return null 
     */
    private function __wakeup() {
        
    }

    /**
     * Получение объекта класса
     * @return allowed $this
     */
    public static function o() {        
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>