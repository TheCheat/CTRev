<?php

/**
 * Project:            	CTRev
 * File:                class.users.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Пользовательские функции
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class users_checker {

    /**
     * Данная группа юзера
     * @tutorial protected, ибо предполагается, что неизменно в процессе работы. 
     * Ибо это данные из БД и для изменения юзать соотв. функции {@link db}.
     * Если необходимо какое-то доп. поле, его можно получить через groups
     * @var array
     */
    protected $perms = array();

    /**
     * Массив данных данного юзера
     * @tutorial protected, ибо предполагается, что неизменно в процессе работы. 
     * Ибо это данные из БД и для изменения юзать соотв. функции {@link db}.
     * Если необходимо временно переопределить, юзаем {@see users::set_tmpvars()}
     * @var array
     */
    protected $vars = array();

    /**
     * Массив всех групп юзерей
     * @var array
     */
    protected $groups = array();

    /**
     * Режим администратора(проверка на права и formkey не действует)
     * @var bool
     */
    protected $admin_mode = false;

    /**
     * Столбцы для автоапдейта групп
     * В таблице groups и users должны иметь вид:
     * столбец_count
     * @var array
     */
    protected $update_columns = array("torrents", "karma", "bonus");

    /**
     * Целочисленные настройки пользователя
     * @var array
     */
    protected $isettings = array("icq", "country", "show_age");

    /**
     * Генерация хеша пароля
     * @param array $row массив пользовательских параметров
     * @return string пассхеш
     */
    public function generate_pwd_hash($password, $salt) {
        $pass_md5 = md5($password);
        return md5(substr($salt, 0, 31) . substr($pass_md5, 6, 5) . substr($pass_md5, 1, 31));
    }

    /**
     * Генерация "соли" пароля
     * @param int $length длина "соли"
     * @return string "соль"
     */
    public function generate_salt($length = 32) {
        $symbs = ($length != 6 ? "1" : "") . "23456789" . ($length != 6 ? "0" : "") . "qwertyu" . ($length != 6 ? "io" : "") . "pasdfghjklzxcvbnmQWERTYU" . ($length != 6 ? "IO" : "") . "PASDFGHJKLZXCVBNM";
        $salt = "";
        $length_symb = strlen($symbs) - 1;
        for ($i = 0; $i < $length; $i++) {
            $symb = rand(0, $length_symb);
            $salt .= $symbs [$symb];
        }
        return $salt;
    }

    /**
     * Вывод ошибки банов
     * @global lang $lang
     * @global display $display
     * @param array $r массив из запроса(причина и до..)
     * @param string $what тип бана
     * @return string ошибка
     */
    protected function bans_error($r, $what = "ip") {
        global $lang, $display;
        $lang->get('admin/bans');
        $r = $lang->v("bans_this_" . $what . "_banned", true);
        $r .= ($r['to_time'] ? $lang->v('bans_until') . $display->date($r['to_time'], "ymd") : $lang->v('bans_forever'));
        $r .= $lang->v('bans_dot') . ($r["reason"] ? $lang->v('bans_reason') . $r['reason'] : "");
        return $r;
    }

    /**
     * Включить/Выключить режим администратора(проверка на права и formkey не действует)
     * @param bool $check проверять, был ли установлен режим? (только включает)
     * тогда возвращает true, иначе - false.
     * @return bool|users $this
     */
    public function admin_mode($check = false) {
        $am = $this->check_adminmode();
        if (!$check || !$am)
            $this->admin_mode = !$this->admin_mode;
        if ($check)
            return $am;
        return $this;
    }

    /**
     * Проверка режима администратора
     * @return bool режим администратора?
     */
    public function check_adminmode() {
        return $this->admin_mode;
    }

    /**
     * Преобразовываем в массив разрешённые модули АЦ юзера
     * @param array $row массив прав
     * @return null
     */
    public function acp_modules(&$row = null) {
        if (!$row)
            $row = &$this->perms;
        if (!is_array($row['acp_modules']))
            $row['acp_modules'] = explode(';', $row['acp_modules']);
    }

    /**
     * Форма логина в АЦ
     * @global tpl $tpl 
     * @global lang $lang
     * @param string $afile файл АЦ
     * @return null
     */
    protected function acp_login($afile) {
        global $tpl, $lang;
        $lang->get('admin/login');
        $login = $_POST['login'];
        $password = $_POST['password'];
        if ($login && $password) {
            if (mb_strtolower($login) != $this->vars['username_lower'])
                $login = '';
            if (!$this->check_data($login, $password, $error)) {
                print($error);
            } else {
                $sid = get_formkey(null, 'sid');
                $_SESSION['sid'] = $sid; // Дабы не входить снова, если SID отсутствует
                print("OK!" . $afile . $sid);
            }
        } else
            $tpl->display('admin/login.tpl');
        die();
    }

    /**
     * Проверка "силы" пароля
     * @param string $string пароль
     * @param int $can_length число символов, при которых пароль считается > low
     * @return int 3 - очень сильный, 2 - сильный, 1 - сойдёт, 0 - уныл
     */
    public function check_password_strength($string, $can_length = 6) {
        $numbers = '0123456789';
        $special = '`~!@#$%^&*()_=-+"№;:?/,.<>:\'';
        $be_num = 0;
        if (mb_strlen($string) >= $can_length && preg_match('/\w+/siu', $string))
            for ($i = 0; $i < mb_strlen($string); $i++) {
                if (mb_strpos($special, s($string, $i)) !== false && $be_num)
                    return 3;
                if (mb_strpos($special, s($string, $i)) !== false && !$be_num) {
                    for ($ii = 0; $ii < mb_strlen($numbers); $ii++) {
                        if (mb_strpos($string, s($numbers, $ii)) !== false)
                            return 3;
                    }
                    return 2;
                }
                if (mb_strpos($numbers, s($string, $i)) !== false)
                    $be_num = 1;
            }
        if ($be_num)
            return 1;
        return 0;
    }

    /**
     * Проверка сессии в АЦ
     * @global string $eadmin_file 
     * @global furl $furl
     * @global tpl $tpl
     * @global string $BASEURL
     * @param string $module имя модуля
     * @param bool $onlylink только получение ссылки?(тобишь без переадресаций)
     * @param bool $hardmode для АЦ, к примеру. Проверяет SID, даже если отсутствует
     * @return bool в АЦ?
     */
    public function check_inadmin($module, $onlylink = false, $hardmode = false) {
        global $eadmin_file, $furl, $tpl, $BASEURL;
        if ($hardmode)
            $onlylink = false;
        elseif (!$this->perms['can_acp'] || (!$_REQUEST['sid'] && !$onlylink))
            return false;
        $sid = get_formkey(null, "sid");
        if ($_SESSION['sid'] != $sid)
            $_SESSION['sid'] = '';
        if (!$_REQUEST['sid'] && $_SESSION['sid'])  // Дабы не входить снова, если SID отсутствует
            $sid = $_SESSION['sid']; // Но переадресация нужна
        $afile = $BASEURL . "admincp.php?sid=";
        $eadmin_file = $afile . $sid;
        if (!$onlylink) {
            try {
                check_formkey("sid");
            } catch (EngineException $e) {
                if ($hardmode && (!$_SESSION['sid'] || $_REQUEST['sid']))
                    $this->acp_login($afile);
                $furl->location($eadmin_file);
                die(); // Die Dead Enough
            }
        }
        $this->acp_modules();
        if ($module && $this->perms['can_acp'] == 1)
            if (!in_array($module, (array) $this->perms['acp_modules'])) {
                if (!$onlylink)
                    $furl->location($eadmin_file);
                else
                    return false;
            }
        $tpl->assign("admin_sid", 'sid=' . $sid);
        $tpl->assign("eadmin_file", $eadmin_file);
        return true;
    }

    /**
     * Проверка на верный IP
     * @param int $ip IP пользователя
     * @return bool верный IP?
     */
    public function validip($ip) {
        return $ip && (validid($ip) ? $ip === ip2ulong(long2ip($ip)) : $ip === long2ip(ip2ulong($ip)));
    }

    /**
     * Проверка на существование группы
     * @param int $group ID группы
     * @return bool true, если существует
     */
    public function validgroup($group) {
        return (bool) $this->groups [$group];
    }

    /**
     * Проверка правильноти E-mail
     * @global db $db
     * @global config $config
     * @param string $email E-mail для проверки
     * @param boolean|string $with_bans с банами? тогда данный аргумент примет значение строки с ошибкой
     * @return bool статус проверки, false - не соотв. рег. выражению или забаннен
     */
    public function check_email($email, &$with_bans = true) {
        global $db, $config;
        if (!preg_match('/^([a-zA-Z0-9\_\-\.\%]+)\@([a-zA-Z0-9\_\-\.]+)\.([a-zA-Z]){2,4}$/siu', $email)) {
            $with_bans = null;
            return false;
        } elseif ($config && $config->v('check_mx_email')) {
            $email_arr = explode('@', $email);
            $host = $email_arr [1];
            $f = @fsockopen($host, 25, $errno, $errstr, 30);
            fclose($f);
            if (!$f) {
                $with_bans = null;
                return false;
            }
        }
        if ($with_bans) {
            $r = $db->query('SELECT to_time, reason FROM bans WHERE ' . $db->esc($email) .
                    ' LIKE REPLACE("*", "%", REPLACE("%", "\\%", email)) AND email<>""
                AND (to_time >= ' . time() . ' OR to_time=0)', 1);
            if ($r) {
                $r = $db->fetch_assoc($r);
                $with_bans = $this->bans_error($r, "email");
                return false;
            }
        }
        $with_bans = null;
        return true;
    }

    /**
     * Проверка правильности логина
     * @param string $login логин для проверки
     * @return bool статус проверки
     */
    public function check_login($login) {
        return !(mb_strlen($login) < 2 || mb_strlen($login) > 25) && preg_match('/^[a-zA-Zа-яА-Я0-9\-\_]+$/siu', $login);
    }

    /**
     * Проверка правильности пароля
     * @param string $password пароль для проверки
     * @return bool статус проверки
     */
    public function check_password($password) {
        return !(mb_strlen($password) < 6 || mb_strlen($password) > 15);
    }

    /**
     * Функция проверки прав пользователей, в случае отсутствия прав - посылает на страницу логина
     * @global furl $furl
     * @param string $rule право пользователя(!без префикса can_!)
     * @param int $value значение права, от {@link $value} и выше.
     * @param int $def 2 - все
     * 1 - все, кроме гостей
     * 0 - все, кроме гостей и пользователей по-умолчанию
     * @return null
     */
    public function check_perms($rule = '', $value = 1, $def = 1) {
        global $furl;
        if ($this->check_adminmode())
            return;
        $default = $this->perms ['guest'] || $this->perms ['bot'] ? 2 : $this->perms['default'];
        $if = false;
        if ($rule) {
            $rule = $this->perms ['can_' . $rule];
            $if = ((int) $rule) < ((int) $value);
        }
        $if = $if || $default > $def;
        if ($if) {
            $furl->location($furl->construct("login", array(
                        "ref" => $_SERVER ['REQUEST_URI'])));
            die();
        }
    }

    /**
     * Проверка права
     * @param string $rule право пользователя(!без префикса can_!) или параметры system, pm_count, acp_modules
     * @param int $value проверяемое значение
     * @return bool|mixed true, если значение больше или равно {@link $value} или значение параметра
     */
    public function perm($rule, $value = null) {
        $nocan = false;
        $ret = false;
        switch ($rule) {
            case "pm_count":
            case "acp_modules":
                $ret = true;
            case "system":
                $nocan = true;
                break;
        }
        if ($this->check_adminmode() && !$ret)
            return true;
        $rule = (!$nocan ? "can_" : "") . $rule;
        if (!$value)
            $value = 1;
        if (!$ret)
            return $this->perms[$rule] >= $value;
        return $this->perms[$rule];
    }

    /**
     * Проверка правильности введённого логина и пароля
     * @global db $db
     * @global lang $lang
     * @global etc $etc
     * @global plugins $plugins
     * @param string $login логин
     * @param string $password пароль
     * @param int $id ID пользователя
     * @param string $error ошибка при выполнении, если имеется
     * @return string пассхеш, в случае успешного завершения
     */
    public function check_data($login, $password, &$error = "", &$id = 0) {
        global $db, $lang, $etc, $plugins;
        if (!$login || !$password) {
            $error = ($lang->v('login_false_signin'));
            return false;
        }

        // проще переопределением, но добавлю хук, в кач. исключения.
        try {
            $plugins->pass_data(array('login' => $login,
                'password' => $password,
                'error' => &$error,
                'id' => &$id), true)->run_hook('users_check_data');
        } catch (PReturn $e) {
            return $e->r();
        }

        $row = $etc->select_user(null, $login, 'id,password,salt');
        if (!$row ['id'] || $row ['password'] != $this->generate_pwd_hash($password, $row ['salt'])) {
            $error = ($lang->v('login_false_signin'));
            return false;
        }
        //if ($row ['confirmed'] != 3) {
        //	$error = ( $lang->v('login_not_confirmed_account') );
        //}
        $id = $row['id'];
        if (mb_strlen($row ['salt']) != 32) {
            $salt = $this->generate_salt(32);
            $password = $this->generate_pwd_hash($password, $salt);
            $db->update(array('salt' => $salt,
                'password' => $password), 'users', 'WHERE id = ' . $row ['id'] . ' LIMIT 1');
        } else
            $password = $row ['password'];
        return $password;
    }

}

