<?php

/**
 * Project:            	CTRev
 * File:                styles.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление темами
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class styles_man {

    /**
     * Доступные папки
     * @var array
     */
    public static $spaths = array(
        TEMPLATES_PATH,
        'js',
        'css');

    /**
     * Разрешённые типы файлов для шаблонов
     * @var array
     */
    protected $allowed_types = array(
        TEMPLATES_PATH => array('tpl', 'xtpl'),
        'js' => 'js',
        'css' => 'css'
    );

    /**
     * Инициализация управления темами
     * @global lang $lang
     * @global array $POST
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $POST, $tpl;
        $lang->get('admin/styles');
        $act = $_GET['act'];
        switch ($act) {
            case "add":
            case "edit":
                try {
                    $this->add($_GET['id'], $_GET['file'], $act == "add");
                } catch (EngineException $e) {
                    $e->defaultCatch(true);
                }
                break;
            case "files":
                $this->files($_GET['id'], $_REQUEST['folder']);
                break;
            case "save":
                $_POST['content'] = $POST['content'];
                $this->save($_POST);
                break;
            case "search":
                if ($_GET['results']) {
                    $_POST['search'] = $POST['search'];
                    $this->search($_POST['id'], $_POST);
                } else {
                    $tpl->assign('apaths', self::$spaths);
                    $tpl->display('admin/styles/search.tpl');
                }
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Поиск в темах
     * @global tpl $tpl
     * @global search $search
     * @param string $name имя темы
     * @param array $data массив данных
     * @return null
     * @throws EngineException
     */
    protected function search($name, $data) {
        global $tpl, $search;
        $cols = array(
            'what' => 'search',
            'where',
            'regexp');
        $data = rex($data, $cols);
        extract($data);
        $regexp = (bool) $regexp;
        if (!validfolder($name, THEMES_PATH))
            throw new EngineException;
        if (!$what || mb_strlen($what) < 2)
            throw new EngineException('nothing_selected');
        $arr = array();
        $where = (array) $where;
        foreach ($where as $w) {
            if (!self::$spaths[$w])
                return;
            $res = $search->search_infiles(THEMES_PATH . '/' . $name . '/' . self::$spaths[$w], $what, $regexp);
            $a = array();
            foreach ($res as $k => $r)
                $a[self::$spaths[$w] . '/' . $k] = $r;
            if ($arr)
                $arr = array_merge($arr, $a);
            else
                $arr = $a;
        }
        $tpl->assign('row', $arr);
        $tpl->assign('id', $name);
        $data['search'] = $data['what'];
        unset($data['what']);
        $tpl->assign('postdata', http_build_query($data));
        $tpl->display('admin/styles/results.tpl');
    }

    /**
     * Добавление/Редактирование темы
     * @global tpl $tpl
     * @param string $name имя темы
     * @param string $f2e файл
     * @param bool $add добавление?
     * @return null
     * @throws EngineException
     */
    protected function add($name, $f2e, $add = false) {
        global $tpl;
        if (!validfolder($name, THEMES_PATH))
            throw new EngineException;
        $f2e = validpath($f2e, false, self::$spaths);
        $folder = dirname($f2e) . '/';
        $p = mb_strpos($f2e, '/');
        $ffu = mb_substr($f2e, 0, $p);
        $sf2e = mb_substr($f2e, $p + 1);
        if ($folder == './')
            $folder = '';
        $fpath = ROOT . THEMES_PATH . '/' . $name . '/' . $f2e;
        if (is_dir($fpath) || !file_exists($fpath))
            $add = true;
        if (!$add)
            $contents = file_get_contents($fpath);
        $tpl->assign('id', $name);
        $tpl->assign('parent', $add ? $f2e : $folder);
        $tpl->assign('filename', $sf2e);
        $tpl->assign('folder', $ffu);
        $tpl->assign('file', !$add ? $sf2e : '');
        $tpl->assign('is_writable', is_writable($fpath));
        $tpl->assign('contents', $contents);
        $tpl->display('admin/styles/add.tpl');
    }

    /**
     * Выбор файла темы
     * @global display $display
     * @param string $name тема
     * @param string $folder выбранная дирректория
     * @return null
     */
    protected function files($name, $folder = null) {
        global $display;
        $display->filechooser(THEMES_PATH, $name, $folder, self::$spaths);
    }

    /**
     * Вывод списка тем
     * @global tpl $tpl
     * @global file $file
     * @return null 
     */
    public function show() {
        global $tpl, $file;
        $rows = $file->open_folder(THEMES_PATH, true);
        $tpl->assign('rows', $rows);
        $tpl->register_modifier('get_style_conf', array($tpl, 'init_cfg'));
        $tpl->display('admin/styles/index.tpl');
    }

    /**
     * Сохранение файла темы
     * @global furl $furl
     * @global string $admin_file
     * @global file $file
     * @global tpl $tpl
     * @param array $data массив данных файла темы
     * @return null
     * @throws EngineException 
     */
    protected function save($data) {
        global $furl, $admin_file, $file, $tpl;
        $cols = array(
            'name' => 'id',
            'of2e' => 'file',
            'f2e' => 'filename',
            'folder',
            'content');
        extract(rex($data, $cols));
        if (!validfolder($name, THEMES_PATH))
            throw new EngineException;
        if (!in_array($folder, self::$spaths))
            throw new EngineException('styles_wrong_filename');
        $types = implode('|', array_map('mpc', (array) $this->allowed_types[$folder]));
        $regexp = '/^([\w\.\/]+)\.(' . $types . ')$/si';
        $f2e = validpath($f2e);
        $of2e = validpath($of2e);
        if (!preg_match($regexp, $f2e) || ($of2e && !preg_match($regexp, $of2e)))
            throw new EngineException('styles_wrong_filename');
        $opath = THEMES_PATH . "/" . $name . '/' . $folder . '/';
        $path = ROOT . $opath;
        if ($f2e != $of2e && file_exists($path . $f2e))
            throw new EngineException('styles_file_exists');
        if ($of2e != $f2e && $of2e)
            unlink($path . $of2e);
        $file->write_file($content, $opath . $f2e);
        if (!$of2e)
            log_add('changed_style_file', 'admin', array($f2e, $of2e, $name));
        else
            log_add('added_style_file', 'admin', array($f2e, $name));
        $furl->location($admin_file);
    }

}

