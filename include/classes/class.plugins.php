<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.plugins.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Класс, реализующий поддержку плагинов в движке
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

// Для прекращения основной функции
final class PReturn extends Exception {

    /**
     * Возвращаемое значение
     * @var mixed $return
     */
    private $return = null;

    /**
     * Конструктор исключения
     * @param mixed $return возвращаемое значение
     * @return null
     */
    public function __construct($return = null) {
        $this->return = $return;
    }

    /**
     * Получение значения, которая должна вернуть функция
     * @return mixed значение
     */
    public function r() {
        return $this->return;
    }

}

// Ограничиваем доступ к защищённым переменным, добавляем методы

/**
 * Чтение
 */
define('PVAR_READ', 0);
/**
 * Добавление ключа
 */
define('PVAR_ADD', 1);
/**
 * Изменение переменной
 */
define('PVAR_MOD', 2);
/**
 * Удаление ключа
 */
define('PVAR_DELETE', 4);

abstract class pluginable_object {

    /**
     * Доп. методы
     * @var array $_methods
     */
    private $_methods = array();

    /**
     * Защищённые переменные
     * @var array $_pvars
     */
    private $_pvars = array();

    /**
     * Оригинальная функция
     * @var callback $_original
     */
    private $_original = null;

    /**
     * Массив допустимых действий в конструкторе
     * @var array $_actions
     */
    private $_actions = array(
        "add_method",
        "modify_var",
        "remove_key");

    /**
     * Конструктор
     * @return null 
     */
    final public function __construct() {
        $this->plugin_construct();
        plugins::o()->get_preloaded($this, $this->_actions);
    }

    /**
     * Конструктор для классов, наследующих данный
     * @return null 
     */
    abstract protected function plugin_construct();

    /**
     * Добавление метода
     * @param string $method имя метода
     * @param callback $callback функция для вызова
     * @return pluginable_object $this
     * @note список параметров смотреть по call_method, 
     * последним параметром будет ориг. функция, если доступна
     */
    final public function add_method($method, $callback) {
        if (is_callable($callback))
            $this->_methods[$method] = $callback;
        return $this;
    }

    /**
     * Доступен ли для вызова метод
     * @param string $method имя метода
     * @return bool true, если доступен
     */
    final protected function is_callable($method) {
        return isset($this->_methods[$method]) || is_callable(array($this, $method));
    }

    /**
     * Вызов оригинальной функции, даже если она объявлена protected
     * @note Про func_get_args() знаю, но он не воспринимает аргументы по ссылке
     * @param array $params массив параметров
     * @return mixed возвращаемое значение функции
     */
    final public function call_original($params = null) {
        if (!$this->_original)
            return;
        return call_user_func_array($this->_original, $params);
    }

    /**
     * Вызов метода
     * @note Последний параметр для переопределяемого метода всегда callback
     * оригинальной функции
     * @param string $method имя метода
     * @param array $params массив параметров
     * @param bool $redefine разрешить переопределять стандартные методы
     * @return mixed возвращаемое значение функции
     */
    final protected function call_method($method, $params = null, $redefine = true) {
        $params = (array) $params;
        $cb = array($this, $method);
        $c = is_callable($cb);
        if ((!$c || $redefine) && isset($this->_methods[$method])) {
            if ($c) {
                $params[] = array($this, "call_original"); // возможность вызвать ориг. метод
                $this->_original = $cb;
            }
            $return = call_user_func_array($this->_methods[$method], $params);
            $this->_original = null;
            return $return;
        } elseif ($c)
            return call_user_func_array($cb, $params);
    }

    // ограничиваем доступ к переменным извне
    // зачем? да потому что так надо.

    /**
     * Получение значения переменной
     * @param string $var ипя переменной
     * @param mixed $key ключ, если требуется
     * @return mixed значение переменной
     */
    final public function get_var($var, $key = null) {
        if (!isset($this->_pvars[$var]))
            return;
        $var = $this->$var;
        if (is_array($var) && $key)
            return $var[$key];
        else
            return $var;
    }