class users_getter extends users_checker {

    /**
     * Группа пользователя по-умолчанию
     * @var int
     */
    protected $def_group = 0;

    /**
     * Группа гостя по-умолчанию
     * @var int
     */
    protected $guest_group = 0;

    /**
     * Группа заблокированных
     * @const int $banned_group
     */

    const banned_group = -1;

    /**
     * Язык данного юзера
     * @var string
     */
    protected $lang = "";

    /**
     * Тема данного юзера
     * @var string
     */
    protected $theme = "";

    /**
     * Поиск группы
     * @param string $column искомый столбец
     * @return int ID группы
     */
    public function find_group($column) {
        switch ($column) {
            case 'default':
                if ($this->def_group)
                    return $this->def_group;
                break;
            case 'guest':
                if ($this->guest_group)
                    return $this->guest_group;
                break;
        }
        foreach ($this->groups as $id => $group)
            if ($group[$column]) {
                switch ($column) {
                    case 'default':
                        $this->def_group = $id;
                        break;
                    case 'guest':
                        $this->guest_group = $id;
                        break;
                }
                return $id;
            }
    }

    /**
     * Получение значения пользовательской переменной
     * @param string $var имя переменной
     * если отсутствует, то функция возвращает, true, когда пользователь опознан
     * @return mixed значение 
     */
    public function v($var = null) {
        return !$var ? (bool) $this->vars : $this->vars[$var];
    }

