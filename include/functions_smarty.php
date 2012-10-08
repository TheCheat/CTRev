<?php

/**
 * Project:            	CTRev
 * File:                functions_smarty.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Функции для Smarty
 * @version           	1.00
 */


if (!defined('INSITE'))
    die('Remote access denied!');

/**
 * Аналог функции addslashes, но с возможностью убирания перевода на новую строку
 * @param string $string обрабатываемая строка
 * @param bool $cut_newline при true, экранируются переводы на новую строку
 * @return string экранированная строка
 */
function slashes_smarty($string, $cut_newline = true) {
    $string = addslashes($string);
    if ($cut_newline)
        $string = str_replace("\n", "\\" . "\n", $string);
    return $string;
}

/**
 * Замена всех & на &amp; в строке
 * @param string $string входная строка
 * @return string выходная строка
 */
function w3c_amp_replace($string) {
    return str_replace("&", "&amp;", str_replace("&amp;", "&", $string));
}

/**
 * Проверка на принадлежность данного пользователя к ч\л
 * @param int $owner_id ID пользователя, добавившего данный ресурс
 * @param string $rule правило, в котором, если пользователь является Owner, то его право должно быть 1 или 2,
 * если не является - только 2(!без префикса can_!)
 * @return bool принадлежность
 */
function check_owner($owner_id, $rule) {
    return (users::o()->v('id') == $owner_id && !users::o()->perm($rule, 2)) || users::o()->perm($rule, 2);
}

/**
 * Модификатор, аналог sprintf
 * @param string $str входящая строка
 * @return string отфарматированная строка
 */
function smarty_print_format($str) {
    $args = func_get_args();
    unset($args [0]);
    return vsprintf(lang::o()->if_exists($str), $args);
}

/**
 * Создание тега для atom:id
 * @global string $PREBASEURL
 * @param array $params массив параметров(time - время, title - заголовок, id - ID торрента)
 * @return string тег
 */
function smarty_make_atom_tag($params) {
    global $PREBASEURL;
    $time = $params ['time'];
    $title = $params ['title'];
    $id = $params ['id'];
    $tag = $_SERVER ['SERVER_NAME'] . ",";
    $tag .= display::o()->date($time, "Y-m-d") . ":";
    $tag .= $PREBASEURL . furl::o()->construct('torrents', array(
                'title' => $title,
                'id' => $id), false, false, true);
    return $tag;
}

/**
 * Информация об использовании приложением ОЗУ и генерации страницы
 * @global int $start
 * @global string $theme_path
 * @return string строка с информацией
 */
function smarty_get_memory_usage() {
    global $start, $theme_path;
    if (function_exists('memory_get_usage'))
        $memory_usage = display::o()->convert_size(memory_get_usage());
    return sprintf(lang::o()->v('page_loaded_in'), timer() - $start, count(db::o()->query_stat)) .
            ($memory_usage ? sprintf(lang::o()->v('memory_loaded'), $memory_usage) : "");
}

/**
 * Генератор пароля для шаблонов Smarty
 * @global string $theme_path
 * @param array $params массив параметров(pname - имя поля пароля, paname - имя поля повтора пароля)
 * @return string HTML код генератора
 */
function smarty_passgen($params) {
    global $theme_path;
    $name = $params["pname"];
    $name2 = $params["paname"];
    return '<img src="' . $theme_path . 'engine_images/passgen.png" alt="' . lang::o()->v('passgen') . '" class="passgen clickable"
        title="' . lang::o()->v('passgen') . '" onclick="passgen(\'' . addslashes($name) . '\', \'' . addslashes($name2) . '\');">';
}

/**
 * Аналог функции для "окрашивания" группы пользователя
 * @param string $user_name имя группы
 * @param int $group_id ID группы
 * @param bool $bbcode BBCode?
 * @return string HTML код окрашенной группы
 */
function smarty_group_color_link($user_name, $group_id = null, $bbcode = false) {
    if (mb_strtolower($user_name) === 'curuser') {
        $user_name = users::o()->v('username');
        $group_id = users::o()->v('group');
    } else {
        if (!$user_name)
            $user_name = 'anonym';
        if (!$group_id)
            $group_id = users::o()->find_group('guest');
    }
    return smarty_user_link(display::o()->group_color($group_id, $user_name, $bbcode), $user_name, $bbcode);
}

/**
 * Функция для превращения имени пользователя в ссылку
 * @param string $text имя пользователя либо HTML текст
 * @param string $subtext имя пользователя
 * @param bool $bbcode BBCode?
 * @return string HTML код ссылки
 */
function smarty_user_link($text, $subtext = "", $bbcode = false) {
    if (!$subtext)
        if (!users::o()->check_login($text)) {
            $gr = users::o()->get_group(users::o()->find_group('guest'));
            $subtext = $gr ["name"];
        }
    $quote = display::o()->html_encode('"');
    $aopen = $bbcode ? '[url=' . $quote : "<a class='profile_link' href='";
    $aopen2 = $bbcode ? $quote . ']' : "'>";
    $aclose = $bbcode ? '[/url]' : '</a>';
    return (users::o()->perm('profile') ? $aopen . furl::o()->construct("users", array(
                        "user" => (!$subtext ? $text : $subtext))) . $aopen2 : "") . $text .
            (users::o()->perm('profile') ? $aclose : "");
}

/**
 * Вывод статистики по запросам
 * @return string HTML код
 */
function query_stat() {
    if (!config::o()->v('show_process') || !users::o()->perm('acp', 2))
        return;
    $r = "";
    foreach (db::o()->query_stat as $n => $stat)
        $r .= "[" . ($n + 1) . "] => 
            <b><font color='" . ($stat['seconds'] > 0.01 ? "red" : "green") . "'>
                " . $stat["seconds"] . "</font></b> [" . $stat["query"] . "]<br>";
    return "<div class='query_stat'>" . $r . "</div>";
}

/**
 * Замена перевода на новую строку для однострочного инпата
 * @param string $text исходный текст
 * @param bool $decode обратное действие?
 * @return string обработанный текст
 */
function replace_newline($text, $decode = false) {
    if ($decode)
        return str_replace('\n', "\n", $text);
    else
        return str_replace("\n", '\n', $text);
}

/**
 * Получение значения параметра определённой группы
 * @param int $group ID группы
 * @param string $param имя параметра
 * @return mixed значение параметра
 */
function smarty_group_value($group, $param) {
    $gr = users::o()->get_group($group);
    return $gr[$param];
}

?>