    /**
     * Изменение переменной
     * @param string $var имя переменной
     * @param mixed $value значение для добавления в массив или изменение не-массива
     * если массив, то массивы соединяются
     * @param bool $add добавлять вне зависимости от ключа
     * @return pluginable_object $this
     */
    final public function modify_var($var, $value, $add = false) {
        $pv = $this->_pvars[$var];
        if (!isset($pv))
            return $this;
        $var = &$this->$var;
        $m = is($pv, PVAR_MOD);
        $a = is($pv, PVAR_ADD);
        if (!is_array($var)) {
            if ($m && !is_array($value))
                $var = $value;
            return $this;
        }
        if (!is_array($value)) {
            if ($a)
                $var[] = $value;
        } else {
            if ($add && $a)
                $var = array_merge($value, $var);
            elseif (!$add)
                foreach ($value as $k => $v)
                    if ($var[$k] ? $m : $a)
                        $var[$k] = $v;
        }
        return $this;
    }

    /**
     * Удаление ключа из переменной
     * @param string $var имя переменной
     * @param mixed $key ключ
     * @return pluginable_object $this
     */
    final public function remove_key($var, $key) {
        $pv = $this->_pvars[$var];
        if (!isset($pv))
            return $this;
        if (!is($pv, PVAR_DELETE))
            return $this;
        $var = &$this->$var;
        if (is_array($var))
            unset($var[$key]);
        return $this;
    }

    /**
     * Разрешение доступа к переменной protected
     * @param string $var имя переменной
     * @param int $access доступ(задаётся через константы PVAR_)
     * @return pluginable_object $this
     */
    final protected function access_var($var, $access = PVAR_READ) {
        if (!isset($this->$var))
            return $this;
        $this->_pvars[$var] = $access;
        return $this;
    }

}

// Управление плагинами
final class plugins_manager {

    /**
     * Массив плагинов
     * @var array $plugins
     */
    private $plugins = array();

    /**
     * Объект плагиновой системы
     * @var plugins $p
     */
    private $p = null;

    /**
     * Массив спарсенных настроек
     * @var array $parsed
     */
    private $parsed = array();

    /**
     * Имя загружаемого плагина
     * @var string $cplugin
     */
    private $cplugin = null;

    /**
     * Конструктор
     * @param plugins $plugins объект плагинов
     * @param string $plugin_name имя загружаемого плагина
     * @return null
     */
    public function __construct($plugins, &$plugin_name) {
        $this->cplugin = &$plugin_name;
        $this->p = $plugins;
        $this->plugins = db::o()->cname('plugins')->ckeys('file', 'settings')
                ->query('SELECT file,settings FROM plugins');
        foreach ($this->plugins as $plugin => $settings) {
            $this->load($plugin);
            $this->settings($plugin, $settings);
        }
    }

    /**
     * Загрузка инклуд-файла
     * @param string $plugin имя плагина
     * @param bool $extend файл расширения классов?
     * @return bool статус выполнения
     */
    public function load_incfile($plugin, $extend = false) {
        $prefix = ($extend ? 'ext' : 'inc');
        $path = ROOT . PLUGINS_PATH . '/' . PLUGINS_INC . '/' . $prefix . '.' . $plugin . '.php';
        if (!file_exists($path))
            return false;
        if (!$extend)
            include_once $path;
        else
            include $path;
        return true;
    }

    /**
     * Загрузка плагина
     * @param string $plugin имя плагина
     * @param bool $install из инсталляции? 
     * не инициализует плагин и не подключает инклуд файл
     * @return bool статус выполнения
     */
    private function load($plugin, $install = false) {
        if (isset($this->plugins[$plugin]) && is_object($this->plugins[$plugin]))
            return true;
        $path = ROOT . PLUGINS_PATH . '/plugin.' . $plugin . '.php';
        if (/* !validword($plugin) || */!file_exists($path))
            return false;
        include_once $path;
        $pname = 'plugin_' . $plugin;
        if (!class_exists($pname, false))
            return false;
        $obj = new $pname();
        $this->plugins[$plugin] = $obj;
        $this->cplugin = $plugin;
        if (!$install) {
            $this->load_incfile($plugin);
            if (is_callable(array($obj, "init")))
                $obj->init($this->p);
            //$this->load_incfile($plugin, true);
        }
        $this->cplugin = '';
        return true;
    }

    /**
     * Установка плагина
     * @param string $plugin имя плагина
     * @param bool $re переустановка? 
     * говорит плагину, что в БД уже все изменения произведены
     * @return bool статус выполнения
     */
    public function install($plugin, $re = false) {
        if (!$this->load($plugin, true))
            return false;
        $o = $this->plugins[$plugin];
        if ($re)
            $this->p->revert_replace($plugin);
        if (is_callable(array($o, 'install')))
            $r = $o->install($re);
        $this->p->save_replaced($plugin);
        return $r || is_null($r) ? true : false;
    }