    /**
     * Получение IP пользователя
     * @param bool $longed в ip2ulong
     * @return string|int IP в формате целого числа, либо 4 чисел с точками
     */
    public function get_ip($longed = true) {
        $ip = ($_SERVER ['REMOTE_ADDR'] == "::1" ? "127.0.0.1" : $_SERVER ['REMOTE_ADDR']);
        return ($longed ? ip2ulong($ip) : $ip);
    }

    /**
     * Получение имени группы
     * @global lang $lang
     * @param int $group ID группы
     * @return string имя группы
     */
    public function get_group_name($group) {
        global $lang;
        return $lang->if_exists($group == self::banned_group ? "group_banned" : $this->groups [$group] ['name']);
    }

    /**
     * Получение группы с ID {@link $id}
     * @param int $id ID группы
     * @return array массив группы
     */
    public function get_group($id = null) {
        return $id ? $this->groups[$id] : $this->groups;
    }

    /**
     * Получение цвета группы
     * @param int $group ID группы
     * @return string цвет группы
     */
    public function get_group_color($group) {
        return $group == self::banned_group ? "black" : $this->groups [$group] ['color'];
    }

    /**
     * Получение языка пользователя
     * @return string имя языка
     */
    public function get_lang() {
        return $this->lang;
    }

    /**
     * Получение темы пользователя
     * @return string имя темы
     */
    public function get_theme() {
        return $this->theme;
    }

}

