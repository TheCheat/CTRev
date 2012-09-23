<?php

/**
 * Project:             CTRev
 * File:                index.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Главная страница сайта
 * @version             1.00
 */
if (!file_exists('install/lock') && file_exists('install/')) {
    @header('Location: install.php');
    die();
}
define('DELAYED_UINIT', true); // отложенная инициализация юзерей...
include_once "./include/include.php";
$module = $_GET ['module'];
$blocks->set_module($module ? $module : "index");
$this_file = $BASEURL . "index.php?module=" . $module;
$tpl->assign("this_file", $this_file);
$ajax = (bool) ($_REQUEST ['from_ajax']); // Из AJAX
$nno = (bool) ($_REQUEST ['nno']); // Стандартный класс(без постфикса '_ajax')
$tpl->assign('from_ajax', $ajax);
$tpl->assign('module_loaded', $module);
if ($module) {
    if (!allowed::o()->is($module))
        die($lang->v('module_not_exists'));
    $mod = $plugins->get_module($module, false, $ajax && !$nno);
    if (!$mod)
        die($lang->v('module_not_exists'));
    $plugins->call_init($mod, 'pre_init');
}
users_init(); // ...доседова
if ($module != "login")
    $display->siteoffline_check();
$content = "";
try {
    if ($mod) {
        if (!$ajax) {
            ob_start();
            $plugins->call_init($mod);
            $content = ob_get_contents();
            ob_end_clean();
            if (isset($mod->title))
                $tpl->assign("overall_title", $mod->title);
        } else {
            $plugins->call_init($mod);
            die();
        }
    }
} catch (EngineException $e) {
    $e->defaultCatch();
}
if (!$mod) {
    $tpl->assign('module_loaded', 'index');
    $tpl->assign("overall_title", $lang->v('index_page'));
}
$tpl->display("overall_header.tpl");
if ($mod)
    print ($content);
else
    $tpl->display("index.tpl");
$tpl->display("overall_footer.tpl");
if (!$ajax)
    $cleanup->init();
?>