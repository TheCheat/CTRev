<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.message.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name                Класс вывода сообщений об ошибке, инфо и успешном выполнении
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class message {

    /**
     * Параметры функций
     * @var array $params
     */
    protected $params = array();

    /**
     * Остановить скрипт?
     * @param bool $value значение
     * @return message $this
     */
    public function sdie($value = true) {
        return $this->sparam('die', (bool) $value);
    }

    /**
     * Тип сообщения
     * @param string $value тип(info|success|error)
     * @return message $this
     */
    public function stype($value = "info") {
        return $this->sparam('type', $value);
    }

    /**
     * Заголовок сообщения
     * @param string|int $value если 0, то ничего не выводится, иначе выводится данное значение
     * @return message $this
     */
    public function stitle($value) {
        return $this->sparam('title', $value);
    }

    /**
     * Положение текста в сообщений
     * @param string $value положение(left|center|right)
     * @return message $this
     */
    public function salign($value = "center") {
        return $this->sparam('align', $value);
    }

    /**
     * Не выводить статусную картинку?
     * @param bool $value значение
     * @return message $this
     */
    public function sno_image($value = true) {
        return $this->sparam('no_image', (bool) $value);
    }

    /**
     * Выводить только message.tpl?
     * @param bool $value значение
     * @return message $this
     */
    public function sonly_box($value = true) {
        return $this->sparam('only_box', (bool) $value);
    }

    /**
     * Установка значения параметра
     * @param string $key имя параметра
     * @param mixed $value значение параметра
     * @return message $this
     */
    protected function sparam($key, $value) {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Объединение и сброс параметров
     * @param string $lang_var языковая переменная
     * @param array $vars переменные
     * @return null
     */
    protected function join_params(&$lang_var, $vars = array()) {
        $lang_var = array_merge($this->params, array('lang_var' => $lang_var,
            'vars' => $vars));
        $this->params = array();
        return $this;
    }

    /**
     * Функция вывода сообщения
     * @param string $lang_var языковая переменная, в соответствии с которой будет выводится на экран сообщение,
     * либо цельный текст.
     * @param array $vars массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
     * @return null
     */
    public function info($lang_var, $vars = array()) {
        if (!is_array($lang_var))
            $this->join_params($lang_var, $vars);
        $ajax = globals::g('ajax');
        $vars = $lang_var ['vars'];
        $type = $lang_var ['type'];
        if (isset($lang_var ['title']))
            $title = $lang_var ['title'];
        $align = $lang_var ['align'];
        $die = (bool) $lang_var ['die'];
        $no_image = (bool) $lang_var ['no_image'];
        $only_box = (bool) $lang_var ['only_box'];
        $lang_var = $lang_var ['lang_var'];
        if ($die && !tpl::o()->displayed('overall_header.tpl') && !tpl::o()->displayed('admin/header.tpl') && !$only_box && !$ajax)
            tpl::o()->display('overall_header.tpl');
        $type = ($type ? $type : "info");
        $align = ($align ? $align : "left");
        if (!$title && $title !== 0)
            $title = lang::o()->v($type);
        elseif ($title && lang::o()->visset($title))
            $title = lang::o()->v($title);
        tpl::o()->assign('type', $type);
        tpl::o()->assign('align', $align);
        tpl::o()->assign('title', $title);
        tpl::o()->assign('no_image', $no_image);
        if (!$lang_var)
            $lang_var = $title;
        $lv = lang::o()->if_exists($lang_var);
        $vars = $vars ? (array) $vars : null;
        if (is_array($vars))
            tpl::o()->assign('message', vsprintf($lv, $vars));
        else
            tpl::o()->assign('message', $lv);
        if ($die)
            tpl::o()->assign("died_mess", true);
        tpl::o()->display('message.tpl');
        if ($die && !$only_box && !$ajax) {
            if (tpl::o()->displayed('overall_header.tpl') && !tpl::o()->displayed('overall_footer.tpl'))
                tpl::o()->display('overall_footer.tpl');
            elseif (tpl::o()->displayed('admin/header.tpl') && !tpl::o()->displayed('admin/footer.tpl'))
                tpl::o()->display('admin/footer.tpl');
        }
        if ($die)
            die();
    }

    /**
     * Функция вывода ошибки(именно ошибки типа fatal error, а не сообщения об ошибке,
     * которое выводится через функцию message)
     * @param string|array $lang_var языковая переменная, в соответствии с коорой будет выводится на экран сообщение,
     * либо цельный текст, так же, может содержать в себе все остальные паремтры в качестве ассоциативного массива.
     * @param array $vars массив значений, включаемых в сообщение, работают, блягодаря функции vsprintf
     * @return null
     */
    public function error($lang_var, $vars = array()) {
        $title = $this->params['title'];
        $this->params = array();
        $ajax = globals::g('ajax');
        ob_end_clean();
        if (!$title && !is_null($title))
            $title = lang::o()->v('error');
        elseif ($title && lang::o()->visset($title))
            $title = lang::o()->v($title);
        if (lang::o()->visset($lang_var)) {
            $vars = (!is_array($vars) && $vars ? array(
                        $vars) : $vars);
            if (is_array($vars))
                $message = vsprintf(lang::o()->v($lang_var), $vars);
            else
                $message = lang::o()->v($lang_var);
        }
        else
            $message = $lang_var;
        if ($ajax) {
            print($title . ": " . $message);
            die();
        }
        tpl::o()->assign('message', $message);
        tpl::o()->assign('title', $title);
        tpl::o()->display("error.tpl");
        die();
    }

}

?>