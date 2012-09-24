<?php

/**
 * Project:             CTRev
 * File:                main.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Инсталляция сайта. Основные действия
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class main {

    /**
     * Проверка CHMOD для
     * @var array
     */
    protected $chmod = array(
        'include/cache',
        'include/plugins/replaced',
        'upload/avatars',
        'upload/torrents',
        'include/system/aliases.php',
        'include/dbconn.php',
        'install/lock');

    /**
     * Необходимый объём загружаемых файлов(в МБ.)
     * @const int need_filesize
     */

    const need_filesize = 5;

    /**
     * Инициализация AJAX части инсталляции
     * @return null 
     */
    public function init() {
        if ($_GET['import']) {
            $this->run_query($_GET['offset']);
            die();
        }
        if ($_GET['check']) {
            $this->check_steps($_POST);
            die('OK!');
        } else {
            $f = "show_" . INSTALL_PAGE;
            $this->$f();
        }
    }

    /**
     * Получение дампа БД
     * @return string дамп
     */
    protected function get_dump() {
        return file_get_contents(ROOT . 'install/database/ctrev.sql');
    }

    /**
     * Выполнение одного запроса из дампа
     * @global db $db
     * @global lang $lang
     * @param int $offset позиция, где заканчивается последний запрос
     * @return null 
     */
    protected function run_query($offset = 0) {
        global $db, $lang;
        $db->connect();
        $db->no_error();
        $offset = (int) $offset;
        // файл не должен содержать комментариев
        $matches = array();
        $dump = $this->get_dump();
        if ($offset < strlen($dump)) {
            // При выполнении регулярки ниже у меня апач вешался на относительно большой строке(вставка config)
            // Sad, but true
            /* preg_match('/((?:[^\'";]+(?:([\'"]).*?(?<!\\\)\\2)?)+)(;)/s', $dump, $matches, PREG_OFFSET_CAPTURE, $offset);
             * $query = $matches[1][0];
             * $offset = $matches[3][1] + 1;
             */
            $dump = substr($dump, $offset);
            preg_match('/(?:^|\n)(.*?)(;)(?:\n|$)/s', $dump, $matches, PREG_OFFSET_CAPTURE);
            $query = $matches[1][0];
            $offset = $matches[2][1] + $offset + 1;
        }
        if (!$matches) {
            print('<script type="text/javascript">
            stop_loading();
            </script>');
            return;
        }
        $query = preg_replace('/^--.*$/m', '', $query);
        $query = trim($query);
        if ($query) {
            $db->query($query);
            preg_match('/((?:CREATE|ALTER)\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|UPDATE|(?:REPLACE|INSERT)(?:\s+IGNORE)?(?:\s+INTO)?)\s+`?(\w+)`?/is', $query, $matches);
            $qtype = strtolower($matches[1][0]);
            $qtype = 'install_import_query_type' . $qtype;
            if ($lang->visset($qtype)) {
                $table = strtolower($matches[2]);
                if ($db->errno())
                    $status = sprintf($lang->v('install_import_query_error'), $db->errno());
                else
                    $status = $lang->v('install_import_query_success');
                printf($lang->v('install_import_query'), $lang->v($qtype), $table, $status);
            }
        }
        print('<script type="text/javascript">
            run_query(' . $offset . ');
            </script>');
    }

    /**
     * Перезаписываем?
     * @global lang $lang
     * @param int|bool $c ответ функции
     * @return string текст
     */
    public function rewritable($c) {
        global $lang;
        if ($c === 2 || $c === true)
            $s = $lang->v('install_check_writable_yes');
        elseif ($c === 1)
            $s = $lang->v('install_check_writable_part');
        else
            $s = $lang->v('install_check_writable_no');
        return $s;
    }

    /**
     * Вывод "цветного" текста
     * @global lang $lang
     * @param bool $cond условие
     * @param string $ytext текст при верном условии
     * @param string $ntext текст при неверном условии
     * @return string "окращенный" текст
     */
    public function colored($cond, $ytext = null, $ntext = null) {
        global $lang;
        if (!$ytext) {
            $ytext = $lang->v('yes_simple');
            $ntext = $lang->v('no_simple');
        }
        if (!$ntext)
            $ntext = $ytext;
        if ($cond)
            return "<font color='green'>" . $ytext . "</font>";
        else
            return "<font color='red'>" . $ntext . '</font>';
    }

    /**
     * Проверка перезаписываемости шаблонов/языковых пакетов
     * @global file $file 
     * @param string $dir проверяемая дирректория
     * @return int 2 - полностью перезаписываемы, 1 - частично, 0 - не перезаписываемы
     */
    public function check_dir($dir = 'themes') {
        global $file;
        $f = $file->open_folder($dir, true);
        $n = !$y = true;
        foreach ($f as $d) {
            $sd = '';
            if ($dir == 'themes')
                $sd = '/templates';
            $r = $file->is_writable($dir . '/' . $d . $sd, true, true);
            $y = $y || $r === true || $r === 2;
            $n = $n || !$r || $r === 1;
        }
        return $y + !$n;
    }

    /**
     * Инициализация инсталляции
     * @return null 
     */
    protected function show_license() {
        global $tpl, $lang;
        $f = $lang->v('install_license');
        if (!$f || !validword($f))
            $f = 'LICENSE_EN';
        $tpl->assign('license', file_get_contents(ROOT . $f));
        $tpl->display('license');
    }

    /**
     * Конвертация объёма данных вида \d(K|M|G|T) в \d({@link $to}), 
     * если {@link $to} больше входного значения
     * @param string $v объём данных
     * @param string $to куда конвертируем
     * @return string значение
     */
    protected function convert_filesize($v, $to) {
        $l = strlen($v) - 1;
        $pv = $v[$l];
        if (!is_numeric($pv))
            $v = longval(substr($v, 0, $l));
        $a = array('K' => 1, 'M' => 2, 'G' => 3, 'T' => 4);
        $pv = (int) $a[strtoupper($pv)];
        $to = (int) $a[strtoupper($to)];
        if ($to >= $pv)
            return $v;
        return pow(1024, $pv - $to);
    }

    /**
     * Сравнение объёма данных вида \d(K|M|G)
     * @param string $v1 певрое значение
     * @param string $v2 второе значение
     * @return bool true, если {@link $v1} больше или равно {@link $v2}
     */
    public function check_filesize($v1, $v2) {
        $to1 = $v2[strlen($v2) - 1];
        $to2 = $v1[strlen($v1) - 1];
        $v1 = $this->convert_filesize($v1, $to1);
        $v2 = $this->convert_filesize($v2, $to2);
        return $v1 >= $v2;
    }

    /**
     * Отображение проверки на перезаписываемость
     * @global file $file
     * @global db $db
     * @global tpl $tpl
     * @return null 
     */
    protected function show_check() {
        global $file, $tpl, $db;
        $tpl->assign('file', $file);
        $ufs = ini_get('upload_max_filesize');
        $pms = ini_get('post_max_size');
        $tpl->assign('maxfilesize', $this->check_filesize($ufs, $pms) ? $pms : $ufs);
        $tpl->assign('needfilesize', self::need_filesize);
        //$tpl->assign('dbversion', $db->version());
        $tpl->assign('chmod', $this->chmod);
        $tpl->assign('this', $this);
        $tpl->display('check');
    }

    /**
     * Отображение настройки коннекта к БД
     * @global tpl $tpl
     * @return null 
     */
    protected function show_database() {
        global $tpl;
        $f = 'include/dbconn.php';
        if (file_exists($f))
            include_once ROOT . $f;
        if (!$dbhost)
            $dbhost = 'localhost:3306';
        if (!$dbuser)
            $dbuser = 'root';
        if (!$dbname)
            $dbname = 'ctrev';
        if (!$charset)
            $charset = 'utf8';
        $tpl->assign('dbhost', $dbhost);
        $tpl->assign('dbuser', $dbuser);
        $tpl->assign('dbpass', $dbpass);
        $tpl->assign('dbname', $dbname);
        $tpl->assign('charset', $charset);
        $tpl->display('database');
    }

    /**
     * Отображение импорта данных в БД
     * @global tpl $tpl
     * @return null 
     */
    protected function show_import() {
        global $tpl;
        $tpl->display('import');
    }

    /**
     * Отображение панели создания администратора
     * @global tpl $tpl
     * @return null 
     */
    protected function show_admin() {
        global $tpl;
        $tpl->display('admin');
    }

    /**
     * Отображение конфигурации сайта
     * @global tpl $tpl
     * @global string $PREBASEURL
     * @return null 
     */
    protected function show_config() {
        global $tpl, $PREBASEURL;
        init_baseurl();
        $PREBASEURL = $PREBASEURL == "/" ? "/" : rtrim($PREBASEURL, '/');
        $tpl->assign('baseurl', $PREBASEURL);
        $tpl->display('config');
    }

    /**
     * Отображение последней стадии установки
     * @global tpl $tpl
     * @global file $file
     * @return null 
     */
    protected function show_finish() {
        global $tpl, $file;
        $file->write_file('1', ILOCK_FILE);
        $tpl->display('finish');
    }

    /**
     * Запись данных в БД
     * @global file $file
     * @param array $data массив данных
     * @return bool true, если успешно записано
     */
    protected function write_db($data) {
        global $file;
        extract(rex($data, array('dbhost',
                    'dbuser',
                    'dbpass',
                    'dbname',
                    'charset')));
        $f = 'include/dbconn.php';
        if (!$dbuser && file_exists(ROOT . $f))
            return true;
        if (!$dbhost)
            $dbhost = 'localhost';
        if (!$charset)
            $charset = 'utf8';
        $contents = '<?php
$dbhost = ' . var_export($dbhost, true) . ';
$dbuser = ' . var_export($dbuser, true) . ';
$dbpass = ' . var_export($dbpass, true) . ';
$dbname = ' . var_export($dbname, true) . ';
$charset = ' . var_export($charset, true) . ';
?>';
        return $file->write_file($contents, $f);
    }

    /**
     * Проверка импорта дампа в БД
     * @global db $db
     * @global lang $lang
     * @param array $error массив ошибок
     * @return null
     */
    protected function import_db(&$error) {
        global $db, $lang;
        $dump = $this->get_dump();
        $c = preg_match_all('/(?:^|\n)CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?/s', $dump, $matches);
        for ($i = 0; $i < $c; $i++) {
            $table = $matches[1][$i];
            $r = $db->query('SHOW TABLES LIKE ' . $db->esc($table));
            if (!$db->num_rows($r))
                $error[] = sprintf($lang->v('install_error_table_non_exists'), $table);
        }
    }

    /**
     * Создание администратора
     * @global db $db
     * @global lang $lang
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @return null
     */
    protected function create_admin($data, &$error) {
        global $db, $lang;
        $group = 6; // группа админа
        extract(rex($data, array('username',
                    'password',
                    'passagain',
                    'email')));
        include_once ROOT . 'include/classes/class.users.php';
        $users = new users();
        if ($password != $passagain)
            $error[] = $lang->v('install_error_passwords_not_match');
        if (!$users->check_login($username))
            $error[] = $lang->v('install_error_wrong_username');
        if (!$users->check_password($password))
            $error[] = $lang->v('install_error_wrong_password');
        $tmp = false; // reference
        if (!$users->check_email($email, $tmp))
            $error[] = $lang->v('install_error_wrong_email');
        if ($error)
            return;
        $db->truncate_table('users');
        $salt = $users->generate_salt();
        $salt2 = $users->generate_salt();
        $passhash = $users->generate_pwd_hash($password, $salt);
        $insert = array('username' => $username,
            'username_lower' => mb_strtolower($username),
            'confirmed' => 3,
            'email' => $email,
            'password' => $passhash,
            'salt' => $salt,
            'registered' => time(),
            'group' => $group,
            'passkey' => $salt2);
        $users->write_cookies($username, $passhash);
        $db->insert($insert, "users");
    }

    /**
     * Настройка сайта
     * @global cache $cache
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @return null
     */
    protected function config($data, &$error) {
        global $cache;
        include_once ROOT . 'include/classes/class.cache.php';
        $cache = new cache();
        include_once ROOT . 'include/classes/class.users.php';
        $users = new users();
        include_once ROOT . 'include/classes/class.config.php';
        $config = new config();
        $params = array('site_title',
            'baseurl',
            'contact_email',
            'furl',
            'cache_on');
        $upd = rex($data, $params);

        // предустановка параметров, если не заданы
        if (!$upd['baseurl'])
            $upd['baseurl'] = preg_replace('/^(.*)(\/|\\\)(.*?)$/siu', '\1', $_SERVER['PHP_SELF']);
        if (!$upd['contact_email'])
            $upd['contact_email'] = 'admin@' . $_SERVER['SERVER_NAME'];
        if (!isset($data['furl']))
            $upd['furl'] = (bool) $_SERVER['HTTP_FURL_AVALIABLE'];
        else
            $upd['furl'] = (bool) $upd['furl'];
        if (!isset($data['cache_on']))
            $upd['cache_on'] = true;
        else
            $upd['cache_on'] = (bool) $upd['cache_on'];

        $upd['secret_key'] = $users->generate_salt();
        foreach ($upd as $k => $v)
            $config->set($k, $v);
    }

    /**
     * Проверка стадий
     * @global db $db
     * @global lang $lang
     * @global file $file
     * @param array $data массив данных
     * @return null
     */
    protected function check_steps($data) {
        global $db, $lang, $file;
        $error = array();
        $db->no_reset()->no_error();
        switch (INSTALL_PAGE) {
            case "license":
            case "finish":
                break;
            case "config":
                $db->connect();
                $this->config($data, $error);
                break;
            case "admin":
                $db->connect();
                $this->create_admin($data, $error);
                break;
            case "import":
                $db->connect();
                $this->import_db($error);
                break;
            case "database":
                if (!$this->write_db($data))
                    $error[] = $lang->v('install_error_cant_write_dbconn');
                $db->connect();
            case "check":
                foreach ($this->chmod as $f) {
                    $r = $file->is_writable($f, true, true, true);
                    if ($r !== 2 && $r !== true)
                        $error [] = sprintf($lang->v('install_error_not_rewritable'), $f);
                }
                $ufs = ini_get('upload_max_filesize');
                $pms = ini_get('post_max_size');
                $s = $this->check_filesize($ufs, $pms) ? $pms : $ufs;
                if (!$this->check_filesize($s, self::need_filesize . 'M'))
                    $error[] = $lang->v('install_error_upload_filesize');
                if (!version_compare(PHP_VERSION, '5.0', '>='))
                    $error[] = $lang->v('install_error_php_version');
                if (!in_array('mbstring', get_loaded_extensions()))
                    $error[] = $lang->v('install_error_mbstring');
                break;
        }
        if ($error)
            die(implode('<br>', $error));
    }

}

?>