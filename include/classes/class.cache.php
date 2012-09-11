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
     * @var bool
     */
    private $state = true;

    /**
     * Кешируемые файлы
     * @var array
     */
    private $cache_files = array();

    /**
     * Объект Memcache(d)
     * @var Memcache(d) $m
     */
    private $m = null;

    /**
     * Время кеширования по-умолчанию
     * @var int
     */
    private $time = 86400;

    /**
     * Инициализация кеша
     * @global config $config
     * @return null
     */
    public function init() {
        global $config;
        $this->state = (bool) $config->v('cache_on');
        $this->time = (int) $config->v('cache_oldtime');
        if (class_exists('Memcache', false))
            $m = new Memcache();
        elseif (class_exists('Memcached', false))
            $m = new Memcached();
        if (!$m)
            return;
        list($server, $port) = explode(':', $config->v('memcache_server'));
        $m->addServer($server, $port);
    }

    /**
     * Проверка на недавнее исполнение запроса в сессии юзера
     * @global config $config
     * @param string $name имя сессии
     * @param int $mytime своё время задержки
     * @return bool нуждается ли запрос в исполнении
     */
    public function query_delay($name, $mytime = false) {
        global $config;
        if (!$this->state)
            return true; // нуждается же
        $time = &$_SESSION['cachetime_' . $name];
        $delay = $mytime ? $mytime : $config->v('delay_queries');
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
        array_pop($this->cache_files); // Ибо уже кешировано
        return $array;
    }

    /**
     * Кеш массива
     * @global file $file
     * @param array $array записываемый массив
     * @param string $cache_file кешируемый файл
     * @return null
     */
    public function write($array, $cache_file = null) {
        global $file;
        if (!$this->state)
            return;
        $tmp = $cache_file;
        if (!$cache_file)
            $cache_file = end($this->cache_files);
        $my_array = array($array, '!_cache_time_' => time());
        if (!$this->m) {
            $contents = serialize($my_array);
            $f = 'include/cache/' . $cache_file . '.html';
            $cfile = $file->write_file($contents, $f);
            if (!$cfile)
                error("cannot_write_cache", $cache_file);
        } else
            $this->m->set($cache_file, $my_array);
        if ($tmp) {
            $k = array_search($tmp, $this->cache_files);
            if ($k)
                unset($this->cache_files[$k]);
        } else
            array_pop($this->cache_files);
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
     * @global file $file
     * @return bool true, если успешно очищено
     */
    public function clear() {
        global $file;
        if ($this->m)
            return @$this->m->flush();
        else
            return @$file->unlink_folder('include/cache', true, array(TEMPLATES_PATH, '.gitignore'));
    }

    /**
     * Очистка от скомпилированных шаблонов
     * @global file $file
     * @return bool true, если успешно очищено
     */
    public function clear_tpl() {
        global $file;
        return @$file->unlink_folder('include/cache/' . TEMPLATES_PATH, true, '.gitignore');
    }

}

?>