class users_modifier extends users_getter {

    /**
     * Временные переменные пользователя
     * @var array
     */
    protected $tmp_vars = null;

    /**
     * Декодирование строки с правами типа ID1:значение1,ID2:значение2,...
     * @param string $perms строка с правами
     * @param array $r массив, куда пишем
     * @return array декодированная строка
     */
    protected function decode_perms($perms, &$r = null) {
        $perms = explode(";", $perms);
        $c = count($perms);
        for ($i = 0; $i < $c; $i++) {
            list($id, $value) = explode(":", $perms[$i]);
            $r[$this->pid[$id]] = (int) $value;
        }
        return $r;
    }

    /**
     * Присвоение дополнительных прав юзера
     * @param array $group массив прав пользователя
     * @param array $user массив переменных пользователя
     * @return null
     */
    public function alter_perms(&$group = array(), $user = array()) {
        if (!$user)
            $user = $this->vars;
        if (!$group)
            $group = &$this->perms;
        if ($user ['add_permissions']) {
            $rules = $this->decode_perms($user ['add_permissions']);
            if ($rules)
                $group = @array_merge($group, $rules);
        }
    }

    /**
     * Декодирование настроек пользователя
     * @param array $uservars параметры пользователя
     * @param string $what декодируемый столбец
     * @return array декодированные параметры пользователя
     */
    public function decode_settings($uservars = null, $what = "settings") {
        if (!$uservars) {
            $this->vars = $this->decode_settings($this->vars, $what);
            return $this->vars;
        }
        $t = unserialize($uservars[$what]);
        unset($uservars[$what]);
        if (!is_array($t))
            return $uservars;
        return array_merge($uservars, $t);
    }

