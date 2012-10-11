<?php

/**
 * Project:            	CTRev
 * File:                class.lang.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Языковая система движка
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class lang {
    /**
     * Префикс для сплиттера
     */

    const presplitter = '{lang_splitted}';

    /**
     * Дирректория для языка
     * @var string $folder
     */
    private $folder = '';

    /**
     * Подключённые языки
     * @var array $included
     */
    private $included = array();

    /**
     * Языковые переменные
     * @var array $vars
     * @note protected, ибо предполагается, что неизменно в процессе работы. 
     */
    private $vars = array();

    /**
     * Последний используемый язык
     * @var string $last
     */
    private $last = null;

    /**
     * Соединяемые языковые файлы
     * @var array $binding
     */
    private $binding = array();

    /**
     * Сменить дирректорию языка
     * @param string $folder новая дирректория языка
     * @param bool $join присоединить system и main из новой дирректории?
     * @return null
     */
    public function change_folder($folder, $join = true) {
        if ($folder == $this->folder || !$folder)
            return;
        $this->folder = $folder;
        if ($join) {
            $this->get("system");
            $this->get("main");
        }
    }

    /**
     * Получение язык. переменной для данной строки, если существует
     * @param string $var данная строка
     * @return string если существует, то возвращает значение язык. переменной,
     * иначе просто возвращает данную строку
     */
    public function if_exists($var) {
        if (isset($this->vars[$var]))
            return $this->vars[$var];
        else
            return $var;
    }

    /**
     * Существует ли языковая переменная?
     * @param string $var имя переменной
     * @return bool true, если существует
     */
    public function visset($var) {
        return isset($this->vars[$var]);
    }

    /**
     * Получение значения языковой переменной
     * @param string $var имя переменной
     * @param bool $nsure возможно отстутствие переменной. Тогда возвратится пустая строка.
     * @return string значение 
     */
    public function v($var, $nsure = false) {
        if ($nsure && !isset($this->vars[$var]))
            return '';
        if (isset($this->vars[$var]))
            return $this->vars[$var];
        else
            return "LANG_" . $var;
    }

    /**
     * Подключение языка
     * @param string $file подключаемый файл(!тип файла не указывается!)
     * @param string $folder дирректория языка
     * @param bool $join соединить с остальными переменными? Иначе возвращает массив.
     * @return bool в зависимости от статуса
     */
    public function get($file, $folder = null, $join = true) {
        $file = strtolower($file);
        if (!$folder)
            $folder = $this->folder;
        if ($join) {
            if ($folder != $this->last) {
                $this->last = $folder;
                $this->included = null;
            }
            if ($this->included [$file])
                return true;
        }
        $o = ROOT . LANGUAGES_PATH . '/' . $folder . '/' . $file . '.php';
        if (file_exists($o))
            include $o;
        else
            return false;
        if (!$languages)
            return false;
        if (!$join)
            return $languages;
        $this->remove_splitters($languages);
        $this->vars = array_merge($this->vars, $languages);
        $this->included [$file] = true;
        if ($this->binding [$file]) {
            $bind = (array) $this->binding [$file];
            $c = count($bind);
            for ($i = 0; $i < $c; $i++)
                $this->get($bind[$i], $folder);
        }
        return true;
    }

    /**
     * Замена языкового файла
     * @param string $f изменяемый файл(!тип файла не указывается!)
     * @param array $arr новый массив
     * @param string $folder дирректория языка
     * @return null
     */
    public function set($f, $arr, $folder = null) {
        if (!$folder)
            $folder = $this->folder;
        $out = '$languages = array(';
        foreach ($arr as $key => $value)
            if (is_numeric($key))
                $out .= "\$this->splitter(" . var_export($this->cut_splitter($value), true) . "),\n";
            else
                $out .= "\t" . var_export($key, true) . " => " . var_export($value, true) . ",\n";
        $out = "<?php\n" . rtrim($out, ",\n") . ");\n?>";
        file::o()->write_file($out, LANGUAGES_PATH . '/' . $folder . '/' . $f . '.php');
    }

    /**
     * Разделитель внутри языкового файла
     * @param string $header заголовок разделителя
     * @return string текст разделителя
     */
    private function splitter($header) {
        $header = preg_replace('/[^\w\-]+/si', '', mb_strtoupper($header));
        if (!$header)
            return null;
        return self::presplitter . $header;
    }

    /**
     * Удаление presplitter из заголовка
     * @param string $str строка
     * @return string изменённая строка 
     */
    public function cut_splitter($str) {
        if (mb_strpos($str, self::presplitter) === 0)
            return mb_substr($str, mb_strlen(self::presplitter));
        return $str;
    }

    /**
     * Удаление сплиттеров из языка
     * @param array $languages массив языковых переменных
     * @return null
     */
    private function remove_splitters(&$languages) {
        $i = 0;
        while ($languages[$i])
            unset($languages[$i++]);
    }

    /**
     * Автоматически подгружать соединяемый файл вместе с оригинальным
     * @param string $file оригинальный файл
     * @param string $with соединяемый файл
     * @return lang $this
     */
    public function bind($file, $with) {
        if (!$this->binding[$file])
            $this->binding[$file] = array();
        $this->binding[$file][] = $with;
        return $this;
    }

    // Реализация Singleton

    /**
     * Объект данного класса
     * @var lang $o
     */
    private static $o = null;

    /**
     * Конструктор
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
     * @return lang $this
     */
    public static function o() {
        if (!self::$o) {
            self::$o = new self();
            self::$o->change_folder(DEFAULT_LANG);
        }
        return self::$o;
    }

}

?>