    /**
     * Удаление плагина
     * @param string $plugin имя плагина
     * @return bool статус выполнения
     */
    public function uninstall($plugin) {
        if (!$this->load($plugin, true))
            return false;
        $o = $this->plugins[$plugin];
        $revert = $this->p->revert_replace($plugin);
        if (is_callable(array($o, 'uninstall')))
            $r = $o->uninstall($revert);
        return $r || is_null($r) ? true : false;
    }

    /**
     * Добавление плагина
     * @param string $plugin имя плагина
     * @return bool статус выполнения
     */
    public function add($plugin) {
        if (!validword($plugin))
            return false;
        if (!$this->install($plugin))
            return false;
        db::o()->insert(array('file' => $plugin), 'plugins');
        $this->uncache();
        return true;
    }

    /**
     * Удаление плагина
     * @param string $plugin имя плагина
     * @return bool статус выполнения
     */
    public function delete($plugin) {
        if (!validword($plugin))
            return false;
        if (!$this->uninstall($plugin))
            return false;
        db::o()->p($plugin)->delete("plugins", 'WHERE file=?');
        if (function_exists('clear_aliases'))
            clear_aliases();
        $this->uncache($plugin);
        return true;
    }

    /**
     * Получение значения переменной плагина
     * @param string $plugin имя плагина
     * @param string $var имя переменной
     * @return mixed значение переменной или false, если не удаётся получить
     */
    public function pvar($plugin, $var) {
        if (!$this->plugins[$plugin] && !$this->load($plugin))
            return false;
        $o = $this->plugins[$plugin];
        if (!isset($o->$var))
            return false;
        return $o->$var;
    }

    /**
     * Получение значения настройки плагина
     * @param string $plugin имя плагина
     * @param string $var имя настройки
     * @return mixed значение переменной или false, если не удаётся получить
     */
    public function psetting($plugin, $var) {
        if (!$this->plugins[$plugin])
            return false;
        if (($s = $this->pvar($plugin, "settings")) === false)
            return false;
        return $s->$var;
    }

    /**
     * Получение спарсенных настроек плагинов
     * @param string $plugin имя плагина
     * @return array массив настроек
     */
    public function parsed_settings($plugin) {
        return $this->parsed[$plugin];
    }

    /**
     * Получение объекта плагина
     * @param string $plugin имя плагина
     * @return object объект плагина
     */
    public function object($plugin) {
        return $this->plugins[$plugin];
    }

    /**
     * Получение и парсинг настроек плагина
     * @param string $plugin имя плагина
     * @param string $settings сериализованный массив настроек
     * @return bool статус выполнения
     */
    private function settings($plugin, $settings) {
        $object = $this->plugins[$plugin];
        if (!$object)
            return false;
        $settings = unserialize($settings);
        $this->parsed[$plugin] = modsettings::o()->change_type('plugins')->parse($plugin, $object, $settings);
        return true;
    }

    /**
     * Удаление кеша
     * @param string $plugin имя плагина
     * @return null
     */
    public function uncache($plugin = null) {
        cache::o()->remove('plugins');
        if ($plugin)
            modsettings::o()->change_type('plugins')->uncache($plugin);
    }

}

class plugins_modifier {

    /**
     * Временная перменная для записи, чем заменяем
     * @var string $tmp_with
     */
    private $tmp_with = null;

    /**
     * Список заменённого в шаблонах
     * @var string $replaced
     */
    private $replaced = array();

    /**
     * Список заменённого в шаблоне
     * @var string $treplaced
     */
    private $treplaced = array();

    /**
     * Разрешить/запретить добавлять комментарий для замены
     * @var bool $comment
     */
    private $comment = false;

    /**
     * Вставка в шаблон
     * @var int $insert
     */
    private $insert = 0;

    /**
     * Callback функция для замены в шаблоне
     * @param array $matches массив спарсенных групп
     * @return string заменённая строка
     */
    private function replace_callback($matches) {
        $with = $this->tmp_with;
        $c = count($matches);
        for ($i = 0; $i < $c; $i++)
            $with = str_replace('$' . $i, $matches[$i], $with);
        if (trim($matches[0]) != trim($with))
            $this->treplaced[] = array($matches[0], $with);
        return $with;
    }

