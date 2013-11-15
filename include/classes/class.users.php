<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.users.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var array $perms
     * @note protected, ибо предполагается, что неизменно в процессе работы. 
     * Ибо это данные из БД и для изменения юзать соотв. функции {@link db}.
     * Если необходимо какое-то доп. поле, его можно получить через groups
     * Если необходимо временно переопределить, юзаем set_tmpperms
     * @see set_tmpperms()
     * @see remove_tmpperms()
     */
    protected $perms = array();

    /**
     * Массив данных данного юзера
     * @var array $vars
     * @note protected, ибо предполагается, что неизменно в процессе работы. 
     * Ибо это данные из БД и для изменения юзать соотв. функции {@link db}.
     * Если необходимо временно переопределить, юзаем set_tmpvars
     * @see set_tmpvars()
     * @see remove_tmpvars()
     */
    protected $vars = array();

    /**
     * Массив всех групп юзерей
     * @var array $groups
     */
    protected $groups = array();

    /**
     * Режим администратора(проверка на права и formkey не действует)
     * @var bool $admin_mode
     */
    protected $admin_mode = false;

    /**
     * Столбцы для автоапдейта групп
     * В таблице groups и users должны иметь вид:
     * столбец_count
     * @var array $update_columns
     */
    protected $update_columns = array("content", "karma", "bonus");

    /**
     * "Права" пользователя, возвращающие значения(без префикса can_)
     * @var array $retperms
     */
    protected $retperms = array("pm_count", "acp_modules");

    /**
     * Исключение вместо переадресации?
     * @var int $perm_exception
     */
    protected $perm_exception = false;

    /**
     * Генерация хеша пароля
     * @param string $password пароль пользователя
     * @param string $salt соль пользователя
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
     * @param array $r массив из запроса(причина и до..)
     * @param string $what тип бана
     * @return string ошибка
     */
    protected function bans_error($r, $what = "ip") {
        lang::o()->get('admin/bans');
        $ret = lang::o()->v("bans_this_" . $what . "_banned", true);
        $ret .= ($r['to_time'] ? lang::o()->v('bans_until') . display::o()->date($r['to_time'], "ymd") : lang::o()->v('bans_forever'));
        $ret .= lang::o()->v('bans_dot') . ($r["reason"] ? lang::o()->v('bans_reason') . $r['reason'] : "");
        return $ret;
    }

    /**
     * Включить/Выключить режим администратора(проверка на права и formkey не действует)
     * @param bool $state включить/выключить
     * @return users $this
     */
    public function admin_mode($state = true) {
        $this->admin_mode = (bool) $state;
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
     * @param string $afile файл АЦ
     * @return null
     */
    protected function acp_login($afile) {
        lang::o()->get('admin/login');
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
                ok(true);
                print($afile . $sid);
            }
        }
        else
            tpl::o()->display('admin/login.tpl');
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
     * @param string $module имя модуля
     * @param bool $onlylink только получение ссылки?(тобишь без переадресаций)
     * @param bool $hardmode для АЦ, к примеру. Проверяет SID, даже если отсутствует
     * @return bool в АЦ?
     */
    public function check_inadmin($module, $onlylink = false, $hardmode = false) {
        $baseurl = globals::g('baseurl');

        if ($hardmode)
            $onlylink = false;
        elseif (!$this->perms['can_acp'] || (!$_REQUEST['sid'] && !$onlylink))
            return false;

        $sid = get_formkey(null, "sid");

        if ($_SESSION['sid'] != $sid)
            $_SESSION['sid'] = '';
        if (!$_REQUEST['sid'] && $_SESSION['sid'])  // Дабы не входить снова, если SID отсутствует
            $sid = $_SESSION['sid']; // Но переадресация нужна

        $afile = $baseurl . "admincp.php?sid=";
        $eadmin_file = $afile . $sid;
        globals::s('eadmin_file', $eadmin_file);

        if (!$onlylink) {
            try {
                check_formkey("sid");
            } catch (EngineException $e) {
                if ($hardmode && (!$_SESSION['sid'] || $_REQUEST['sid']))
                    $this->acp_login($afile);
                furl::o()->location($eadmin_file);
            }
        }

        $this->acp_modules();

        if ($module && $this->perms['can_acp'] == 1)
            if (!in_array($module, (array) $this->perms['acp_modules'])) {
                if (!$onlylink)
                    furl::o()->location($eadmin_file);
                else
                    return false;
            }

        tpl::o()->assign("admin_sid", 'sid=' . $sid);
        tpl::o()->assign("eadmin_file", $eadmin_file);
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
     * Проверка забанен ли E-mail
     * @param string $email E-mail для проверки
     * @return string строка с ошибкой, если есть
     */
    protected function check_emailban($email) {
        $with_bans = null;
        $r = db::o()->p($email, time())->query('SELECT to_time, reason FROM bans WHERE ?' .
                ' LIKE REPLACE(REPLACE(email, "%", "\\%"), "*", "%") AND email<>""
                AND (to_time >= ? OR to_time=0) LIMIT 1');
        if ($r = db::o()->fetch_assoc($r))
            $with_bans = $this->bans_error($r, "email");
        return $with_bans;
    }

    /**
     * Проверка существования E-mail
     * @param string $email E-mail для проверки
     * @return bool статус проверки
     */
    protected function check_mxemail($email) {
        $email_arr = explode('@', $email);
        $host = $email_arr [1];
        if (!checkdnsrr($host))
            return false;
        return true;
    }

    /**
     * Проверка правильности E-mail
     * @param string $email E-mail для проверки
     * @param boolean|string $with_bans с банами? тогда данный аргумент примет значение строки с ошибкой
     * @return bool статус проверки, false - не соотв. рег. выражению или забаннен
     */
    public function check_email($email, &$with_bans = true) {

        if (!preg_match('/^([a-zA-Z0-9\_\-\.\%]+)\@([a-zA-Z0-9\_\-\.]+)\.([a-zA-Z]){2,4}$/siu', $email)) {
            $with_bans = null;
            return false;
        } elseif (class_exists('config') && config::o()->v('check_mx_email') && !$this->check_mxemail($email)) {
            $with_bans = null;
            return false;
        }

        if ($with_bans) {
            $with_bans = $this->check_emailban($email);
            if ($with_bans)
                return false;
        }

        return true;
    }

    /**
     * Проверка правильности логина
     * @param string $login логин для проверки
     * @return bool статус проверки
     */
    public function check_login($login) {
        return !(mb_strlen($login) < 2 || mb_strlen($login) > 25) && preg_match('/^[a-zа-я0-9\-\_ ]+$/siu', $login);
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
     * Функция check_perms будет бросать исключение, вместо переадресации на страницу
     * @param int $exc 1, если необходимы только исключения
     * при 1, значение автоматически сбросится после первого исключения
     * при 2, значение сбрасываться не будет
     * @return users_checker $this
     */
    public function perm_exception($exc = 1) {
        $this->perm_exception = (int) $exc;
        return $this;
    }

    /**
     * Функция проверки прав пользователей, в случае отсутствия прав - посылает на страницу логина
     * @param string $rule право пользователя(!без префикса can_!)
     * @param int $value значение права, от данного и выше.
     * @param int $def 2 - все
     * 1 - все, кроме гостей
     * 0 - все, кроме гостей и пользователей по-умолчанию
     * @return null
     * @throws EngineException
     */
    public function check_perms($rule = '', $value = 1, $def = 1) {
        if ($this->check_adminmode())
            return;
        $default = $this->perms ['guest'] || $this->perms ['bot'] ? 2 : $this->perms['default'];
        $if = false;
        if ($rule) {
            $rule = $this->perms ['can_' . $rule];
            $if = ((int) $rule) < ((int) $value);
        }
        $if = $if || $default > $def;
        if ($if && $this->perm_exception) {
            throw new EngineException('not_enought_rights');
            if ($this->perm_exception !== 2)
                $this->perm_exception(0);
        }

        if ($if) {
            if (globals::g("ajax"))
                print(lang::o()->v('not_enought_rights'));
            else
                furl::o()->location(furl::o()->construct("login", array(
                            "ref" => $_SERVER ['REQUEST_URI'])));
            die();
        }
    }

    /**
     * Проверка права
     * @param string $rule право пользователя(!без префикса can_!) или параметры system, pm_count, acp_modules
     * @param int $value проверяемое значение
     * @return bool|mixed true, если значение больше или равно данному, или значение параметра
     */
    public function perm($rule, $value = null) {
        $nocan = false;
        $ret = false;
        if ($rule == "system")
            $nocan = true;
        elseif (in_array($rule, $this->retperms)) {
            $ret = true;
            $nocan = true;
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
     * @param string $login логин
     * @param string $password пароль
     * @param int $id ID пользователя
     * @param string $error ошибка при выполнении, если имеется
     * @return string пассхеш, в случае успешного завершения
     */
    public function check_data($login, $password, &$error = "", &$id = 0) {
        if (!$login || !$password) {
            $error = (lang::o()->v('login_false_signin'));
            return false;
        }

        try {
            plugins::o()->pass_data(array('login' => $login,
                'password' => $password,
                'error' => &$error,
                'id' => &$id), true)->run_hook('users_check_data');
        } catch (PReturn $e) {
            return $e->r();
        }
        /* @var $etc etc */
        $etc = n("etc");
        $row = $etc->select_user(null, $login, 'id,password,salt');
        if (!$row ['id'] || $row ['password'] != $this->generate_pwd_hash($password, $row ['salt'])) {
            $error = (lang::o()->v('login_false_signin'));
            return false;
        }
        //if ($row ['confirmed'] != 3) {
        //	$error = ( lang::o()->v('login_not_confirmed_account') );
        //}
        $id = $row['id'];
        if (mb_strlen($row ['salt']) != 32) {
            $salt = $this->generate_salt(32);
            $password = $this->generate_pwd_hash($password, $salt);
            db::o()->p($row ['id'])->update(array('salt' => $salt,
                'password' => $password), 'users', 'WHERE id = ? LIMIT 1');
        }
        else
            $password = $row ['password'];
        return $password;
    }

}

class users_getter extends users_checker {

    /**
     * Группа пользователя по-умолчанию
     * @var int $def_group
     */
    protected $def_group = 0;

    /**
     * Группа гостя по-умолчанию
     * @var int $guest_group
     */
    protected $guest_group = 0;

    /**
     * Группа заблокированных
     */

    const banned_group = -1;

    /**
     * Язык данного юзера
     * @var string $lang
     */
    protected $lang = "";

    /**
     * Тема данного юзера
     * @var string $theme
     */
    protected $theme = "";

    /**
     * Цвет темы
     * @var string $theme_color 
     */
    protected $theme_color = "";

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
     * @param int $group ID группы
     * @return string имя группы
     */
    public function get_group_name($group) {
        return lang::o()->if_exists($group == self::banned_group ? "group_banned" : $this->groups [$group] ['name']);
    }

    /**
     * Получение группы
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
     * Получение темы/цвета темы пользователя
     * @param bool $color цвет темы?
     * @return string имя темы/цвета темы
     */
    public function get_theme($color = false) {
        return $color ? $this->theme_color : $this->theme;
    }

}

class users_modifier extends users_getter {

    /**
     * Временные переменные пользователя
     * @var array $tmp_vars
     */
    protected $tmp_vars = null;

    /**
     * Временные права пользователя
     * @var array $tmp_perms
     */
    protected $tmp_perms = null;

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
     * @param array $what массив переменных
     * @param bool $merge объединить?
     * @return users_modifier $this
     */
    public function set_tmpvars($what, $merge = false) {
        if ($this->tmp_vars)
            $this->remove_tmpvars();
        $this->tmp_vars = $this->vars;
        $this->vars = $merge ? array_merge($this->vars, $what) : $what;
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
     * Установка временных прав пользователя
     * @param array $what массив переменных
     * @param bool $merge объединить?
     * @return users_modifier $this
     */
    public function set_tmpperms($what, $merge = false) {
        if ($this->tmp_perms)
            $this->remove_tmpperms();
        $this->tmp_perms = $this->perms;
        $this->perms = $merge ? array_merge($this->perms, $what) : $what;
        return $this;
    }

    /**
     * Удаление временных прав пользователя
     * @return users_modifier $this
     */
    public function remove_tmpperms() {
        if (!$this->tmp_perms)
            return;
        $this->perms = $this->tmp_perms;
        return $this;
    }

    /**
     * Генерация пассхеша, для привязки к IP
     * @param string $real_hash passhash из БД
     * @param bool $short_sess короткая сессия
     * @return string сгенерированный пассхеш
     */
    public function passhash_real($real_hash, $short_sess = false) {
        if ($short_sess)
            $real_hash = md5($real_hash . session_id());
        if (!class_exists('config') || config::o()->v('ip_binding'))
            return md5(md5($real_hash) . $this->get_ip(false));
        else
            return $real_hash;
    }

    /**
     * Декодирование настроек пользователя
     * @param array $uservars параметры пользователя или массив настроек
     * @param string $what декодируемый столбец или ничего
     * @return array декодированные параметры пользователя
     */
    public function decode_settings($uservars = null, $what = "settings") {
        if (!$uservars) {
            $this->vars = $this->decode_settings($this->vars, $what);
            return $this->vars;
        }
        $uv = $uservars;
        if ($what) {
            $uv = $uservars[$what];
            unset($uservars[$what]);
        }
        $t = explode("\n", $uv);
        $c = count($t);
        $a = array();
        for ($i = 0; $i < $c; $i++) {
            $tc = $t[$i];
            $ap = mb_strpos($tc, ":");
            $area = mb_substr($tc, 0, $ap);
            if (!$area)
                continue;
            $a[$area] = str_replace('\n', "\n", mb_substr($tc, $ap + 1));
        }
        if (!$what)
            return $a;
        if (!is_array($a) || !$a)
            return $uservars;
        return array_merge($uservars, $a);
    }

    /**
     * Сериализация массива с настройками пользователя
     * @param array $settings массив настроек(show_age, website, icq,
     *      skype, country, town, name_surname)
     * @return string сериализованные настройки
     */
    public function make_settings($settings) {
        if (is_array($settings)) {
            $r = '';
            foreach ($settings as $area => $value)
                if ($value)
                    $r .= $area . ':' . str_replace("\n", '\n', $value) . "\n";
            return $r;
        }
        return "";
    }

}

class users extends users_modifier {
    /**
     * Префикс для бота
     */

    const bot_prefix = '[BOT]';

    /**
     * Пара ID=>право
     * @var array $pid
     */
    protected $pid = array();

    /**
     * Извлечение массива групп из БД
     * @return null
     */
    protected function get_groups() {
        if (!($a = cache::o()->read('groups'))) {
            $gp = db::o()->query("SELECT id, perm, dvalue FROM groups_perm");
            while ($row = db::o()->fetch_assoc($gp)) {
                $row["perm"] = "can_" . $row["perm"];
                $this->pid[$row["id"]] = $row["perm"];
                $dperms[$row["perm"]] = $row["dvalue"];
            }
            $gr = db::o()->query("SELECT * FROM groups");
            while ($group = db::o()->fetch_assoc($gr)) {
                $id = $group["id"];
                $perms = $group["perms"];
                unset($group["perms"]);
                $group = array_merge($dperms, $group);
                if ($perms)
                    $this->decode_perms($perms, $group);
                $this->groups[$id] = $group;
            }
            cache::o()->write(array($this->groups, $this->pid));
        }
        else
            list($this->groups, $this->pid) = $a;
        $this->def_group = $this->find_group('default');
    }

    /**
     * "Поимка" бота
     * @return bool поймали?
     */
    protected function catch_bot() {
        if (!config::o()->v('use_bots'))
            return false;
        $ip = $this->get_ip();
        $agent = $_SERVER ['HTTP_USER_AGENT'];
        $r = db::o()->p($ip, $ip, $agent)->query("SELECT id, name FROM bots WHERE firstip<=? AND lastip>=?
                OR ? LIKE CONCAT('%', agent, '%') LIMIT 1");
        list($id, $name) = db::o()->fetch_row($r);
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
     * Инициализация банов IP
     * @return null
     */
    protected function init_ipbans() {
        $ip = $this->get_ip();
        $ban_allowed = defined("ALLOW_WITH_BAN"); // отключить баны?
        if (config::o()->v('use_ipbans') && !$ban_allowed) {
            $res = db::o()->p($ip, $ip)->query("SELECT * FROM bans WHERE ? >= ip_f AND ? <= ip_t");
            if ($row = db::o()->fetch_assoc($res))
                n("message")->error($this->bans_error($row));
        }
    }

    /**
     * Начало инициализации пользователя
     * @return null
     */
    protected function init_begin() {
        display::o()->site_autoon();
        $this->lang = validfolder($_COOKIE ["lang"], LANGUAGES_PATH) ? $_COOKIE ["lang"] : config::o()->v('default_lang');
        $this->theme = validfolder($_COOKIE ["theme"]) ? $_COOKIE ["theme"] : config::o()->v('default_style');
        init_spaths();
        $this->theme_color = tpl::o()->set_color($_COOKIE ["theme_color"]);
        lang::o()->change_folder($this->lang);
        $this->init_ipbans();
        $this->get_groups();
        try {
            plugins::o()->run_hook('user_init_begin');
        } catch (PReturn $e) {
            return $e->r();
        }
    }

    /**
     * Инициализация пользовательских банов
     * @param array $row массив данных пользователя
     * @param int $group группа пользователя
     * @throws EngineException
     */
    protected function init_userbans($row, &$group) {
        $ban_allowed = defined("ALLOW_WITH_BAN"); // отключить баны?
        if ($group == users_getter::banned_group && $this->groups [$row ['old_group']] ['can_bebanned']) {
            if (!$ban_allowed)
                n("message")->error($this->bans_error($row, "user"));
            throw new EngineException;
        } elseif ($group == users_getter::banned_group) {
            $group = $row ['old_group'];
            /* @var $etc etc */
            $etc = n("etc");
            $etc->unban_user($row['id']);
        }
    }

    /**
     * Инициализация юзера
     * @return null
     */
    public function init() {
        $this->init_begin();

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

            plugins::o()->pass_data(array('login' => $login,
                'password' => $password), true)->run_hook('users_init_middle');

            if (!$login && !$password && $this->catch_bot())
                return;
            if (!$login || !$password)
                throw new EngineException;

            if (mb_strlen($password) != 32) {
                $this->clear_cookies();
                n("message")->error('invalid_cookie');
            }

            $res = db::o()->p(mb_strtolower($login))->query("SELECT u.*, b.reason FROM users AS u
                LEFT JOIN bans AS b ON b.uid=u.id
                WHERE u.username_lower = ?
                GROUP BY u.id");
            $row = db::o()->fetch_assoc($res);
            $group = &$row ['group'];

            if ($row['confirmed'] != 3)
                $group = $this->guest_group;

            if (!$row || $password != $this->passhash_real($row ["password"], $short_sess))
                throw new EngineException;
            $this->init_userbans($row, $group);

            $row ['ip'] = $this->get_ip();
            if (!$this->groups [$group])
                n("message")->error('group_doesnt_exists');

            $this->perms = $this->groups [$group];
            $this->vars = $row;

            plugins::o()->pass_data(array('user' => &$this->vars, 'perms' => &$this->perms))->run_hook('users_init_end');

            $this->alter_perms();
        } catch (EngineException $e) {
            $guest = $this->find_group('guest');
            if (!$guest)
                n("message")->error('no_guest_group');
            $this->perms = $this->groups [$guest];
        } catch (PReturn $e) {
            return $e->r();
        }
    }

    /**
     * Инициализация сессии пользователя
     * @return null
     */
    public function write_session() {

        if (!cache::o()->query_delay('usessions', config::o()->v('delay_userupdates')))
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
            plugins::o()->pass_data(array('update_sess' => &$updateset,
                'update_user' => &$users_updateset), true)->run_hook('users_sessions');
        } catch (PReturn $e) {
            return $e->r();
        }

        if ($this->vars)
            db::o()->p($uid)->update($users_updateset, "users", "WHERE id=? LIMIT 1");

        db::o()->p($sid)->update($updateset, "sessions", "WHERE sid=? LIMIT 1");

        if (db::o()->affected_rows() < 1)
            db::o()->insert($updateset, "sessions");
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
     * @return null
     */
    public function groups_autoupdate() {
        $a = $k = array();

        try {
            plugins::o()->pass_data(array(
                'update_columns' => &$this->update_columns), true)->run_hook('users_groups_autoupdate');
        } catch (PReturn $e) {
            return $e->r();
        }

        $cols = $this->update_columns;
        $c = count($cols);
        $w = array();
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
                $w[] = $id;
        }
        if (!$w)
            return;
        $w[] = $this->find_group('default');

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
                    db::o()->p($v);
                    $where .= ( $where ? " AND " : "") . "`" . $e . "`>=?";
                    if ($a[$i][++$j]) {
                        db::o()->p($a[$i][$j]);
                        $where .= " AND `" . $e . "`<?";
                    }
                }
            }
            if (!$where)
                continue;
            db::o()->p($w)->update(array(
                "group" => $id), "users", "WHERE " . $where . " AND `group` IN (@" . count($w) . '?)');
        }
    }

    // Реализация Singleton для переопределяемого класса

    /**
     * Объект данного класса
     * @var users $o
     */
    protected static $o = null;

    /**
     * Конструктор? А где конструктор? А нет его.
     * @return null 
     */
    protected function __construct() {
        
    }

    /**
     * Не клонируем
     * @return null 
     */
    protected function __clone() {
        
    }

    /**
     * И не десериализуем
     * @return null 
     */
    protected function __wakeup() {
        
    }

    /**
     * Получение объекта класса
     * @return users $this
     */
    public static function o() {
        if (!self::$o) {
            $cn = __CLASS__;
            $c = n($cn, true);
            self::$o = new $c();
        }
        return self::$o;
    }

}

?>