class styles_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @global array $POST
     * @return null
     */
    public function init() {
        global $POST;
        $act = $_GET['act'];
        $name = $_POST['id'];
        if (!validfolder($name, THEMES_PATH))
            die();
        switch ($act) {
            case "replace":
                $_POST['search'] = $POST['search'];
                $_POST['with'] = $POST['with'];
                $this->replace($name, $_POST);
                break;
            case "clone":
                $this->copy($name, $_POST['new']);
                break;
            case "delete":
                $this->delete($name);
                break;
            case "delete_file":
                $this->delete_file($name, $_POST['file']);
                break;
            case "default":
                $this->bydefault($name);
                break;
        }
        die('OK!');
    }

    /**
     * Замена в теме
     * @global search $search
     * @param string $name имя темы
     * @param array $data данные поиска
     * @return null
     */
    protected function replace($name, $data) {
        global $search;
        $cols = array(
            'what' => 'search',
            'with',
            'regexp',
            'files');
        extract(rex($data, $cols));
        if (!$what)
            return;
        $regexp = (bool) $regexp;
        $dir = THEMES_PATH . '/' . $name;
        if (!$files)
            $files = $dir;
        else {
            if (!is_array($files))
                $files = (array) $files;
            foreach ($files as $k => $v) {
                $v = validpath($v, false, styles_man::$spaths);
                $files[$k] = $dir . '/' . $v;
            }
        }
        $search->replace_infiles($with)->search_infiles($files, $what, $regexp);
        log_add('replaced_in_style', 'admin', $name);
    }

    /**
     * Удаление файла/папки
     * @global file $file
     * @param string $name имя темы
     * @param string $f2d имя файла/папки
     * @return null
     * @throws EngineException
     */
    protected function delete_file($name, $f2d) {
        global $file;
        $f2d = validpath($f2d, false, styles_man::$spaths);
        if (preg_match('/^\/*(' . implode('|', array_map('mpc', styles_man::$spaths)) . ')\/*$/siu', trim($f2d)))
            throw new EngineException;
        $file->unlink_folder(THEMES_PATH . '/' . $name . '/' . $f2d);
        log_add('deleted_style_file', 'admin', array($f2d, $name));
    }

    /**
     * Удаление темы
     * @global file $file
     * @global config $config
     * @param string $name имя темы
     * @return null
     */
    protected function delete($name) {
        global $file, $config;
        $rows = $file->open_folder(THEMES_PATH, true);
        if (count($rows) < 2)
            return;
        if ($config->v('default_style') == $name) {
            $i = array_search($name, $rows);
            unset($rows[$i]);
            $this->bydefault(reset($rows));
        }
        $file->unlink_folder(THEMES_PATH . '/' . $name);
        log_add('deleted_style', 'admin', $name);
    }

    /**
     * Клонирование темы
     * @global file $file
     * @global lang $lang
     * @param string $name имя темы
     * @param string $newname новое имя темы
     * @return null
     * @throws EngineException
     */
    protected function copy($name, $newname) {
        global $file, $lang;
        $lang->get('admin/styles');
        if (!validword($newname))
            throw new EngineException('styles_invalid_new_name');
        $file->copy_folder(THEMES_PATH . '/' . $name, THEMES_PATH . '/' . $newname);
        log_add('copied_style', 'admin', array($newname, $name));
    }

    /**
     * Присвоение темы по-умолчанию
     * @global config $config
     * @param string $name имя темы
     * @return null
     */
    protected function bydefault($name) {
        global $config;
        if ($config->v('default_style') == $name)
            return;
        $config->set('default_style', $name);
        log_add('changed_config', 'admin');
    }

}

?>