    /**
     * Разрешить/запретить добавлять комментарий для успешной обратной замены
     * @param bool $state true, если разрешить
     * @return plugins_modifier $this
     */
    public function allow_comment($state) {
        $this->comment = (bool) $state;
        return $this;
    }

    /**
     * Вставка в шаблон
     * @param string $f файл шаблона(относительно дирректории, с расширением)
     * @param string $what что вставлять?
     * @param bool $begin в начало?
     * @return bool|int 2, если все шаблоны успешно изменены, true, если стандартный шаблон
     * успешно изменён
     */
    public function insert_template($f, $what, $begin = true) {
        $this->insert = $begin ? 1 : 2;
        $this->modify_template($f, $what);
        $this->insert = 0;
    }

    /**
     * Модификация шаблона 
     * @param string $f файл шаблона(относительно дирректории, с расширением)
     * @param string $what что заменять? (рег. выражение без делимиттеров)
     * @param string $with чем заменять? Чтобы добавить, достаточно дописать 
     * $0 в нужное место, ибо работает рег. выражение в любом случае.
     * !Использовать только вставки вида ${номер группы}, для рег. выражений.
     * @param bool $regexp рег. выражение?
     * @param string $folder дирректория шаблонов
     * @return bool|int 2, если все шаблоны успешно изменены, true, если стандартный шаблон
     * успешно изменён
     * @note CSS, JS не нужно модифицировать, проще подцепить свой, 
     * ибо всё переопределяемо.
     * @see join_js()
     * @see join_css()
     */
    public function modify_template($f, $what, $with = "", $regexp = false, $folder = null) {
        if (!$what)
            return false;
        $f = validpath($f);
        $ft = file::o()->get_filetype($f);
        if (!$f || ($ft != 'tpl' && $ft != 'xtpl'))
            return false;
        if (!$regexp && !$this->insert) {
            $what = mpc($what);
            $what = preg_replace('/\s+/s', '\s+', $what); // не учитываем пробелы
            $regexp = true;
        }
        if (!$folder) {
            $b = 0;
            $dir = file::o()->open_folder(THEMES_PATH, true);
            $c = count($dir);
            $cb = true;
            for ($i = 0; $i < $c; $i++) {
                $cur = $dir[$i];
                $r = $this->modify_template($f, $what, $with, $regexp, $cur);
                if ($cur == config::o()->v('default_style'))
                    $cb = $r;
                $b += $r;
            }
            return !$cb ? false : $b == $c + $cb;
        }
        if ($this->insert)
            $what = "<!-- inserted by plugin at time " . time() . ". begin-->\n" .
                    $what .
                    "\n<!-- inserted by plugin. end-->"; // для обратной замены
        elseif (!$this->comment)
            $with = "<!-- " . crc32($what) . " replaced by plugin at time " . time() . ". begin-->\n" .
                    $with .
                    "\n<!-- replaced by plugin. end-->"; // для обратной замены
        $p = THEMES_PATH . '/' . $folder . '/' . TEMPLATES_PATH . '/' . $f;
        if (!file_exists(ROOT . $p))
            return true; // ибо может наследовать
        $ftpl = file_get_contents(ROOT . $p);
        if (!$this->replaced[$p])
            $this->replaced[$p] = array();
        if (!$this->insert) {
            $this->tmp_with = $with;
            $this->treplaced = array();
            $ftpl = preg_replace_callback('/' . $what . '/siu', array($this, "replace_callback"), $ftpl);
            $this->replaced[$p][] = $this->treplaced;
        } else {
            if ($this->insert == 1)
                $ftpl = $what . $ftpl;
            else
                $ftpl = $ftpl . $what;
            $this->replaced[$p][] = array(array("", $what));
        }
        return file::o()->write_file($ftpl, $p);
    }

    /**
     * Подключает CSS в заменяемую часть шаблона 
     * @param string $string замена
     * @param string $file имя файла(в папке css без расширения)
     * @param bool $admin в АЦ?
     * @return string с CSS
     */
    public function join_css($string, $file, $admin = false) {
        return '<link rel="stylesheet" href="[*$' . ($admin ? 'a' : '') . 'theme_path*]css/' . $file . '.css" type="text/css">' . $string;
    }