    /**
     * Десериализация поля пользователя
     * @param string $var имя поля
     * @return mixed значение
     */
    public function unserialize($var) {
        if (!$this->vars[$var])
            return;
        $this->vars[$var] = unserialize($this->vars[$var]);
        return $this->vars[$var];
    }

    /**
     * Установка временных переменных пользователя
     * !Использовать только в случае крайней необходимости, ибо:
     * подразумевается, что изменение переменных не нужно.
     * @param array $what массив переменны
     * @return users_modifier $this
     */
    public function set_tmpvars($what) {
        $this->tmp_vars = $this->vars;
        $this->vars = $what;
        return $this;
    }

    /**
     * Удаление временных переменных пользователя
     * @return users_modifier $this
     */
    public function remove_tmpvars() {
        if (!$this->tmp_vars)
            return;
        $this->vars = $this->tmp_vars;
        return $this;
    }

    /**
     * Генерация пассхеша, для привязки к IP
     * @global config $config
     * @param string $real_hash passhash из БД
     * @param bool $short_sess короткая сессия
     * @return string сгенерированный пассхеш
     */
    public function passhash_real($real_hash, $short_sess = false) {
        global $config;
        if ($short_sess)
            $real_hash = md5($real_hash . session_id());
        if (!$config || $config->v('ip_binding'))
            return md5(md5($real_hash) . $this->get_ip(false));
        else
            return $real_hash;
    }

