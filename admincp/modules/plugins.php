<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/plugins.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление плагинами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class plugins_man {
    /**
     * Шаблон плагина
     */

    const plugin_template = 'include/plugins/plugin_template';

    /**
     * Инициализация модуля управления плагинами
     * @return null
     */
    public function init() {
        lang::o()->get('admin/plugins');
        $act = $_GET["act"];
        switch ($act) {
            case "build":
                $this->build($_POST);
                break;
            case "save":
                $this->save($_POST);
                break;
            case "settings":
                try {
                    $this->settings($_GET['id']);
                } catch (EngineException $e) {
                    die();
                }
                break;
            case "add":
                tpl::o()->display('admin/plugins/add.tpl');
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Построение плагина
     * @param array $data массив данных
     * @return null 
     * @throws EngineException
     */
    public function build($data) {
        lang::o()->get('admin/plugins');
        $data_params = array("plugin",
            "version",
            "author",
            "name",
            "descr",
            "comp",
            "comp_min",
            "comp_max");
        extract(rex($data, $data_params));
        if (!validword($plugin))
            throw new EngineException("plugins_invalid_name");
        $settings = modsettings::o()->make($data);
        $defaults = modsettings::o()->save(modsettings::nocache_id, $data);
        $vars = array($plugin, var_export((string) $version, true),
            var_export((string) $author, true), var_export((string) $name, true),
            var_export((string) $descr, true), var_export((string) $comp, true),
            var_export((string) $comp_min, true), var_export((string) $comp_max, true),
            var_export((array) $settings, true), $plugin,
            var_export((array) $defaults, true));
        $contents = @file_get_contents(ROOT . self::plugin_template);
        $contents = vsprintf($contents, $vars);
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $uploader->download_headers($contents, 'plugin.' . $plugin . '.php', 'text/plain');
    }

    /**
     * Селектор файлов плагинов
     * @param array $res массив уже включенных плагинов
     * @return null
     */
    public function file_selector($res = null) {
        $res = (array) $res;
        $mask = implode('|', array_map('mpc', $res));
        $mask = '/^plugin\.' . ($mask ? "(?!" . $mask . ")" : "") . '(\w+)\.php$/i';
        return input::o()->snull()->select_folder("plugin_files", PLUGINS_PATH, false, $mask, 1);
    }

    /**
     * Отображение настроек плагина
     * @param string $plugin имя плагина
     * @return null
     * @throws EngineException
     */
    protected function settings($plugin) {
        if (!($settings = plugins::o()->manager->parsed_settings($plugin)))
            throw new EngineException;
        $object = plugins::o()->manager->object($plugin);
        $psettings = modsettings::o()->change_type('plugins')->display($plugin, $object, $settings, "plugins_" . $plugin, true);
        tpl::o()->assign('psettings', $psettings);
        tpl::o()->assign('id', $plugin);
        tpl::o()->display('admin/plugins/settings.tpl');
    }

    /**
     * Отображение списка включенных плагинов
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT file FROM plugins');
        tpl::o()->assign('res', db::o()->fetch2array($r, null, array('file')));
        $themes = file::o()->open_folder(THEMES_PATH, true);
        $n = !$y = true;
        foreach ($themes as $theme) {
            $r = file::o()->is_writable(THEMES_PATH . '/' . $theme . '/' . TEMPLATES_PATH, true, true);
            $y = $y || $r === true || $r === 2;
            $n = $n || !$r || $r === 1;
        }
        tpl::o()->assign('trewritable', $y + !$n);
        tpl::o()->register_modifier('pcompatibility', array($this, 'check'));
        tpl::o()->register_modifier('pvar', array(plugins::o()->manager, 'pvar'));
        tpl::o()->register_modifier('psettings', array(plugins::o()->manager, 'parsed_settings'));
        tpl::o()->register_modifier('plugin_selector', array($this, 'file_selector'));
        tpl::o()->display('admin/plugins/index.tpl');
    }

    /**
     * Сохранение настроек плагина
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        $admin_file = globals::g('admin_file');
        $id = $data['id'];
        $settings = serialize(modsettings::o()->save($id, $data));
        db::o()->p($id)->update(array('settings' => $settings), 'plugins', 'WHERE file=? LIMIT 1');
        plugins::o()->manager->uncache();
        furl::o()->location($admin_file);
    }

    /**
     * Проверка совместимости плагина
     * @param string $plugin имя плагина
     * @return int 0 - несовместим, 1 - совместим, 2 - наилучшая совместимость
     */
    public function check($plugin) {
        $best = plugins::o()->manager->pvar($plugin, 'compatibility');
        $min = plugins::o()->manager->pvar($plugin, 'compatibility_min');
        $max = plugins::o()->manager->pvar($plugin, 'compatibility_max');
        if (ENGINE_VERSION == $best)
            return 2;
        if (ENGINE_VERSION >= $min && ENGINE_VERSION <= $max)
            return 1;
        return 0;
    }

}

class plugins_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     * @throws EngineException
     */
    public function init() {
        $act = $_GET["act"];
        $plugin = $_POST['id'];
        switch ($act) {
            case "delete":
                $r = plugins::o()->manager->delete($plugin);
                break;
            case "check":
                /* @var $o plugis_man */
                $o = plugins::o()->get_module('plugins', 1);
                print($o->check($plugin));
                die();
                break;
            case "add":
                $r = plugins::o()->manager->add($plugin);
                break;
            case "reinstall":
                $r = plugins::o()->manager->install($plugin, true);
                break;
        }
        if (!$r)
            throw new EngineException;
        ok();
    }

}

?>