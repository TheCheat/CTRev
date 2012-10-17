<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.fbenc.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Bencode functions
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class fbenc {

    /**
     * Проверка, является ли массив списком или словарём
     * проверка идёт только по первому ключу
     * @param array $arr масив
     * @return bool true, если список
     */
    protected function is_list($arr) {
        reset($arr);
        return is_integer(key($arr));
    }

    /**
     * Получение типа переменной
     * @param mixed $m переменная
     * @return string тип
     * i - integer, s - string, l - список, d - словарь
     */
    protected function get_type($m) {
        $t = gettype($m);
        if ($t == 'double' || $t == 'float')
            $t = 'integer';
        switch ($t) {
            case "integer" :
            case "string" :
                return $t[0];
            case "array":
                if ($this->is_list($m))
                    return "l";
                else
                    return "d";
        }
    }

    /**
     * Получение необходимой функции для декодирования строки
     * @param string $c символ/строка
     * @param bool $benc то же, но для кодирования bencode
     * @return callback функция, необходимая для декодирования данной строки
     */
    protected function get_fname($c, $benc = false) {
        if (is_string($c) && isset($c[1]))
            $c = $c[0];
        $s = "b" . ($benc ? "enc" : "dec") . "_";
        switch ($c) {
            case "i":
                return $s . "int";
            case "d":
                return $s . "dict";
            case "l":
                return $s . "list";
            default:
                return $s . "str";
        }
    }

    /**
     * Bencode
     * @param array $obj массив параметров
     * @return string кодированная строка
     */
    public function benc($obj) {
        $f = $this->get_fname($this->get_type($obj), true);
        return $this->$f($obj);
    }

    /**
     * Кодирование строки в Bencode
     * @param string $s строка
     * @return string кодированная строка
     */
    protected function benc_str($s) {
        return strlen($s) . ":$s";
    }

    /**
     * Кодирование числа в Bencode
     * @param int $i число
     * @return string кодированное число
     */
    protected function benc_int($i) {
        return "i" . $i . "e";
    }

    /**
     * Кодирование массива в Bencode
     * @param array $a массив
     * @return string кодированный массив
     */
    protected function benc_list($a) {
        $s = "l";
        foreach ($a as $e) {
            $s .= $this->benc($e);
        }
        $s .= "e";
        return $s;
    }

    /**
     * Кодирование ассоциативного массива(словаря) в Bencode
     * @param array $d массив
     * @return string кодированный массив
     */
    protected function benc_dict($d) {
        $s = "d";
        $keys = array_keys($d);
        sort($keys);
        foreach ($keys as $k) {
            $v = $d [$k];
            $s .= $this->benc_str($k);
            $s .= $this->benc($v);
        }
        $s .= "e";
        return $s;
    }

    /**
     * Декодирование Bencode
     * @param string $s кодированная строка или путь к файлу
     * @param bool $f файл?
     * @return array "словарь" параметров торрент-файла
     */
    public function bdec($s, $f = false) {
        if ($f)
            $s = file_get_contents($s);
        $fname = $this->get_fname($s);
        return $this->$fname($s);
    }

    /**
     * Декодирование строки из Bencode
     * @param string $s кодированная строка
     * @param string $left ссылка на оставщуюся строку
     * @return string строка
     */
    protected function bdec_str($s, &$left = null) {
        if (!preg_match('/^(\d+):(.*)$/s', $s, $m))
            return;
        $l = $m [1];
        $s = $m [2];
        $v = substr($s, 0, $l);
        $left = substr($s, $l);
        return (string) $v;
    }

    /**
     * Декодирование числа из Bencode
     * @param string $s кодированное число
     * @param string $left ссылка на оставщуюся строку
     * @return int число
     */
    protected function bdec_int($s, &$left = null) {
        if (!preg_match('/^i(\d+)e(.*)$/s', $s, $m))
            return;
        $v = $m [1];
        if ($v === "-0")
            return;
        if ($v [0] == "0" && strlen($v) != 1)
            return;
        $left = $m[2];
        return (float) longval($v); // сначала "длинное" целое, потом меняем тип
    }

    /**
     * Декодирование массива из Bencode
     * @param string $s кодированный массив
     * @param string $left ссылка на оставщуюся строку
     * @return array массив
     */
    protected function bdec_list($s, &$left = null) {
        if ($s[0] != "l")
            return;
        $s = substr($s, 1);
        $r = array();
        while (!empty($s)) {
            if ($s[0] == "e") {
                $left = substr($s, 1);
                return (array) $r;
            }
            $f = $this->get_fname($s);
            $l = "";
            $r[] = $this->$f($s, $l);
            $s = $l;
        }
    }

    /**
     * Декодирование ассоциативного массива(словаря) из Bencode
     * @param string $s кодированный массив
     * @param string $left ссылка на оставщуюся строку
     * @return array массив
     */
    protected function bdec_dict($s, &$left = null) {
        if ($s[0] != "d")
            return;
        $s = substr($s, 1);
        $r = array();
        while (!empty($s)) {
            if ($s[0] == "e") {
                $left = substr($s, 1);
                return (array) $r;
            }
            $k = $this->bdec_str($s, $l);
            $s = $l;
            $f = $this->get_fname($s);
            $l = "";
            $r[$k] = $this->$f($s, $l);
            $s = $l;
        }
    }

    /**
     * Проверка словаря на наличие ключей и их тип
     * @param array $d ассоциативный массив
     * @param string $s список ключей, например:
     * имя1(тип1):!имя2:имя3:!имя4(тип2)
     * ! - обозначает необязательность ключа
     * типы: i - integer; s - string; l - list; d - dictionary
     * @return array массив, содержащий значения этих ключей или массив(t=>тип, v=>значение)
     * @throws EngineException 
     */
    public function dict_check($d, $s) {
        if (!is_array($d) || $this->is_list($d))
            throw new EngineException('bencode_not_dict');
        $a = explode(":", $s);
        $ret = array();
        foreach ($a as $k) {
            $t = "";
            $u = false;
            if (preg_match('/^(.*)\(([isld]{1})\)$/s', $k, $m)) {
                $k = $m [1];
                $t = $m [2];
            }
            if ($k[0] == "!") {
                $k = substr($k, 1);
                $u = true;
            }
            if (!isset($d [$k]) && !$u)
                throw new EngineException("bencode_dict_miss_keys");
            if ($t && isset($d [$k]) && $this->get_type($d [$k]) != $t)
                throw new EngineException("bencode_dict_invalid_type");
            $ret [] = $d [$k];
        }
        return $ret;
    }

    /**
     * Вывод строки на экран, для последующего чтения её клиентом
     * @param string $x строка
     * @return null
     */
    public function benc_resp_raw($x) {
        @header("Content-Type: text/plain");
        @header("Pragma: no-cache");
        print ($x);
    }

    /**
     * Вывод ошибки Bit-torrent клиенту
     * @param string $msg сообщение
     * @return null
     */
    public function err($msg) {
        $this->benc_resp_raw($this->benc(array("failure reason" => $msg)));
        exit();
    }

}

?>