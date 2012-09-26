<?php

/**
 * Project:            	CTRev
 * File:                plugin.convert_cyberhype.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Пример плагина
 * @version           	1.00
 * @tutorial            Плагин для реализации входа конвертированного пользователя
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class plugin_convert_cyberhype {

    /**
     * Версия плагина
     * @var string
     */
    public $version = '1.00';

    /**
     * Имя плагина
     * @var string
     */
    public $name = 'Cyberhype Convert';

    /**
     * Описание плагина
     * @var string
     */
    public $descr = 'Реализация входа конв. пользователя';

    /**
     * Совместимость с движком. Наилучшая
     * @var string
     */
    public $compatibility = '1.00';

    /**
     * Совместимость с движком.  Мин. версия.
     * @var string
     */
    public $compatibility_min = '1.00';

    /**
     * Совместимость с движком. Макс. версия.
     * @var string
     */
    public $compatibility_max = '1.00';

    /**
     * Автор плагина
     * @var string
     */
    public $author = 'The Cheat';

    /**
     * Инициализация плагина
     * @param plugins $plugins объект плагиновой системы
     * @return null
     */
    public function init($plugins) {
        $plugins->add_hook('users_check_data', array($this, 'converted_login'));
        $plugins->add_hook('usercp_save_main', array($this, 'converted_usercp'));
        $plugins->add_hook('login_recover_save', array($this, 'converted_recover'));
    }

    /**
     * Сохранение конвертированного пользователя
     * @param array $data массив переменных
     * @return null
     */
    public function converted_recover($data) {
        $update = &$data['update'];
        $update['converted'] = '0';
    }

    /**
     * Сохранение конвертированного пользователя
     * @param array $data массив переменных
     * @return null
     */
    public function converted_usercp($data) {
        $update = &$data['update'];
        if ($update ["password"])
            $update['converted'] = '0';
    }

    /**
     * Вход для конвертированного пользователя
     * @global users $users
     * @global etc $etc
     * @global db $db
     * @global lang $lang
     * @param array $data массив переменных
     * @return null
     * @throws PReturn
     */
    public function converted_login($data) {
        global $users, $etc, $db, $lang;
        $login = $data['login'];
        $password = $data['password'];
        $error = &$data['error'];
        $id = &$data['id'];
        $u = $etc->select_user(null, $login, 'id,password,salt,converted');
        if (!$u['converted'])
            return;
        $salt = $u['salt'];
        if ($u['password'] != md5($salt . $password . $salt)) {
            $error = $lang->v('login_false_signin');
            return;
        }
        $id = $u['id'];
        $salt = $users->generate_salt(32);
        $password = $users->generate_pwd_hash($password, $salt);
        $db->update(array('salt' => $salt,
            'password' => $password,
            'converted' => '0'), 'users', 'WHERE id = ' . $id . ' LIMIT 1');
        throw new PReturn($password);
    }

    /**
     * Установка плагина
     * @param bool $re переустановка?
     * в данном случае необходимо лишь произвести изменения в файлах
     * @return null
     */
    public function install($re = false) {
        global $db;
        $db->query("ALTER TABLE `users` ADD `converted` ENUM( '1', '0' ) NOT NULL DEFAULT '0' AFTER `warnings_count`");
        $db->update(array('converted' => '1'), 'users');
    }

    /**
     * Удаление плагина
     * @param bool $replaced было ли успешно ВСЁ замененённое сохранено?
     * @return null
     */
    public function uninstall($replaced = false) {
        global $db;
        $db->no_error()->query("ALTER TABLE `users` DROP `converted`");
    }

}

?>