<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.tpl.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс, добавляющий некоторые фичи к Smarty
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

final class tpl extends Smarty {

    /**
     * Массив показанных шаблонов
     * @var array $displayed
     */
    private $displayed = array();

    /**
     * Конфиг. инициализирована?
     * @var bool $conf_inited
     */
    private $conf_inited = false;

    /**
     * Временный(старый) путь
     * @var string $tmp
     */
    private $tmp = '';

    /**
     * Тема
     * @var string $theme 
     */
    private $theme = '';

    /**
     * Инициализация конфига
     * @return string родительский шаблон
     */
    public function init_cfg($name = null, $var = 'style_parent') {
        $f = 'main.conf';
        $var = $var ? $var : null;
        if ($name) {
            $tpldir = ROOT . THEMES_PATH . '/' . $name;
            if (!class_exists('Config_File', false))
                include_once SMARTY_DIR . 'Config_File.class.php';
            $cfg = new Config_File($tpldir);
            $cfg->load_file($f);
            $vars = $cfg->get($f, null, $var);
            unset($cfg); // Destruct f*cking class
            return $vars;
        }
        if (!$this->conf_inited) {
            $this->config_load($f);
            $this->conf_inited = true;
        }
        return $this->get_config_vars($var);
    }

    /**
     * В онке?
     * @param string $resource_name имя шаблона
     * @return null
     */
    private function is_window(&$resource_name) {
        if (!$_GET['window'])
            return;
        if ($resource_name == 'overall_header.tpl')
            $resource_name = 'wind_header.tpl';
        elseif ($resource_name == 'overall_footer.tpl')
            $resource_name = 'wind_footer.tpl';
    }

    /**
     * Получение родительского стиля
     * @param string $resource_name имя шаблона
     * @return null
     */
    private function get_parent($resource_name) {
        $parent = $this->init_cfg();
        while (!$this->template_exists($resource_name)) {
            $parent = $this->init_cfg($parent);
            $this->set_theme($parent, false);
            $this->template_dir = $this->get_path();
        }
    }

    /**
     * Проверка на изменение template_dir
     * @return null
     */
    private function check_tmp() {
        if ($this->tmp) {
            if ($this->template_dir != $this->tmp)
                $this->template_dir = $this->tmp;
            $this->tmp = '';
        }
    }

    /**
     * Переопределение функции fetch для собтсвенных нужд
     * @param string $resource_name имя шаблона
     * @param int $cache_id ID кеша
     * @param int $compile_id ID скомпилированного шаблона
     * @param bool $display отображать?
     * @return mixed если возвращает
     */
    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
        $this->prefetch($resource_name);
        $r = parent::fetch($resource_name, $cache_id, $compile_id, $display);
        $this->postfetch($resource_name);
        return $r;
    }

    /**
     * Переопределение функции _smarty_include для собтсвенных нужд
     * @param array $params массив параметров
     * @return mixed если возвращает
     */
    public function _smarty_include($params) {
        $this->prefetch($params['smarty_include_tpl_file']);
        $r = parent::_smarty_include($params);
        $this->postfetch($params['smarty_include_tpl_file']);
        return $r;
    }

    /**
     * Действия перед выполнением шаблона
     * @param string $resource_name имя шаблона
     * @return null
     */
    private function prefetch(&$resource_name) {
        $this->check_tmp();
        $this->is_window($resource_name);
        $this->tmp = $this->template_dir;
        $this->get_parent($resource_name);
    }

    /**
     * Действия после выполнения шаблона
     * @param string $resource_name имя шаблона
     * @return null
     */
    private function postfetch($resource_name) {
        $this->check_tmp();
        if (!in_array($resource_name, $this->displayed))
            $this->displayed [] = $resource_name;
    }

    /**
     * Показанн ли шаблон?
     * @param string $resource_name имя шаблона
     * @return bool так показан или нет?
     */
    public function displayed($resource_name) {
        return (in_array($resource_name, $this->displayed));
    }

    /**
     * Установка темы
     * @param string $theme имя темы
     * @param bool $reset установить настройки Smarty?
     * @return null
     */
    public function set_theme($theme, $reset = true) {
        $this->theme = $theme;
        if ($reset) {
            $this->template_dir = $this->get_path();
            $this->compile_dir = $this->get_path(1);
            $this->config_dir = $this->get_path(2);
        }
    }

    /**
     * Получение пути
     * @param int $type тип пути(0-шаблоны, 1-компиляция, 2-конфиг)
     * @return string путь
     */
    private function get_path($type = 0) {
        switch ($type) {
            case 1:
                $f = ROOT . 'include/cache/' . TEMPLATES_PATH . '/' . $this->theme . '/';
                if (!file_exists($f))
                    @mkdir($f);
                return $f;
            case 2:
                return ROOT . THEMES_PATH . '/' . $this->theme . '/';
            default:
                return ROOT . THEMES_PATH . '/' . $this->theme . '/' . TEMPLATES_PATH . '/';
        }
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var tpl $o
     */
    private static $o = null;

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
     * @return tpl $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }
    
}

?>