    /**
     * Подключает JS в заменяемую часть шаблона 
     * @param string $string замена
     * @param string $file имя файла(в папке js без расширения)
     * @param bool|int $theme в тему? 2 - в АЦ
     * @return string с JS
     */
    public function join_js($string, $file, $theme = false) {
        $pre = $theme ? '[*$' . ($theme === 2 ? 'a' : '') . 'theme_path*]' : '';
        return $string . '<script type="text/javascript" src="' . $pre . 'js/' . $file . '.js"></script>';
    }

    /**
     * Сохранение заменённых частей файла
     * @param string $plugin_name имя плагина
     * @return bool статус
     */
    public function save_replaced($plugin_name) {
        if (!validword($plugin_name))
            return false;
        $path = PLUGINS_PATH . '/' . PLUGINS_REPLACED . '/repl.' . $plugin_name . '.back';
        $s = serialize($this->replaced);
        $this->replaced = array();
        return file::o()->write_file($s, $path);
    }

    /**
     * Обратная замена
     * @param string $plugin_name имя плагина
     * @return bool статус
     */
    public function revert_replace($plugin_name) {
        $b = ROOT . PLUGINS_PATH . '/' . PLUGINS_REPLACED . '/repl.' . $plugin_name . '.back';
        if (!file_exists($b))
            return true;
        $replaced = unserialize(file_get_contents($b));
        if (!$replaced)
            return true;
        $r = 0;
        $i = 0;
        foreach ($replaced as $f => $arr) {
            if (!$arr)
                continue;
            if (!file_exists(ROOT . $f))
                continue;
            $contents = file_get_contents(ROOT . $f);
            foreach ((array) $arr as $cur) {
                if (!$cur)
                    continue;
                foreach ((array) $cur as $per) {
                    list($what, $with) = $per;
                    $c = 1; // reference
                    $contents = str_replace($with, $what, $contents, $c);
                }
            }
            $r += file::o()->write_file($contents, $f);
            $i++;
        }
        $r += @unlink($b);
        $i++;
        return $r == $i;
    }

}

final class plugins extends plugins_modifier {

    /**
     * Статус плагиновой системы
     * @var bool $state
     */
    private $state = true;

    /**
     * Для классов, наследующих pluginable_object создаём копию и копируем её
     * @var array $pluginable
     */
    private $pluginable = array();

    /**
     * Алиасы классов инициализированы?
     * @var bool $aliases_inited
     */
    private $aliases_inited = false;

    /**
     * Массивы данных
     * @var array $data
     */
    private $data = array();

    /**
     * Массивы хуков
     * @var array $hooks
     */
    private $hooks = array();

    /**
     * Добавление методов, модифицирование перменных в классе в конструкторе
     * @var array $preloaded
     */
    private $preloaded = array();

    /**
     * Расширённые классы(параметры для class_exists)
     * @var array $extended
     * @see function class_exists
     */
    private $extended = array();

    /**
     * Список плагинов, для которых необходимо подключить файл ext.
     * @var array $inc_redefined
     */
    private $inc_redefined = array();

    /**
     * Массив переопределённых объектов
     * @var array $redefined
     */
    private $redefined = array();

    /**
     * Массив расширенных функций инициализации
     * @var array $init
     */
    private $init = array();

    /**
     * Данное имя плагина
     * @var string $current_plugin
     */
    private $current_plugin = null;

    /**
     * Управлятор плагинами
     * @var plugins_manager $manager
     */
    public $manager = null;

    /**
     * Вызов хука
     * @param string $name имя хука
     * @return null
     * @throws PReturn
     */
    public function run_hook($name) {
        if (!$this->state)
            return;
        if (!isset($this->hooks[$name]))
            return;
        $hook = (array) $this->hooks[$name];
        $c = count($hook);
        for ($i = 0; $i < $c; $i++)
            call_user_func($hook[$i], $this->data);
        return;
    }

    /**
     * Добавление реализации хука
     * @param string $name имя хука
     * @param callback $callback реализация
     * @return plugins $this 
     * @note единственный параметр, который должна получить функция при вызове - массив данных
     */
    public function add_hook($name, $callback) {
        if (!$this->state)
            return $this;
        if (!is_callable($callback))
            return $this;
        if (!$this->hooks[$name])
            $this->hooks[$name] = array();
        $this->hooks[$name][] = $callback;
        return $this;
    }

    /**
     * Передача переменных из функции хукам
     * @param array $data массив переменных
     * @param bool $clear очистить старый массив?
     * @return plugins $this
     * @note если необходимо передать переменную по ссылке, 
     * так и указываем в массиве, например:
     * plugins::o()->pass_data(array('a' => &$linktoa,
     * 'b' => $simplevar))
     */
    public function pass_data($data, $clear = false) {
        if (!$this->state)
            return $this;
        if (!$data)
            return $this;
        if ($clear)
            $this->clear_data();
        $this->data = array_merge($this->data, (array) $data);
        return $this;
    }

