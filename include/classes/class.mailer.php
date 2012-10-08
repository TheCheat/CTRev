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
     * @return null 
     */
    protected function plugin_construct() {
        $this->state = (bool) config::o()->v('mailer_on');
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
     * @param int $id ID ресурса
     * @param string $type тип подписки
     * @return string код заголовка
     */
    public function get_title($id, $type = "torrents") {
        $vars = $this->change_type($type)->get_vars($id);
        $link = $vars["link"];
        $name = $vars["name"];
        $content = "<b>" . lang::o()->v('usercp_mailer_type_' . $type) . "</b>" .
                "<a href='" . $link . "'>" . $name . "</a>";
        return $content;
    }

    /**
     * Отображение подписок
     * @return null
     */
    public function show() {
        if (!$this->state) {
            disabled();
            return;
        }
        $res = db::o()->query("SELECT * FROM mailer");
        tpl::o()->register_modifier('get_mailer_title', array($this, "get_title"));
        tpl::o()->assign('mailer_res', db::o()->fetch2array($res));
        tpl::o()->assign('intervals', self::$allowed_interval);
        tpl::o()->display('usercp/mailer.tpl');
    }

    /**
     * Создание\обновление подписки
     * @param int $id ID ресурса
     * @param int $interval интервал посылок
     * @param bool $updt обновить?
     * @return bool|int статус создания\обновления
     * @throws EngineException 
     */
    public function make($id, $interval = null, $updt = false) {
        if (!$this->state)
            return true;
        $type = $this->type;
        lang::o()->get('usercp');
        users::o()->check_perms();
        $interval = (int) $interval;
        if (!self::$allowed_interval[$interval])
            throw new EngineException('usercp_mailer_not_allowed_interval');
        $where = array(
            "user" => users::o()->v('id'),
            "toid" => (int) $id,
            "type" => $type
        );
        $upd = array("interval" => ($interval ? $interval : users::o()->v('mailer_interval')));
        if (!$updt) {
            db::o()->no_error();
            db::o()->insert(array_merge($upd, $where), "mailer");
            if (db::o()->errno() != UNIQUE_VALUE_ERROR && db::o()->errno())
                throw new EngineException(lang::o()->v('db_error') . '(' . db::o()->errno() . ')');
            return true;
        }
        if ($updt || (db::o()->errno() == UNIQUE_VALUE_ERROR && $interval)) {
            $wh = "";
            foreach ($where as $key => $value)
                $wh .= ( $wh ? " AND " : "") . "`" . $key . '`=' . db::o()->esc($value);
            return db::o()->update($upd, "mailer", "WHERE " . $wh . " LIMIT 1");
        } else
            return true;
    }

    /**
     * Удаление подписки
     * @param int $id ID ресурса
     * @param bool $user ID пользователя?
     * @return int статус удаления
     */
    public function remove($id, $user = false) {
        if (!$this->state)
            return true;
        $type = $this->type;
        users::o()->check_perms();
        $id = (int) $id;
        if ($user)
            $where = 'user = ' . $id;
        else
            $where = "user = " . users::o()->v('id') .
                    " AND toid = " . $id .
                    " AND type = " . db::o()->esc($type);
        return db::o()->delete("mailer", "WHERE " . $where . " LIMIT 1");
    }

    /**
     * Обновление и отсылка "срочных" писем в подписках
     * @param int|array $id ID ресурса/ресурсов
     * @return int  статус обновления
     */
    public function update($id) {
        if (!$this->state)
            return true;
        $type = $this->type;
        if (!$id)
            return;
        if (is_array($id))
            $id = 'IN(' . implode(',', array_map('intval', $id)) . ')';
        else
            $id = '= ' . ((int) $id);
        $where = 'toid ' . $id . ' AND type=' . db::o()->esc($type);
        $ret = db::o()->update(array("is_new" => 1), "mailer", "WHERE " . $where);
        $this->cleanup($id);
        return $ret;
    }

    /**
     * Отсылка писем в подписках
     * @param int $id ID ресурса
     * @return int  статус обновления
     */
    public function cleanup($id = null) {
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
                AND m.type=" . db::o()->esc($type) : "");
        $res = db::o()->query('SELECT m.user,m.toid,m.type, u.email FROM mailer AS m
            LEFT JOIN users AS u ON u.id=m.user
            WHERE ' . $where . (config::o()->v('mailer_per_once') ? ' LIMIT ' . config::o()->v('mailer_per_once') : ''));
        $where = array();
        $c = 0;
        while ($row = db::o()->fetch_assoc($res)) {
            $this->send_mail($row["email"], $id);
            $where[$row["type"]]["user"][] = $row["user"];
            $where[$row["type"]]["toid"][] = $row["toid"];
            $c++;
        }
        $wh = "";
        foreach ($where as $type => $vs) {
            $wh .= ( $wh ? " OR " : "") . "(type=" . db::o()->esc($type);
            foreach ($vs as $what => $vals)
                $wh .= " AND `" . $what . "` IN (" . implode(',', $vals) . ")";
            $wh .= ")";
        }
        if ($wh)
            return db::o()->update(array("last_check" => time(),
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
     * @param int $id ID категории
     * @return array массив переменных
     */
    protected function get_vars_category($id) {
        /* @var $cats categories */
        $cats = n("categories");
        $res = $cats->get($id);
        $name = $res["name"];
        $link = furl::o()->construct('torrents', array('act' => 'new', 'cat' => $res["transl_name"]));
        return array("link" => $link, "name" => $name);
    }

    /**
     * Получение переменных для торрентов
     * @param int $id ID торрента
     * @return array массив переменных
     */
    protected function get_vars_torrents($id) {
        $res = db::o()->fetch_assoc(db::o()->query('SELECT title FROM torrents WHERE id=' . $id . ' LIMIT 1'));
        $name = $res["title"];
        $link = furl::o()->construct('torrents', array('title' => $name, 'id' => $id));
        return array("link" => $link, "name" => $name);
    }

    /**
     * Отсылка письма
     * @param string $to E-mail адресата
     * @param int $id ID ресурса
     * @return int статус отсылки
     */
    protected function send_mail($to, $id) {
        $type = $this->type;
        $vars = $this->get_vars($id);
        /* @var $etc etc */
        $etc = n("etc");
        return $etc->send_mail($to, "mailer_" . $type, $vars);
    }

}

?>