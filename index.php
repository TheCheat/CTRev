<?php

/**
 * Project:             CTRev
 * @file                /index.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
blocks::set_module($module ? $module : "index");
$this_file = globals::g('baseurl') . "index.php?module=" . $module;
tpl::o()->assign("this_file", $this_file);
$ajax = (bool) ($_REQUEST ['from_ajax']); // Из AJAX
$nno = (bool) ($_REQUEST ['nno']); // Стандартный класс(без постфикса '_ajax')
globals::s('ajax', $ajax);
tpl::o()->assign('from_ajax', $ajax);
tpl::o()->assign('module_loaded', $module);
if ($module) {
    if (!allowed::o()->is($module))
        die(lang::o()->v('module_not_exists'));
    $mod = plugins::o()->get_module($module, false, $ajax && !$nno);
    if (!$mod)
        die(lang::o()->v('module_not_exists'));
    plugins::o()->call_init($mod, 'pre_init');
}
users_init(); // ...доседова
if ($module != "login")
    display::o()->siteoffline_check();
$content = "";
try {
    if ($mod) {
        if (!$ajax) {
            ob_start();
            if ($mod instanceof empty_class)
                disabled(false);
            plugins::o()->call_init($mod);
            $content = ob_get_contents();
            ob_end_clean();
            if (isset($mod->title))
                tpl::o()->assign("overall_title", $mod->title);
        } else {
            plugins::o()->call_init($mod);
            print('<script type="text/javascript">ajax_complete();</script>');
            die();
        }
    }
} catch (EngineException $e) {
    $e->defaultCatch();
}
if (!$mod) {
    tpl::o()->assign('module_loaded', 'index');
    tpl::o()->assign("overall_title", lang::o()->v('index_page'));
}
tpl::o()->display("overall_header.tpl");
if ($mod)
    print ($content);
else
    tpl::o()->display("index.tpl");
tpl::o()->display("overall_footer.tpl");
if (!$ajax)
    n("cleanup")->execute();
?>