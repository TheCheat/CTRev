<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.file.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Работа с файлами. Базовые функции, расширять плагинами не нужно.
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class file {

    /**
     * Временный путь для сортировки
     * @var string $tmp_path
     */
    private $tmp_path = '';

    /**
     * Необходимо перекешировать?
     * @var bool $recache
     */
    private $recache = false;

    /**
     * Массив кешированных результатов is_writable
     * @var array $cached
     */
    private $cached = null;

    /**
     * Проверять служебные файлы? 
     * @var bool $check_htaccess
     */
    private $check_htaccess = false;

    /**
     * Паттерн служебных файлов
     */

    const htaccess_pattern = '\.(htaccess|gitignore)';

    /**
     * Кеш-файл
     */
    const cachefile = 'writable';

    /**
     * Получение типа файла
     * @param string $path путь к файлу(имя файла)
     * @return string тип файла
     */
    public function get_filetype($path) {
        preg_match('/\.([a-zA-Z0-9]+)$/siu', $path, $matches);
        return $matches[1];
    }

    /**
     * Функция выборки всех файлов и(или) подкаталогов из данного каталога
     * @param string $folder данный каталог
     * @param bool $dironly только дирректории?
     * @param string $mask регэксп, по которому выбираются файлы(без делимиттеров)
     * @param bool|int $match возвращаемая группа в рег. выражении, если true
     * то все спарсенные группы
     * @return array файлы и(или)подкаталоги
     */
    public function open_folder($folder, $dironly = false, $mask = null, $match = null) {
        $p = ROOT . $folder;
        if (!file_exists($p))
            return false;
        $dir = opendir($p);
        $ret = array();
        while ($res = readdir($dir)) {
            if ($res == "." || $res == "..")
                continue;
            if ($mask && !preg_match('/' . $mask . '/siu', $res, $matches))
                continue;
            if ($dironly && !is_dir(ROOT . $folder . '/' . $res))
                continue;
            if (!is_null($match) && $mask) {
                if ($match === true)
                    $ret[] = $matches;
                else
                    $ret[] = $matches[$match];
            }
            else
                $ret[] = $res;
        }
        closedir($dir);
        return $ret;
    }

    /**
     * Сортировка файлов
     * @param string $path путь к файлам
     * @param array $files массив файлов
     * @return null 
     */
    public function sort($path, &$files) {
        $this->tmp_path = ROOT . $path . '/';
        usort($files, array($this, 'sort_impl'));
    }

    /**
     * Сортировка массива
     * @param string $a первое значение
     * @param string $b второе значение
     * @return int куда надо переместить значения
     */
    private function sort_impl($a, $b) {
        $adir = is_dir($this->tmp_path . $a);
        $bdir = is_dir($this->tmp_path . $b);
        if ($adir == $bdir)
            return $a < $b ? -1 : $a == $b ? 0 : 1;
        elseif ($adir)
            return -1;
        else
            return 1;
    }

    /**
     * Проверять служебные файлы(.htaccess/.gitignore) в данном is_writable?
     * @note по-умолчанию не проверяются, автоматически отключается, если
     * проверяется папка
     * @param bool $state true, если проверять
     * @return file $this
     */
    public function check_htaccess($state = true) {
        $this->check_htaccess = (bool) $state;
        return $this;
    }

    /**
     * Записываемый ли файл(папка)?
     * @param string $filename путь к файлу(папке)
     * @param bool $chmod пытаться установить права на запись?
     * @param bool $recursive проверять все файлы в данной папке?
     * в данном случае вернётся 2, если все файлы записываемы
     * 1, если записываема часть файлов
     * 0, если не записываемы все файлы
     * @param bool $nonexists проверять на возможность записи в дирректорию, 
     * если файл отсутствует?
     * @return bool записываем ли?
     */
    public function is_writable($filename, $chmod = true, $recursive = false, $nonexists = false) {
        if ($recursive && $this->cached[$filename])
            return $this->cached[$filename];
        $rfilename = ROOT . $filename;
        if ($recursive && is_dir($rfilename)) {
            $files = $this->open_folder($filename);
            $c = count($files);
            if ($chmod)
                @chmod($rfilename, 0777);
            $n = !$y = is_writable($filename);
            for ($i = 0; $i < $c; $i++) {
                if (!$this->check_htaccess && preg_match('/^' . self::htaccess_pattern . '$/siu', $files[$i]))
                    continue;
                $r = $this->is_writable($filename . '/' . $files[$i], $chmod, 'inrec');
                $y = $y || $r === true || $r === 2;
                $n = $n || !$r || $r === 1;
            }
            $r = $y + !$n;
            if ($recursive !== 'inrec') {
                $this->check_htaccess = false;
                $this->recache = true;
                $this->cached[$filename] = $r;
            }
            return $r;
        } elseif ($nonexists && !is_dir($rfilename) && !file_exists($rfilename)) {
            preg_match('/(.*)\/[^\/]+?/su', $filename, $matches);
            if ($matches) {
                $filename = $matches[1];
                $rfilename = ROOT . $filename;
            }
        }
        if ($chmod)
            @chmod($rfilename, 0777);
        return is_writable($filename);
    }

    /**
     * Получение MIME файла
     * @param string $filepath путь к файлу
     * @return string MIME-тип файла
     */
    public function get_content_type($filepath) {
        if (function_exists('mime_content_type')) {
            return @mime_content_type($filepath);
        } elseif (function_exists("finfo_open") && function_exists("finfo_file")) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if (!$finfo)
                return null;
            $ret = finfo_file($finfo, $filepath);
            finfo_close($finfo);
            return $ret;
        }
        else
            return null;
    }

    /**
     * Удаляет дирректорию и всё, что в ней лежит
     * @param string $path путь к дирректории
     * @param bool $savedirs не удалять дирректории
     * @param mixed $except исключая...
     * @param bool $fullpath полный путь в исключениях?
     * @return null
     */
    public function unlink_folder($path, $savedirs = false, $except = null, $fullpath = false) {
        $npath = ROOT . $path;
        $epath = $path;
        if (!$fullpath && $except)
            $epath = preg_replace('/^(.*)(\/|\\\)(.+?)$/siu', '\3', $epath);
        if ($except && in_array($epath, (array) $except))
            return;
        if (is_dir($npath)) {
            $r = $this->open_folder($path);
            $c = count($r);
            for ($i = 0; $i < $c; $i++)
                $this->unlink_folder($path . '/' . $r[$i], $savedirs, $except);
            if (!$savedirs)
                rmdir($npath);
        }
        else
            unlink($npath);
    }

    /**
     * Копирует дирректорию и всё, что в ней лежит
     * @param string $path путь к дирректории
     * @param string $newpath новый путь
     * @return null
     */
    public function copy_folder($path, $newpath) {
        $npath = ROOT . $path;
        $nnewpath = ROOT . $newpath;
        if (is_dir($npath)) {
            $r = $this->open_folder($path);
            $c = count($r);
            mkdir($nnewpath);
            for ($i = 0; $i < $c; $i++)
                $this->copy_folder($path . '/' . $r[$i], $newpath . '/' . $r[$i]);
        }
        else
            copy($npath, $nnewpath);
    }

    /**
     * Запись файла
     * @param string $content содержимое
     * @param string $path путь записи от корня
     * @return bool статус записи
     */
    public function write_file($content, $path) {
        $path = ROOT . $path;
        @mkdir(dirname($path), 0777, true);
        @chmod($path, 0777);
        $f = fopen($path, 'w');
        flock($f, LOCK_EX);
        $s = fwrite($f, $content);
        flock($f, LOCK_UN);
        fclose($f);
        return (bool) $s;
    }

    /**
     * Деструктор. Запись кеша
     * @return null
     */
    public function __destruct() {
        if (!class_exists('cache'))
            return;
        if ($this->recache)
            cache::o()->write($this->cached, self::cachefile);
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var file $o
     */
    private static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    private function __construct() {
        if (!class_exists('cache'))
            return;
        $this->cached = cache::o()->read(self::cachefile);
        if ($this->cached === false)
            cache::o()->pop_readed();
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
     * @return file $this
     */
    public static function o() {
        if (!self::$o)
            self::$o = new self();
        return self::$o;
    }

}

?>