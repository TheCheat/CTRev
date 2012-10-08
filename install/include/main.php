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
     * @var array $chmod
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
     * @const need_filesize необходимый объём загружаемых файлов(в МБ.)
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
     * @param int $offset позиция, где заканчивается последний запрос
     * @return null 
     */
    protected function run_query($offset = 0) {
        db::o()->connect();
        db::o()->no_error();
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
            db::o()->query($query);
            preg_match('/((?:CREATE|ALTER)\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|UPDATE|(?:REPLACE|INSERT)(?:\s+IGNORE)?(?:\s+INTO)?)\s+`?(\w+)`?/is', $query, $matches);
            $qtype = strtolower($matches[1][0]);
            $qtype = 'install_import_query_type' . $qtype;
            if (lang::o()->visset($qtype)) {
                $table = strtolower($matches[2]);
                if (db::o()->errno())
                    $status = sprintf(lang::o()->v('install_import_query_error'), db::o()->errno());
                else
                    $status = lang::o()->v('install_import_query_success');
                printf(lang::o()->v('install_import_query'), lang::o()->v($qtype), $table, $status);
            }
        }
        print('<script type="text/javascript">
            run_query(' . $offset . ');
            </script>');
    }

    /**
     * Перезаписываем?
     * @param int|bool $c ответ функции
     * @return string текст
     */
    public function rewritable($c) {
        if ($c === 2 || $c === true)
            $s = lang::o()->v('install_check_writable_yes');
        elseif ($c === 1)
            $s = lang::o()->v('install_check_writable_part');
        else
            $s = lang::o()->v('install_check_writable_no');
        return $s;
    }

    /**
     * Вывод "цветного" текста
     * @param bool $cond условие
     * @param string $ytext текст при верном условии
     * @param string $ntext текст при неверном условии
     * @return string "окращенный" текст
     */
    public function colored($cond, $ytext = null, $ntext = null) {
        if (!$ytext) {
            $ytext = lang::o()->v('yes_simple');
            $ntext = lang::o()->v('no_simple');
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
     * @param string $dir проверяемая дирректория
     * @return int 2 - полностью перезаписываемы, 1 - частично, 0 - не перезаписываемы
     */
    public function check_dir($dir = 'themes') {
        $f = file::o()->open_folder($dir, true);
        $n = !$y = true;
        foreach ($f as $d) {
            $sd = '';
            if ($dir == 'themes')
                $sd = '/templates';
            $r = file::o()->is_writable($dir . '/' . $d . $sd, true, true);
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
        $f = lang::o()->v('install_license');
        if (!$f || !validword($f))
            $f = 'LICENSE_EN';
        tpl::o()->assign('license', file_get_contents(ROOT . $f));
        tpl::o()->display('license');
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
     * @return null 
     */
    protected function show_check() {
        tpl::o()->assign('file', file::o());
        $ufs = ini_get('upload_max_filesize');
        $pms = ini_get('post_max_size');
        tpl::o()->assign('maxfilesize', $this->check_filesize($ufs, $pms) ? $pms : $ufs);
        tpl::o()->assign('needfilesize', self::need_filesize);
        //tpl::o()->assign('dbversion', db::o()->version());
        tpl::o()->assign('chmod', $this->chmod);
        tpl::o()->assign('this', $this);
        tpl::o()->display('check');
    }

    /**
     * Отображение настройки коннекта к БД
     * @return null 
     */
    protected function show_database() {
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
        tpl::o()->assign('dbhost', $dbhost);
        tpl::o()->assign('dbuser', $dbuser);
        tpl::o()->assign('dbpass', $dbpass);
        tpl::o()->assign('dbname', $dbname);
        tpl::o()->assign('charset', $charset);
        tpl::o()->display('database');
    }

    /**
     * Отображение импорта данных в БД
     * @return null 
     */
    protected function show_import() {
        tpl::o()->display('import');
    }

    /**
     * Отображение панели создания администратора
     * @return null 
     */
    protected function show_admin() {
        tpl::o()->display('admin');
    }

    /**
     * Отображение конфигурации сайта
     * @global string $PREBASEURL
     * @return null 
     */
    protected function show_config() {
        global $PREBASEURL;
        init_baseurl();
        $PREBASEURL = $PREBASEURL == "/" ? "/" : rtrim($PREBASEURL, '/');
        tpl::o()->assign('baseurl', $PREBASEURL);
        tpl::o()->display('config');
    }

    /**
     * Отображение последней стадии установки
     * @return null 
     */
    protected function show_finish() {
        file::o()->write_file('1', ILOCK_FILE);
        tpl::o()->display('finish');
    }

    /**
     * Запись данных в БД
     * @param array $data массив данных
     * @return bool true, если успешно записано
     */
    protected function write_db($data) {
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
        return file::o()->write_file($contents, $f);
    }

    /**
     * Проверка импорта дампа в БД
     * @param array $error массив ошибок
     * @return null
     */
    protected function import_db(&$error) {
        $dump = $this->get_dump();
        $c = preg_match_all('/(?:^|\n)CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?(\w+)`?/s', $dump, $matches);
        for ($i = 0; $i < $c; $i++) {
            $table = $matches[1][$i];
            $r = db::o()->query('SHOW TABLES LIKE ' . db::o()->esc($table));
            if (!db::o()->num_rows($r))
                $error[] = sprintf(lang::o()->v('install_error_table_non_exists'), $table);
        }
    }

    /**
     * Создание администратора
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @return null
     */
    protected function create_admin($data, &$error) {
        $group = 6; // группа админа
        extract(rex($data, array('username',
                    'password',
                    'passagain',
                    'email')));
        include_once ROOT . 'include/classes/class.users.php';
        if ($password != $passagain)
            $error[] = lang::o()->v('install_error_passwords_not_match');
        if (!users::o()->check_login($username))
            $error[] = lang::o()->v('install_error_wrong_username');
        if (!users::o()->check_password($password))
            $error[] = lang::o()->v('install_error_wrong_password');
        $tmp = false; // reference
        if (!users::o()->check_email($email, $tmp))
            $error[] = lang::o()->v('install_error_wrong_email');
        if ($error)
            return;
        db::o()->truncate_table('users');
        $salt = users::o()->generate_salt();
        $salt2 = users::o()->generate_salt();
        $passhash = users::o()->generate_pwd_hash($password, $salt);
        $insert = array('username' => $username,
            'username_lower' => mb_strtolower($username),
            'confirmed' => 3,
            'email' => $email,
            'password' => $passhash,
            'salt' => $salt,
            'registered' => time(),
            'group' => $group,
            'passkey' => $salt2);
        users::o()->write_cookies($username, $passhash);
        db::o()->insert($insert, "users");
    }

    /**
     * Настройка сайта
     * @param array $data массив данных
     * @param array $error массив ошибок
     * @return null
     */
    protected function config($data, &$error) {
        include_once ROOT . 'include/classes/class.cache.php';
        include_once ROOT . 'include/classes/class.users.php';
        include_once ROOT . 'include/classes/class.config.php';
        cache::o()->clear();
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

        $upd['secret_key'] = users::o()->generate_salt();
        foreach ($upd as $k => $v)
            config::o()->set($k, $v);
    }

    /**
     * Проверка стадий
     * @param array $data массив данных
     * @return null
     */
    protected function check_steps($data) {
        $error = array();
        db::o()->no_reset()->no_error();
        switch (INSTALL_PAGE) {
            case "license":
            case "finish":
                break;
            case "config":
                db::o()->connect();
                $this->config($data, $error);
                break;
            case "admin":
                db::o()->connect();
                $this->create_admin($data, $error);
                break;
            case "import":
                db::o()->connect();
                $this->import_db($error);
                break;
            case "database":
                if (!$this->write_db($data))
                    $error[] = lang::o()->v('install_error_cant_write_dbconn');
                db::o()->connect();
            case "check":
                foreach ($this->chmod as $f) {
                    $r = file::o()->is_writable($f, true, true, true);
                    if ($r !== 2 && $r !== true)
                        $error [] = sprintf(lang::o()->v('install_error_not_rewritable'), $f);
                }
                $ufs = ini_get('upload_max_filesize');
                $pms = ini_get('post_max_size');
                $s = $this->check_filesize($ufs, $pms) ? $pms : $ufs;
                if (!$this->check_filesize($s, self::need_filesize . 'M'))
                    $error[] = lang::o()->v('install_error_upload_filesize');
                if (!version_compare(PHP_VERSION, '5.0', '>='))
                    $error[] = lang::o()->v('install_error_php_version');
                if (!in_array('mbstring', get_loaded_extensions()))
                    $error[] = lang::o()->v('install_error_mbstring');
                break;
        }
        if ($error)
            die(implode('<br>', $error));
    }

}

?>