    /**
     * Сериализация массива с настройками пользователя
     * @param array $settings массив настроек(show_age, website, icq,
     *      skype, country, town, name_surname)
     * @return string сериализованные настройки
     */
    public function make_settings($settings) {
        if (is_array($settings))
            return serialize($settings);
        return "";
    }

}

class users extends users_modifier {
    /**
     * Префикс для бота
     * @const string bot_prefix 
     */

    const bot_prefix = '[BOT]';

    /**
     * Пара ID=>право
     * @var array
     */
    protected $pid = array();

    /**
     * Извлечение массива групп из БД
     * @global db $db
     * @global cache $cache 
     * @return null
     */
    protected function get_groups() {
        global $db, $cache;
        if (!($a = $cache->read('groups'))) {
            $gp = $db->query("SELECT id, perm, dvalue FROM groups_perm");
            while ($row = $db->fetch_assoc($gp)) {
                $row["perm"] = "can_" . $row["perm"];
                $this->pid[$row["id"]] = $row["perm"];
                $dperms[$row["perm"]] = $row["dvalue"];
            }
            $gr = $db->query("SELECT * FROM groups");
            while ($group = $db->fetch_assoc($gr)) {
                $id = $group["id"];
                $perms = $group["perms"];
                unset($group["perms"]);
                $group = array_merge($dperms, $group);
                if ($perms)
                    $this->decode_perms($perms, $group);
                $this->groups[$id] = $group;
            }
            $cache->write(array($this->groups, $this->pid));
        } else
            list($this->groups, $this->pid) = $a;
        $this->def_group = $this->find_group('default');
    }