    /**
     * Очистка массива данных
     * @return plugins $this
     */
    public function clear_data() {
        if (!$this->state)
            return $this;
        $this->data = array();
        return $this;
    }

    /**
     * Вызов метода в конструкторе для класса, наследующего pluginable_object
     * @param string $class имя класса
     * @param string $action действие(метод из класса pluginable_object)
     * @param array $params массив параметров
     * @return plugins $this
     */
    public function preload($class, $action, $params) {
        if (!$this->state)
            return $this;
        if (!method_exists('pluginable_object', $action))
            return $this;
        if (!$this->preloaded[$class])
            $this->preloaded[$class] = array();
        if (!$this->preloaded[$class][$action])
            $this->preloaded[$class][$action] = array();
        $this->preloaded[$class][$action][] = (array) $params;
        return $this;
    }

    /**
     * Добавление предзагруженных функций
     * @param object $obj объект
     * @param array $actions массив допустимых методов
     * @return null
     */
    public function get_preloaded($obj, $actions) {
        if (!$this->state)
            return;
        if (!$obj || !is_object($obj) || !is_subclass_of($obj, 'pluginable_object'))
            return;
        $c = get_class($obj);
        if ($nc = array_search($c, $this->redefined))
            $c = $nc;
        $pl = (array) $this->preloaded[$c];
        if (!$pl)
            return;
        $c = count($actions);
        for ($i = 0; $i < $c; $i++) {
            $act = $actions[$i];
            $pla = (array) $pl[$act];
            if (!$pla)
                continue;
            $kc = count($pla);
            for ($k = 0; $k < $kc; $k++)
                call_user_func_array(array($obj, $act), $pla[$k]);
        }
    }

    /**
     * Расширение класса путём переопределения старого
     * @param string $original имя базового класса
     * @param string $new имя нового класса
     * @return plugins $this
     * @note данный метод создаёт алиас для наследования базового класса 
     * несколькими плагинами, новый класс должен наследовать класс с именем:
     * <b>plugin_extend_<i>{$plugin_name}</i>_base_<i>{$original}</i></b>
     * где, <i>{$plugin_name}</i> - имя базового класса, а <i>{$original}</i> - имя базового класса
     */
    public function extend_class($original, $new) {
        if (!$this->state || !$this->current_plugin)
            return $this;
        if (/* !class_exists($new, false) */!validword($new)) // подгружается апосле
            return $this;
        $name = "plugin_extend_" . $this->current_plugin . '_base_' . $original;
        $extend = $this->get_class($original, true);
        $this->extended[$original][] = array($extend, $name);
        $this->redefine_class($original, $new);
        return $this;
    }

    /**
     * Переопределение класса
     * @param string $original имя базового класса
     * @param string $new имя нового класса
     * @return plugins $this
     */
    public function redefine_class($original, $new) {
        if (!$this->state || !$this->current_plugin)
            return $this;
        if (/* !class_exists($new, false) */!validword($new)) // подгружается апосле
            return $this;
        $this->redefined[$original] = $new;
        $this->inc_redefined[$original][] = $this->current_plugin;
        return $this;
    }

    /**
     * Подключение переопределённых классов
     * @param string $class имя оригинала
     * @return null
     */
    private function include_redefined($class) {
        $a = (array) $this->extended[$class];
        if ($a) {
            $c = count($a);
            for ($i = 0; $i < $c; $i++)
                if (!class_exists($a[$i][1]))
                    class_alias($a[$i][0], $a[$i][1]);
        }
        $a = (array) $this->inc_redefined[$class];
        if ($a) {
            $c = count($a);
            for ($i = 0; $i < $c; $i++)
                $this->manager->load_incfile($a[$i], true);
        }
        unset($this->extended[$class]); // защищаем от повторного ненужного добавления алиаса
        unset($this->inc_redefined[$class]); // защищаем от повторного ненужного подключения
    }

