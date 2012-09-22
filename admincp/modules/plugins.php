<?php

/**
 * Project:            	CTRev
 * File:                plugins.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление плагинами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class plugins_man {

    /**
     * Инициализация модуля управления плагинами
     * @global lang $lang
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $tpl;
        $lang->get('admin/plugins');
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
                $tpl->display('admin/plugins/add.tpl');
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Построение плагина
     * @global modsettings $modsettings
     * @global uploader $uploader
     * @global lang $lang
     * @param array $data массив данных
     * @return null 
     * @throws EngineException
     */
    protected function build($data) {
        global $modsettings, $uploader, $lang;
        $lang->get('admin/plugins');
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
        $settings = $modsettings->make($data);
        $defaults = $modsettings->save(modsettings::nocache_id, $data);
        $contents = '<?php if (!defined("INSITE")) die("Remote access denied!");
class plugin_' . $plugin . ' {
    public $version = ' . var_export((string) $version, true) . ';
    public $author = ' . var_export((string) $author, true) . ';
    public $name = ' . var_export((string) $name, true) . ';
    public $descr = ' . var_export((string) $descr, true) . ';
    public $compatibility = ' . var_export((float) $comp, true) . ';
    public $compatibility_min = ' . var_export((float) $comp_min, true) . ';
    public $compatibility_max = ' . var_export((float) $comp_max, true) . ';
    public $settings = ' . var_export((array) $settings, true) . ';
    public $settings_lang = "' . $plugin . '";
    public $defaults = ' . var_export((array) $defaults, true) . ';
    /**
     * @param plugins $plugins
     */
    public function init($plugins) {
    
    }
    /**
     * @global db $db
     * @global plugins $plugins
     * @param bool $re reinstall? without DB, only templates
     * @return bool true, if plugin successfully installed
     */
    public function install($re = false) {
        global $db, $plugins;
        return true;
    }
    /**
     * @global db $db
     * @global plugins $plugins
     * @param bool $replaced true, if all templates was successfully replaced
     * @return bool true, if plugin successfully uninstalled
     */
    public function uninstall($replaced = false) {
        global $db, $plugins;
        return true;
    }
} ?>';
        $uploader->download_headers($contents, 'plugin.' . $plugin . '.php', 'text/html');
    }

    /**
     * Селектор файлов плагинов
     * @global input $input
     * @param array $res массив уже включенных плагинов
     * @return null
     */
    public function file_selector($res = null) {
        global $input;
        $res = (array) $res;
        $mask = implode('|', array_map('mpc', $res));
        $mask = '/^plugin\.' . ($mask ? "(?!" . $mask . ")" : "") . '(\w+)\.php$/i';
        return $input->select_folder("plugin_files", PLUGINS_PATH, '', false, true, $mask, 1);
    }

    /**
     * Отображение настроек плагина
     * @global modsettings $modsettings
     * @global plugins $plugins
     * @global tpl $tpl
     * @param string $plugin имя плагина
     * @return null
     * @throws EngineException
     */
    protected function settings($plugin) {
        global $modsettings, $plugins, $tpl;
        if (!($settings = $plugins->manager->parsed_settings($plugin)))
            throw new EngineException;
        $object = $plugins->manager->object($plugin);
        $psettings = $modsettings->change_type('plugins')->display($plugin, $object, $settings, "plugins_" . $plugin, true);
        $tpl->assign('psettings', $psettings);
        $tpl->assign('id', $plugin);
        $tpl->display('admin/plugins/settings.tpl');
    }

    /**
     * Отображение списка включенных плагинов
     * @global db $db
     * @global tpl $tpl
     * @global plugins $plugins
     * @global file $file
     * @return null
     */
    protected function show() {
        global $db, $tpl, $plugins, $file;
        $r = $db->query('SELECT file FROM plugins');
        $tpl->assign('res', $db->fetch2array($r, null, array('file')));
        $themes = $file->open_folder(THEMES_PATH, true);
        $n = !$y = true;
        foreach ($themes as $theme) {
            $r = $file->is_writable(THEMES_PATH . '/' . $theme . '/' . TEMPLATES_PATH, true, true);
            $y = $y || $r === true || $r === 2;
            $n = $n || !$r || $r === 1;
        }
        $tpl->assign('trewritable', $y + !$n);
        $tpl->register_modifier('pcompatibility', array($this, 'check'));
        $tpl->register_modifier('pvar', array($plugins->manager, 'pvar'));
        $tpl->register_modifier('psettings', array($plugins->manager, 'parsed_settings'));
        $tpl->register_modifier('plugin_selector', array($this, 'file_selector'));
        $tpl->display('admin/plugins/index.tpl');
    }

    /**
     * Сохранение настроек плагина
     * @global furl $furl
     * @global string $admin_file
     * @global modsettings $modsettings
     * @global db $db
     * @global plugins $plugins
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $furl, $admin_file, $modsettings, $db, $plugins;
        $id = $data['id'];
        $settings = serialize($modsettings->save($id, $data));
        $db->update(array('settings' => $settings), 'plugins', 'WHERE file=' . $db->esc($id) . ' LIMIT 1');
        $plugins->manager->uncache();
        $furl->location($admin_file);
    }

    /**
     * Проверка совместимости плагина
     * @global plugins $plugins
     * @param string $plugin имя плагина
     * @return int 0 - несовместим, 1 - совместим, 2 - наилучшая совместимость
     */
    public function check($plugin) {
        global $plugins;
        $best = $plugins->manager->pvar($plugin, 'compatibility');
        $min = $plugins->manager->pvar($plugin, 'compatibility_min');
        $max = $plugins->manager->pvar($plugin, 'compatibility_max');
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
     * @global plugins $plugins
     * @return null
     */
    public function init() {
        global $plugins;
        $act = $_GET["act"];
        $plugin = $_POST['id'];
        switch ($act) {
            case "delete":
                $r = $plugins->manager->delete($plugin);
                break;
            case "check":
                $o = $plugins->get_module('plugins', 1);
                print($o->check($plugin));
                die();
                break;
            case "add":
                $r = $plugins->manager->add($plugin);
                break;
            case "reinstall":
                $r = $plugins->manager->install($plugin, true);
                break;
        }
        if (!$r)
            die;
        die("OK!");
    }

}

?>