    /**
     * "Поимка" бота
     * @global config $config
     * @global db $db
     * @return bool поймали?
     */
    protected function catch_bot() {
        global $config, $db;
        if (!$config->v('use_bots'))
            return false;
        $ip = $this->get_ip();
        $agent = $db->esc($_SERVER ['HTTP_USER_AGENT']);
        $r = $db->query("SELECT id, name FROM bots WHERE firstip<=" . $ip . " AND lastip>=" . $ip . "
                OR " . $agent . " LIKE CONCAT('%', agent, '%') LIMIT 1");
        list($id, $name) = $db->fetch_row($r);
        if ($name) {
            $this->vars ['username'] = self::bot_prefix . $name;
            $bot = $this->find_group('bot');
            if (!$bot)
                $bot = $guest;
            if ($bot)
                $this->perms = $this->groups [$bot];
            $this->vars ['group'] = $bot;
            $this->vars ['id'] = $id;
            $this->vars ['bot'] = 1;
            return true;
        }
        return false;
    }

    /**
     * Инициализация юзера
     * @global config $config
     * @global db $db
     * @global lang $lang
     * @global etc $etc
     * @global display $display
     * @global plugins $plugins
     * @return null
     */
    public function init() {
        global $config, $db, $lang, $etc, $display, $plugins;

        $ban_allowed = defined("ALLOW_WITH_BAN"); // отключить баны?
        $display->site_autoon();
        $this->lang = validfolder($_COOKIE ["lang"], LANGUAGES_PATH) ? $_COOKIE ["lang"] : $config->v('default_lang');
        $this->theme = validfolder($_COOKIE ["theme"]) ? $_COOKIE ["theme"] : $config->v('default_style');
        init_spaths();
        $lang->change_folder($this->lang);
        $ip = $this->get_ip();

        if ($config->v('use_ipbans') && !$ban_allowed) {
            $res = $db->query("SELECT * FROM bans WHERE " . $ip . " >= ip_f AND " . $ip . " <= ip_t");
            if ($row = $db->fetch_assoc($res))
                error($this->bans_error($row));
        }

        $this->get_groups();

        // Для заливки аватары
        if (defined('ALLOW_REQUEST_COOKIES')) {
            $login = ($_COOKIE ["login"] ? $_COOKIE ["login"] : $_REQUEST ['login']);
            $password = ($_COOKIE ["pwd"] ? $_COOKIE ["pwd"] : $_REQUEST['pwd']);
            $short_sess = (bool) (isset($_COOKIE ['short_sess']) ? ($_COOKIE ['short_sess']) : ($_REQUEST ['short_sess']));
        } else {
            $login = $_COOKIE ["login"];
            $password = $_COOKIE ["pwd"];
            $short_sess = ((bool) $_COOKIE ['short_sess']);
        }
        try {

            $plugins->pass_data(array('login' => $login,
                'password' => $password), true)->run_hook('users_init_begin');

            if (!$login && !$password && $this->catch_bot())
                return;
            if (!$login || !$password)
                throw new EngineException;
            if (mb_strlen($password) != 32) {
                $this->clear_cookies();
                error('invalid_cookie');
            }
            $res = $db->query("SELECT u.*, b.reason FROM users AS u
                LEFT JOIN bans AS b ON b.uid=u.id
                WHERE u.username_lower = " . $db->esc(mb_strtolower($login)) . "
                AND u.confirmed = '3' GROUP BY u.id");
            $row = $db->fetch_assoc($res);
            $group = &$row ['group'];
            if ($group == users_getter::banned_group && $this->groups [$row ['old_group']] ['can_bebanned']) {
                if (!$ban_allowed)
                    error($this->bans_error($row, "user"));
                throw new EngineException;
            } elseif ($group == users_getter::banned_group) {
                $group = $row ['old_group'];
                $etc->unban_user($row['id']);
            }

            if (!$row || $password != $this->passhash_real($row ["password"], $short_sess))
                throw new EngineException;
            $row ['ip'] = $ip;
            if (!$this->groups [$group])
                error('group_doesnt_exists');

            $plugins->pass_data(array('row' => &$row))->run_hook('users_init_end');

            $this->perms = $this->groups [$group];
            $this->vars = $row;
            $this->alter_perms();
        } catch (EngineException $e) {
            $guest = $this->find_group('guest');
            if (!$guest)
                error('no_guest_group');
            $this->perms = $this->groups [$guest];
        } catch (PReturn $e) {
            return $e->r();
        }
    }

    /**
     * Инициализация сессии пользователя
     * @global db $db
     * @global cache $cache
     * @global config $config
     * @global plugins $plugins
     * @return null
     */
    public function write_session() {
        global $db, $cache, $config, $plugins;
        if (!$cache->query_delay('usessions', $config->v('delay_userupdates')))
            return;
        $ip = $this->get_ip();
        $url = $_SERVER['REQUEST_URI'];
        $ctime = time();
        $agent = $_SERVER ["HTTP_USER_AGENT"];
        if (!$this->vars) {
            $uid = 0;
            $userdata = '';
        } else {
            $uid = $this->vars ['id'];
            $userdata = array('username' => $this->vars['username'],
                'group' => $this->vars['group'],
                'hidden' => $this->vars['hidden'],
                'useragent' => $agent,
                'url' => $url);
            if ($this->vars ['bot']) {
                $userdata['bot'] = true;
                $this->vars = array();
            }
            $userdata = serialize($userdata);
        }
        //$past = time() - 300;
        $sid = session_id();
        $updateset = array(
            "sid" => $sid,
            "uid" => $uid,
            "userdata" => $userdata,
            "ip" => $ip,
            "time" => $ctime);
        $users_updateset = array("ip" => $ip, "last_visited" => time());

        try {
            $plugins->pass_data(array('update_sess' => &$updateset,
                'update_user' => &$users_updateset), true)->run_hook('users_sessions_init');
        } catch (PReturn $e) {
            return $e->r();
        }

        if ($this->vars)
            $db->update($users_updateset, "users", "WHERE id=" . $uid . ' LIMIT 1');
        $db->update($updateset, "sessions", "WHERE sid=" . $db->esc($sid) . " LIMIT 1");
        if ($db->affected_rows() < 1)
            $db->insert($updateset, "sessions");
    }

    /**
     * Функция, абослютно идентичная setcookie, но с другими предустановками
     * @param string $name имя кукисы
     * @param string $value значение кукисы
     * @param int $expire время истечения кукисы
     * @param string $path путь кукисы
     * @param string $domain домен кукисы
     * @param bool $secure защищена?
     * @param bool $httponly только HTTP протокол?
     * @return null
     */
    public function setcookie($name, $value = null, $expire = 0x7fffffff, $path = '/', $domain = null, $secure = null, $httponly = null) {
        @setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Функция записи кукисов пароля и имени юзера
     * @param string $login Имя юзера
     * @param string $password пароль пользователя
     * @param bool $short_session короткая сессия(в зависимости от установок php, def = 2 ч.)
     * @return null
     */
    public function write_cookies($login, $password, $short_session = false) {
        $c = ob_get_contents();
        @ob_clean();
        $this->setcookie('login', $login);
        $this->setcookie('pwd', $this->passhash_real($password, $short_session));
        if ($short_session)
            $this->setcookie('short_sess', true);
        else
            $this->setcookie('short_sess', false);
        print($c);
    }

    /**
     * Функция удаления кукисов пароля и ID юзера
     * @return null
     */
    public function clear_cookies() {
        $c = ob_get_contents();
        @ob_clean();
        $this->setcookie("login", "");
        $this->setcookie("pwd", "");
        $this->setcookie('short_sess', false);
        print($c);
    }

    /**
     * Автоапдейт групп
     * @global db $db
     * @global plugins $plugins
     * @return null
     */
    public function groups_autoupdate() {
        global $db, $plugins;
        $a = $k = array();

        // ради одного поля не стоит наследовать этот класс от pluginable_object
        try {
            $plugins->pass_data(array(
                'update_columns' => &$this->update_columns), true)->run_hook('users_groups_autoupdate');
        } catch (PReturn $e) {
            return $e->r();
        }

        $cols = $this->update_columns;
        $c = count($cols);
        $w = "";
        foreach ($this->groups as $id => $group) {
            $t = false;
            for ($i = 0; $i < $c; $i++) {
                $v = $group[$cols[$i] . "_count"];
                if ($v) {
                    if (!$a[$i] || !in_array($v, $a[$i]))
                        $a[$i][$k[$i]++] = $v;
                    $t = true;
                }
            }
            if ($t)
                $w .= ( $w ? ", " : "") . $id;
        }
        if (!$w)
            return;
        $w .= ( $w ? ", " : "") . $this->find_group('default');
        for ($i = 0; $i < $c; $i++)
            if ($a[$i])
                sort($a[$i]);
        foreach ($this->groups as $id => $group) {
            $where = "";
            for ($i = 0; $i < $c; $i++) {
                if (!$a[$i])
                    continue;
                $e = $cols[$i] . "_count";
                $v = $group[$e];
                if (($j = array_search($v, $a[$i])) !== false) {
                    $where .= ( $where ? " AND " : "") . "`" . $e . "`>=" . $v;
                    if ($a[$i][++$j])
                        $where .= " AND `" . $e . "`<" . $a[$i][$j];
                }
            }
            if (!$where)
                continue;
            $db->update(array("group" => $id), "users", "WHERE " . $where . " AND `group` IN (" . $w . ')');
        }
    }

}

?>