    /**
     * Функция проверяет, был ли переопределён "стандартный" класс
     * @param string $class имя базового класса
     * @param bool $name только имя?
     * @return object|string необходимый объект
     */
    public function get_class($class, $name = false) {
        $this->include_redefined($class);
        if ($this->state) {
            if (function_exists('load_aliases') && !$this->aliases_inited) {
                load_aliases();
                $this->aliases_inited = true;
            }
            if (class_exists($this->redefined[$class], false))
                $class = $this->redefined[$class];
        }
        if (!$name && $class instanceof pluginable_object) {
            if (!$this->pluginable[$class])
                $this->pluginable[$class] = new $class();
            return clone $this->pluginable[$class];
        }
        return $name ? $class : new $class();
    }

    /**
     * Модифицирование инициализации модуля
     * @param string $class имя класса
     * @param callback $callback функция для вызова
     * @param bool $pre pre_init?
     * @return plugins $this
     */
    public function modify_init($class, $callback, $pre = false) {
        if (!$this->state)
            return $this;
        if (!is_callable($callback))
            return $this;
        $m = ($pre ? "pre_" : '') . 'init';
        if (!$this->init[$class])
            $this->init[$class] = array();
        if (!$this->init[$class][$m])
            $this->init[$class][$m] = array();
        $this->init[$class][$m][] = $callback;
        return $this;
    }

    /**
     * Вызываем метод init и pre_init, если необходимо 
     * @param object $obj модуль
     * @param string $m вызываемый метод
     * @return mixed если init что-то возвращает
     */
    public function call_init(&$obj, $m = "init") {
        if ($m != 'init' && $m != 'pre_init')
            $m = 'init';
        $r = '';
        if ($this->state) {
            try {
                $c = get_class($obj);
                if ($nc = array_search($c, $this->redefined))
                    $c = $nc;
                $extended = (array) $this->init[$c][$m];
                $c = count($extended);
                for ($i = 0; $i < $c; $i++)
                    $r .= call_user_func($extended[$i]);
            } catch (PReturn $e) {
                return $e->r();
            }
        }
        if (is_callable(array($obj, $m)))
            $r .= $obj->$m();
        return $r;
    }

    /**
     * Получение объекта(ов) модуля
     * @param string $module_name имя модуля
     * @param bool|int $is_block если true, то к пути к модулю добавлется путь до блока
     * 1 - файл из АЦ(modules)
     * 2 - страница из АЦ(pages)
     * @param bool $ajax если true, то возвращает объект модуля с постфиксом "_ajax"
     * (если существует)
     * @return object объект модуля. При отсутсвии, возвращает объект empty_class
     */
    public function get_module($module_name, $is_block = false, $ajax = false) {
        $p = MODULES_PATH;
        $is_admin = false;
        $is_adminpage = false;
        if ($is_block === 1) {
            $p = ADMIN_MODULES_PATH;
            $is_admin = true;
            $is_block = false;
        } elseif ($is_block === 2) {
            $p = ADMIN_PAGES_PATH;
            $is_adminpage = true;
            $is_block = false;
        } elseif ($is_block) {
            $p .= '/' . BLOCKS_PATH;
            $is_block = true;
        } elseif (class_exists("config") && !config::o()->mstate($module_name))
            return new empty_class();
        $f = ROOT . $p . "/" . $module_name . ".php";
        if (!file_exists($f))
            return null;
        if ($is_block) {
            $module_name .= "_block";
            $ajax = false;
        } elseif ($is_adminpage)
            $module_name .= "_page";
        elseif ($is_admin)
            $module_name .= "_man";
        $aj = "_ajax";
        $module_name_aj = $module_name . $aj;
        if (!class_exists($module_name, false) && !class_exists($module_name_aj, false))
            include $f;
        $o1 = $this->get_class($module_name, true);
        $o2 = $this->get_class($module_name_aj, true);
        $e2 = class_exists($o2, false);
        // дабы не было ошибок. begin
        $e1 = class_exists($o1, false);
        if (!$e1)
            $o1 = 'empty_class';
        if (!$e2)
            $o2 = 'empty_class';
        // дабы не было ошибок. end
        if (!$ajax || !$e2)
            return new $o1();
        else
            return new $o2();
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var plugins $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        $this->state = (bool) config::o()->v('plugins_on');
        if (!$this->state)
            return;
        $this->manager = new plugins_manager($this, $this->current_plugin);
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
     * @return plugins $this
     */
    public static function o() {
        if (!self::$o) {
            self::$o = 1; // предотвращение зацикливания при вызове plugins::o() в плагине 
            self::$o = new self();
        }
        return self::$o;
    }

}

?>