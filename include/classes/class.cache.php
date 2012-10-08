<?php

/**
 * Project:            	CTRev
 * File:                class.cache.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Кеш движка
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class cache {

    /**
     * Статус кеш-системы
     * @var bool $state
     */
    private $state = true;

    /**
     * Кешируемые файлы
     * @var array $cache_files
     */
    private $cache_files = array();

    /**
     * Объект Memcache(d)
     * @var Memcache(d) $m
     */
    private $m = null;

    /**
     * Время кеширования по-умолчанию
     * @var int $time
     */
    private $time = 86400;

    /**
     * Проверка на недавнее исполнение запроса в сессии юзера
     * @param string $name имя сессии
     * @param int $mytime своё время задержки
     * @return bool нуждается ли запрос в исполнении
     */
    public function query_delay($name, $mytime = false) {
        if (!$this->state)
            return true; // нуждается же
        $time = &$_SESSION['cachetime_' . $name];
        $delay = $mytime ? $mytime : config::o()->v('delay_queries');
        if (!$delay)
            return true;
        if (!$time || $time < time() - $delay) {
            $time = time();
            return true;
        }
        return false;
    }

    /**
     * Чтение массива из кеша
     * @param string $cache_file кешируемый файл
     * @param int $mytime время жизни кеш файла, отличное от глобальных настроек
     * @return mixed массив, если файл кеширован и не требует обновления,
     * false - если необходимо обновить/создать
     */
    public function read($cache_file, $mytime = false) {
        $cache_file = validpath($cache_file);
        if (!$cache_file || !$this->state)
            return false;
        $this->cache_files[] = $cache_file;
        $f = ROOT . 'include/cache/' . $cache_file . '.html';
        $my_array = array();
        if ($this->m)
            $my_array = $this->m->get($cache_file);
        elseif (file_exists($f)) {
            $contents = file_get_contents($f);
            $my_array = unserialize($contents);
        }
        if (!$my_array)
            return false;
        $array = (array) $my_array [0];
        $time = $my_array ['!_cache_time_'];
        $delay = ($mytime ? $mytime : $this->time * 3600);
        if ($time < time() - $delay) {
            if ($this->m)
                $this->m->delete($cache_file);
            return false;
        }
        $this->pop_readed(); // Ибо уже кешировано
        return $array;
    }

    /**
     * Удаление последнего прочтённого элемента
     * @return cache $this
     */
    public function pop_readed() {
        array_pop($this->cache_files);
        return $this;
    }

    /**
     * Кеш массива
     * @param array $array записываемый массив
     * @param string $cache_file кешируемый файл
     * @return null
     */
    public function write($array, $cache_file = null) {
        if (!$this->state)
            return;
        $tmp = $cache_file;
        if (!$cache_file)
            $cache_file = end($this->cache_files);
        $my_array = array($array, '!_cache_time_' => time());
        if (!$this->m) {
            $contents = serialize($my_array);
            $f = 'include/cache/' . $cache_file . '.html';
            $cfile = file::o()->write_file($contents, $f);
            if (!$cfile)
                error("cannot_write_cache", $cache_file);
        } else
            $this->m->set($cache_file, $my_array);
        if ($tmp) {
            $k = array_search($tmp, $this->cache_files);
            if ($k)
                unset($this->cache_files[$k]);
        } else
            $this->pop_readed();
    }

    /**
     * Удаление кеш-файла
     * @param string $cache_file кеш-файл
     * @return bool true, если файл удален
     */
    public function remove($cache_file) {
        if ($this->m)
            return @$this->m->delete($cache_file);
        else
            return @unlink(ROOT . 'include/cache/' . $cache_file . '.html');
    }

    /**
     * Полная очистка кеша без скомпилированных шаблонов
     * @return bool true, если успешно очищено
     */
    public function clear() {
        if ($this->m)
            return @$this->m->flush();
        else
            return @file::o()->unlink_folder('include/cache', true, array(TEMPLATES_PATH, '.gitignore'));
    }

    /**
     * Очистка от скомпилированных шаблонов
     * @return bool true, если успешно очищено
     */
    public function clear_tpl() {
        return @file::o()->unlink_folder('include/cache/' . TEMPLATES_PATH, true, '.gitignore');
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var cache $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        $this->state = (bool) config::o()->v('cache_on');
        $this->time = (int) config::o()->v('cache_oldtime');
        if (class_exists('Memcache', false))
            $m = new Memcache();
        elseif (class_exists('Memcached', false))
            $m = new Memcached();
        if (!$m)
            return;
        list($server, $port) = explode(':', config::o()->v('memcache_server'));
        $m->addServer($server, $port);
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
     * @return cache $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>