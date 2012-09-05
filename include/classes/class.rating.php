<?php

/**
 * Project:            	CTRev
 * File:                class.rating.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс рейтинга
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class rating extends pluginable_object {

    /**
     * Статус системы рейтинга
     * @var bool
     */
    protected $state = true;

    /**
     * Инициализовано?
     * @var bool
     */
    protected $inited = false;

    /**
     * Максимальные значения рейтинга
     * @var array
     */
    protected $max = array(
        "torrents" => 5,
        "users" => 1);

    /**
     * Минимальные значения рейтинга
     * @var array
     */
    protected $min = array(
        "torrents" => 0.5,
        "users" => - 1);

    /**
     * Возможная часть рейтинга, 0 - означает только возможность мин. и макс. значения
     * @var array
     */
    protected $part = array(
        "torrents" => 0.5,
        "users" => 0);

    /**
     * Тип рейтинга
     * @var string
     */
    protected $type = 'torrents';

    /**
     * Тип голоса
     * @var string
     */
    protected $stype = 'torrents';

    /**
     * Допустимые типы рейтинга
     * @var array
     */
    protected $allowed_types = array(
        'torrents',
        'users');

    /**
     * Допустимые типы голоса
     * @var array
     */
    protected $allowed_stypes = array(
        'torrents');

    /**
     * Конструктор класса
     * @global config $config
     * @return null 
     */
    protected function plugin_construct() {
        global $config;
        $this->state = (bool) $config->v('rating_on');
        $this->access_var('allowed_types', PVAR_ADD);
        $this->access_var('allowed_stypes', PVAR_ADD);
        $this->access_var('max', PVAR_ADD);
        $this->access_var('min', PVAR_ADD);
        $this->access_var('part', PVAR_ADD);
    }

    /**
     * Изменение типа рейтинга
     * @param string $rtype тип рейтинга
     * @return rating $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Изменение типа рейтинга
     * @param string $stype тип рейтинга
     * @return rating $this
     */
    public function change_stype($stype) {
        if (!in_array($stype, $this->allowed_stypes))
            return $this;
        $this->stype = $stype;
        return $this;
    }

    /**
     * Инициализация рейтинга звёздами
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @global lang $lang
     * @param int $toid ID ресурса
     * @param int $owner владелец ресурса(ч\с создатель торрента)
     * @param array $res массив ресурса
     * @param int $stoid доп. ID ресурса(только для проверки на то, голосовал ли)
     * @return null
     */
    public function display($toid, $owner = null, $res = null, $stoid = 0) {
        global $tpl, $db, $users, $lang;
        if (!$this->state) {
            disabled();
            return;
        }
        if (is_array($toid)) {
            if ($toid ["rtype"])
                $this->change_type($toid ["rtype"]);
            $owner = $toid ["owner"];
            $res = $toid ["res"];
            $stoid = $toid ["srid"];
            if ($toid ["stype"])
                $this->change_stype($toid ["stype"]);
            $toid = $toid ["rid"];
        }
        $type = $this->type;
        $stype = $this->stype;
        $lang->get('rating');
        if (!$type)
            $type = "torrents";
        if (!$stype)
            $stype = "torrents";
        $owner = (int) $owner;
        $toid = (int) $toid;
        $stoid = (int) $stoid;
        $count = 0;
        if (is_numeric($res ["rate_count"]) && is_numeric($res ["rnum_count"])) {
            $count = $res ["rnum_count"];
            $cur_votes = ($res ["rnum_count"] ? $res ["rate_count"] / $res ["rnum_count"] : 0);
        } else
            $cur_votes = $db->act_row("ratings", "value", "avg", ('toid =' . $toid . ' AND type =' . $db->esc($type)), $count);
        $this->min [$type] = (float) $this->min [$type];
        $this->max [$type] = (float) $this->max [$type];
        $this->part [$type] = (float) $this->part [$type];
        $disabled = false;
        if (!$users->perm('vote') || ($owner == $users->v('id') && $owner))
            $disabled = true;
        else {
            $u = $db->esc($users->v() ? $users->v('id') : $users->get_ip());
            $cur_vote = $db->fetch_assoc($db->query('SELECT value FROM ratings WHERE ' .
                            'toid =' . $toid . ' AND stoid=' . $stoid . '
                                     AND stype=' . $db->esc($stype) . ' AND ' .
                            'user = ' . $u . ' AND ip="' . (!$users->v()) . '" AND type=' .
                            $db->esc($type) . ' LIMIT 1'));
            if ($cur_vote)
                $disabled = true;
        }
        $this->value_to_part($cur_votes);
        $tpl->assign("rtoid", $toid);
        $tpl->assign("rtype", $type);
        $tpl->assign("total", $cur_votes);
        $tpl->assign("disabled", $disabled);
        $tpl->assign("count", $count);
        $tpl->assign("min", $this->min [$type]);
        $tpl->assign("loop", ($this->part [$type] ? ($this->max [$type] - $this->min [$type]) / $this->part [$type] + 1 : 1)); // section почему-то не хочет обрабатывать последнее значение
        $tpl->assign("per", $this->part [$type]);
        $tpl->assign("split", ($this->part [$type] ? ($this->part [$type] > 0 && $this->part [$type] <= 1 ? 1 / $this->part [$type] : 1) : 0));
        $tpl->assign("rating_inited", $this->inited);
        if (!$this->inited)
            $this->inited = true;
        $tpl->display("torrents/rating.tpl");
    }

    /**
     * Проверка голосования за торрент
     * @global users $users
     * @global db $db
     * @param int $toid ID торрента
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function torrents_check($toid, $value) {
        global $users, $db;
        $res = $db->fetch_assoc($db->query('SELECT poster_id
            FROM torrents WHERE id = ' . $toid . ' LIMIT 1'));
        if ($users->v('id') == $res ["poster_id"])
            throw new EngineException("rating_cant_vote_for_your_torrents");
    }

    /**
     * Проверка на торренты для кармы юзера
     * @global db $db
     * @param int $toid ID пользователя
     * @param int $stoid ID торрента
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function susers_torrents_check($toid, $stoid, $value) {
        global $db;
        if (!$db->count_rows('torrents', 'id=' . $stoid . ' AND poster_id=' . $toid))
            throw new EngineException("rating_cant_vote_karma_for_torrent");
    }

    /**
     * Проверка голосования за пользователя
     * @global users $users
     * @param int $toid ID пользователя
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function users_check($toid, $value) {
        global $users;
        if ($toid == $users->v('id'))
            throw new EngineException("rating_cant_vote_for_you");
    }

    /**
     * Кеш голосования за торрент
     * @global etc $etc
     * @param int $toid ID торрента
     * @param float $value значение рейтинга
     * @return null
     */
    protected function torrents_vote($toid, $value) {
        global $etc;
        $one = 1;
        if ((string) $value == etc::reset_count)
            $one = etc::reset_count;
        $etc->add_res(array(
            "rate" => $value,
            "rnum" => $one), null, 'torrents', $toid);
    }

    /**
     * Кеш голосования за пользователя
     * @global etc $etc
     * @param int $toid ID пользователя
     * @param float $value значение рейтинга
     * @return null
     */
    protected function users_vote($toid, $value) {
        global $etc;
        $etc->signed_res()->add_res(array("karma" => $value), null, 'users', $toid);
    }

    /**
     * Функция голосования
     * @global db $db
     * @global users $users
     * @global lang $lang
     * @global etc $etc
     * @param int $toid ID ресурса
     * @param float $value значение рейтинга
     * @param int $stoid доп. ID ресурса(только для проверки на то, голосовал ли)
     * @return bool true в случае успешного голосования
     * @throws EngineException 
     */
    public function vote($toid, $value, $stoid = 0) {
        global $db, $users, $lang, $etc;
        if (!$this->state)
            return true;
        $type = $this->type;
        $stype = $this->stype;
        if (!$type)
            $type = "torrents";
        if (!$stype)
            $stype = "torrents";
        $users->check_perms('vote', 1, 2);
        $lang->get('rating');
        $funct = $type . "_check";
        $toid = (int) $toid;
        $stoid = (int) $stoid;
        $value = (float) $value;
        $ret = $this->call_method($funct, array($toid, $value));
        if ($stoid && $stype) {
            $funct = "s" . $type . '_' . $stype . "_check";
            $ret = $this->call_method($funct, array($toid, $stoid, $value));
        }
        $insert = array(
            "toid" => $toid,
            "stoid" => $stoid,
            "type" => $type,
            "stype" => $stype,
            "value" => $value);
        $insert ["ip"] = !$users->v();
        $insert ["user"] = $users->v() ? $users->v('id') : $users->get_ip();
        if ($value > $this->max [$type] || $value < $this->min [$type])
            throw new EngineException("rating_false");
        if ((floatval($this->part [$type]) ? ($value * 100) % ($this->part [$type] * 100) != 0 : $value != $this->min [$type] && $value != $this->max [$type]))
            throw new EngineException("rating_false");
        $db->no_error();
        $db->insert($insert, "ratings");
        //$where = 'type =' . $db->esc($type) . ' AND toid =' . $db->esc($toid) . ' AND ' . $where;
        //$test = $db->count_rows("ratings", ($where));
        //if ($test) {
        if ($db->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException("rating_rated");
        //}
        $funct = $type . "_vote";
        $ret = $this->call_method($funct, array($toid, $value));
        return true;
    }

    /**
     * Получение суммы рейтинга(ч\с для кармы)
     * @global db $db
     * @param int $toid ID ресурса
     * @return int|float значение рейтинга
     */
    public function get_sum_rating($toid) {
        global $db;
        if (!$this->state)
            return 0;
        $type = $this->type;
        if (!$type)
            $type = "users";
        return $db->act_row("ratings", "value", "sum", ('type =' . $db->esc($type) . ' AND toid =' . longval($toid)));
    }

    /**
     * Подсчёт символов после запятой и возвращение множетеля, который необходим для преобразование выражения в
     * целое
     * @param float $value значение
     * @return int множетель, для преобразования в целое число
     */
    protected function count_decimals($value) {
        if (longval($value) != $value) {
            $dec = strval($value - longval($value));
            $dec = strlen($dec) - 2;
            $dec = pow(10, $dec);
        }
        if (!$dec)
            $dec = 1;
        return $dec;
    }

    /**
     * Приближение значения в меньшую сторону
     * @param float $value значение рейтинга
     * @return null
     */
    protected function value_to_part(&$value) {
        $type = $this->type;
        $value = (float) $value;
        if (!$value)
            return;
        $value = number_format($value, 9, '.', '');
        $dec = $this->count_decimals($value);
        if ($dec == 1)
            $dec = $this->count_decimals($this->part [$type]);
        if (floatval($this->part [$type]) > 0 && floatval($this->part [$type]) <= 1)
            if ((($value - longval($value)) * $dec) % ($this->part [$type] * $dec) != 0)
                $value = $value - ((($value - longval($value)) * $dec) % ($this->part [$type] * $dec)) / $dec;
    }

    /**
     * Получение среднего значения рейтинга
     * @global db $db
     * @param int $toid ID ресурса
     * @param int $del символов после запятой
     * @return float значение рейтинга
     */
    public function get_avg_rating($toid, $del = 1) {
        global $db;
        if (!$this->state)
            return 0;
        $type = $this->type;
        if (!$type)
            $type = "torrents";
        $value = floatval($db->act_row("ratings", "value", "avg", ('type =' . $db->esc($type) . ' AND toid =' . longval($toid))));
        $this->value_to_part($value);
        return number_format($value, ($del ? $del : 0), '.', '');
    }

    /**
     * Очистка голосов для ч/либо
     * @global db $db
     * @param int $id ID ч/либо
     * @return null
     */
    public function clear($id) {
        global $db;
        if (!$this->state)
            return;
        $id = (int) $id;
        $type = $this->type;
        if (!$type)
            $type = "torrents";
        $funct = $type . '_vote';
        $this->call_method($funct, array($id, etc::reset_count));
        $db->delete('ratings', 'WHERE type=' . $db->esc($type) . ' AND toid=' . $id);
    }

}

?>