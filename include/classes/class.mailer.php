<?php

/**
 * Project:            	CTRev
 * File:                class.mailer.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name                Класс подписок
 * @tutorial            Подписка только для данной категории, на дочерние не действует.
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class mailer extends pluginable_object {

    /**
     * Статус системы подписок
     * @var bool
     */
    protected $state = true;

    /**
     * Допустимые интервалы подписок
     * @var array
     */
    public static $allowed_interval = array(
        '0' => "no",
        '86400' => "day",
        '604800' => "week",
        '2592000' => "month");

    /**
     * Тип подписок
     * @var string
     */
    protected $type = 'torrents';

    /**
     * Допустимые типы
     * @var array
     */
    protected $allowed_types = array(
        'torrents',
        'category');

    /**
     * Конструктор класса
     * @global config $config
     * @return null 
     */
    protected function plugin_construct() {
        global $config;
        $this->state = (bool) $config->v('mailer_on');
        $this->access_var('allowed_types', PVAR_ADD);
    }

    /**
     * Изменение типа подписок
     * @param string $type тип подписок
     * @return mailer $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Получение заголовков для подписок
     * @global lang $lang
     * @param int $id ID ресурса
     * @param string $type тип подписки
     * @return string код заголовка
     */
    public function get_title($id, $type = "torrents") {
        global $lang;
        $vars = $this->change_type($type)->get_vars($id);
        $link = $vars["link"];
        $name = $vars["name"];
        $content = "<b>" . $lang->v('usercp_mailer_type_' . $type) . "</b>" .
                "<a href='" . $link . "'>" . $name . "</a>";
        return $content;
    }

    /**
     * Отображение подписок
     * @global tpl $tpl
     * @global db $db
     * @return null
     */
    public function show() {
        global $db, $tpl;
        if (!$this->state) {
            disabled();
            return;
        }
        $res = $db->query("SELECT * FROM mailer");
        $tpl->register_modifier('get_mailer_title', array($this, "get_title"));
        $tpl->assign('mailer_res', $db->fetch2array($res));
        $tpl->assign('intervals', self::$allowed_interval);
        $tpl->display('usercp/mailer.tpl');
    }

    /**
     * Создание\обновление подписки
     * @global db $db
     * @global users $users
     * @global lang $lang
     * @param int $id ID ресурса
     * @param int $interval интервал посылок
     * @param bool $updt обновить?
     * @return bool|int статус создания\обновления
     * @throws EngineException 
     */
    public function make($id, $interval = null, $updt = false) {
        global $db, $users, $lang;
        if (!$this->state)
            return true;
        $type = $this->type;
        $lang->get('usercp');
        $users->check_perms();
        $interval = (int) $interval;
        if (!self::$allowed_interval[$interval])
            throw new EngineException('usercp_mailer_not_allowed_interval');
        $where = array(
            "user" => $users->v('id'),
            "toid" => (int) $id,
            "type" => $type
        );
        $upd = array("interval" => ($interval ? $interval : $users->v('mailer_interval')));
        if (!$updt) {
            $db->no_error();
            $db->insert(array_merge($upd, $where), "mailer");
            if ($db->errno() != UNIQUE_VALUE_ERROR && $db->errno())
                throw new EngineException($lang->v('db_error') . '(' . $db->errno() . ')');
            return true;
        }
        if ($updt || ($db->errno() == UNIQUE_VALUE_ERROR && $interval)) {
            $wh = "";
            foreach ($where as $key => $value)
                $wh .= ( $wh ? " AND " : "") . "`" . $key . '`=' . $db->esc($value);
            return $db->update($upd, "mailer", "WHERE " . $wh . " LIMIT 1");
        } else
            return true;
    }

    /**
     * Удаление подписки
     * @global db $db
     * @global users $users
     * @param int $id ID ресурса
     * @param bool $user ID пользователя?
     * @return int статус удаления
     */
    public function remove($id, $user = false) {
        global $db, $users;
        if (!$this->state)
            return true;
        $type = $this->type;
        $users->check_perms();
        $id = (int) $id;
        if ($user)
            $where = 'user = ' . $id;
        else
            $where = "user = " . $users->v('id') .
                    " AND toid = " . $id .
                    " AND type = " . $db->esc($type);
        return $db->delete("mailer", "WHERE " . $where . " LIMIT 1");
    }

    /**
     * Обновление и отсылка "срочных" писем в подписках
     * @global db $db
     * @param int|array $id ID ресурса/ресурсов
     * @return int  статус обновления
     */
    public function update($id) {
        global $db;
        if (!$this->state)
            return true;
        $type = $this->type;
        if (!$id)
            return;
        if (is_array($id))
            $id = 'IN(' . implode(',', array_map('intval', $id)) . ')';
        else
            $id = '= ' . ((int) $id);
        $where = 'toid ' . $id . ' AND type=' . $db->esc($type);
        $ret = $db->update(array("is_new" => 1), "mailer", "WHERE " . $where);
        $this->cleanup($id);
        return $ret;
    }

    /**
     * Отсылка писем в подписках
     * @global db $db
     * @global config $config
     * @param int $id ID ресурса
     * @return int  статус обновления
     */
    public function cleanup($id = null) {
        global $db, $config;
        if (!$this->state)
            return true;
        $type = $this->type;
        $id = (int) $id;
        if (!$type)
            $id = null;
        $where = "m.last_check <= (" . time() . ($id ? " - interval" : "") . ")
            AND m.is_new='1'" . ($id ? "
                AND m.interval=0
                AND m.toid=" . $id . "
                AND m.type=" . $db->esc($type) : "");
        $res = $db->query('SELECT m.user,m.toid,m.type, u.email FROM mailer AS m
            LEFT JOIN users AS u ON u.id=m.user
            WHERE ' . $where . ($config->v('mailer_per_once') ? ' LIMIT ' . $config->v('mailer_per_once') : ''));
        $where = array();
        $c = 0;
        while ($row = $db->fetch_assoc($res)) {
            $this->send_mail($row["email"], $id);
            $where[$row["type"]]["user"][] = $row["user"];
            $where[$row["type"]]["toid"][] = $row["toid"];
            $c++;
        }
        $wh = "";
        foreach ($where as $type => $vs) {
            $wh .= ( $wh ? " OR " : "") . "(type=" . $db->esc($type);
            foreach ($vs as $what => $vals)
                $wh .= " AND `" . $what . "` IN (" . implode(',', $vals) . ")";
            $wh .= ")";
        }
        if ($wh)
            return $db->update(array("last_check" => time(),
                        "is_new" => 0), "mailer", ($wh ? "WHERE " . $wh : "") . " LIMIT " . $c);
    }

    /**
     * Получение переменных
     * @param int $id ID ресурса
     * @return array массив переменных(обязательно link и name)
     */
    public function get_vars($id) {
        $type = $this->type;
        $method = "get_vars_" . $type;
        $id = (int) $id;
        return $this->call_method($method, $id);
    }

    /**
     * Получение переменных для категорий
     * @global categories $cats
     * @global furl $furl
     * @param int $id ID категории
     * @return array массив переменных
     */
    protected function get_vars_category($id) {
        global $cats, $furl;
        $res = $cats->get($id);
        $name = $res["name"];
        $link = $furl->construct('torrents', array('act' => 'new', 'cat' => $res["transl_name"]));
        return array("link" => $link, "name" => $name);
    }

    /**
     * Получение переменных для торрентов
     * @global db $db
     * @global furl $furl
     * @param int $id ID торрента
     * @return array массив переменных
     */
    protected function get_vars_torrents($id) {
        global $db, $furl;
        $res = $db->fetch_assoc($db->query('SELECT title FROM torrents WHERE id=' . $id . ' LIMIT 1'));
        $name = $res["title"];
        $link = $furl->construct('torrents', array('title' => $name, 'id' => $id));
        return array("link" => $link, "name" => $name);
    }

    /**
     * Отсылка письма
     * @global etc $etc
     * @param string $to E-mail адресата
     * @param int $id ID ресурса
     * @return int статус отсылки
     */
    protected function send_mail($to, $id) {
        global $etc;
        $type = $this->type;
        $vars = $this->get_vars($id);
        return $etc->send_mail($to, "mailer_" . $type, $vars);
    }

}

?>