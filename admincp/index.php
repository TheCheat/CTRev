<?php

/**
 * Project:            	CTRev
 * File:                index.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2009-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Основной файл, загружающий модули АЦ
 * @version           	1.00b
 */
if (!defined('INSITE'))
    die("Remote access denied!");
$allowed = "acp_modules";
$allowed_admin_pages = "acp_pages";
if (!($admin_modules = cache::o()->read('admin_modules'))) {
    $admin_modules = array();
    $r = db::o()->query('SELECT ai.name AS ai_name, ac.name AS ac_name, am.name AS am_name, am.link 
    FROM admin_items AS ai 
    LEFT JOIN admin_cats AS ac ON ac.item=ai.id
    LEFT JOIN admin_modules AS am ON am.cat=ac.id
    ORDER BY am.id');
    while ($row = db::o()->fetch_assoc($r))
        $admin_modules[$row['ai_name']][$row['ac_name']][$row['am_name']] = $row['link'];
    cache::o()->write($admin_modules);
}
users::o()->check_perms('acp');
$item_mainpage = false;
/**
 * Узнаём, в какой вкладке сейчас находимся
 */
if ($_GET ['item'] && $admin_modules[$_GET ['item']]) {
    $item = $_GET ['item'];
    $item_mainpage = true;
} else {
    $item = "main";
    $item_mainpage = true;
}
/**
 * Узнаём, какой модуль затребован
 */
$module = $_GET ['module'];
$admin_page = $_GET['page'];
$ajax = (bool) ($_REQUEST ['from_ajax']); // Из AJAX
$nno = (bool) ($_REQUEST ['nno']); // Стандартный класс(без постфикса '_ajax')
$plugins_isblock = 1;
/**
 * Узнаём, какой пункт выбран
 */
if ($module && !$ajax) {
    $urls = array();
    foreach ($admin_modules [$item] as $this_cat) {
        if (is_array($this_cat))
            $urls = array_merge($urls, $this_cat);
    }
    $max_symb = 0;
    foreach ($urls as $key => $url) {
        $len_url = strlen($url);
        parse_str($url, $parsed_url);
        $not_in_array = false;
        foreach ($parsed_url as $kkey => $value) {
            if ($_GET [$kkey] != $value) {
                $not_in_array = true;
                break;
            }
        }
        if (!$not_in_array && $len_url >= $max_symb) {
            $imod = $key;
            $max_symb = $len_url;
        }
    }
}
/**
 * То, что не разрешено - запрещено 
 */
users::o()->acp_modules();
$allowed_acp_mods = (array) users::o()->perm('acp_modules');

if (!users::o()->perm('acp', 2) && !$ajax) {
    foreach ($admin_modules as $k => $v) {
        foreach ($v as $kk => $vv) {
            foreach ($vv as $kkk => $vvv) { // НУЖНО БОЛЬШЕ VVV, KKK
                parse_str($vvv, $a);
                if (!in_array($a['module'], $allowed_acp_mods))
                    unset($admin_modules[$k][$kk][$kkk]);
            }
            if (!$admin_modules[$k][$kk])
                unset($admin_modules[$k][$kk]);
        }
        if (!$admin_modules[$k])
            unset($admin_modules[$k]);
    }
}
if ($item_mainpage && !$module && !$ajax) {
    // Есть "особая" страница для категории
    if (!$admin_page && file_exists(ROOT . ADMIN_PAGES_PATH . '/' . $item . '.php'))
        $admin_page = $item;
    else {
        // Выбираем первый попавшийся модуль
        $icat = current($admin_modules [$item]);
        $imod = key($icat);
        parse_str(current($icat), $index_module);
        $module = $index_module ['module'];
        if ($module && file_exists(ROOT . ADMIN_MODULES_PATH . '/' . $module . '.php'))
            $_GET = array_merge($_GET, $index_module);
        else
            $module = $imod = '';
    }
}
/**
 * Передаём часть переменных в Smarty Tpl.
 */
tpl::o()->assign("selected_item", $item);
if ($imod)
    tpl::o()->assign("selected_imod", $imod);
tpl::o()->assign("imods", $admin_modules);
lang::o()->get("admin/main");
users::o()->check_inadmin($module, false, true);
$iadmin_file = $eadmin_file . '&item=' . $item;
tpl::o()->assign("iadmin_file", $iadmin_file);
if ($module) {
    $admin_file = $iadmin_file . '&module=' . $module;
    tpl::o()->assign("admin_file", $admin_file);
    $admin_page = null;
} elseif ($admin_page) {
    $module = $admin_page;
    $allowed = $allowed_admin_pages;
    $plugins_isblock = 2;
    $admin_file = $iadmin_file . '&page=' . $admin_page;
    tpl::o()->assign("admin_file", $admin_file);
}
/**
 * Загружаем модуль, или индексную страничку во вкладке
 */
if (!$ajax)
    tpl::o()->display("admin/header.tpl");
else {
    db::o()->nt_error();
    tpl::o()->assign("from_ajax", 1);
}
if ($module) {
    if (!allowed::o()->is($module, $allowed))
        die(lang::o()->v('module_not_exists'));
    $m = plugins::o()->get_module($module, $plugins_isblock, $ajax && !$nno);
    try {
        plugins::o()->call_init($m);
    } catch (EngineException $e) {
        $e->defaultCatch();
    }
}
if (!$ajax)
    tpl::o()->display("admin/footer.tpl");
die();
?>