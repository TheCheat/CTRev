<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.rating.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var bool $state
     */
    protected $state = true;

    /**
     * Инициализовано?
     * @var bool $inited
     */
    protected static $inited = false;

    /**
     * Максимальные значения рейтинга
     * @var array $max
     */
    protected $max = array(
        "content" => 5,
        "users" => 1);

    /**
     * Минимальные значения рейтинга
     * @var array $min
     */
    protected $min = array(
        "content" => 0.5,
        "users" => - 1);

    /**
     * Возможная часть рейтинга, 0 - означает только возможность мин. и макс. значения
     * @var array $part
     */
    protected $part = array(
        "content" => 0.5,
        "users" => 0);

    /**
     * Тип рейтинга
     * @var string $type
     */
    protected $type = 'content';

    /**
     * Тип голоса
     * @var string $stype
     */
    protected $stype = 'content';

    /**
     * Допустимые типы рейтинга
     * @var array $allowed_types
     */
    protected $allowed_types = array(
        'content',
        'users');

    /**
     * Допустимые типы голоса
     * @var array $allowed_stypes
     */
    protected $allowed_stypes = array(
        'content');

    /**
     * Номер рейтинга
     * @var int $count
     */
    protected static $count = 0;

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->state = (bool) config::o()->mstate('rating_manage');
        $this->access_var('allowed_types', PVAR_ADD);
        $this->access_var('allowed_stypes', PVAR_ADD);
        $this->access_var('max', PVAR_ADD);
        $this->access_var('min', PVAR_ADD);
        $this->access_var('part', PVAR_ADD);
        /**
         * @note Отображение рейтинга(display_rating)
         * int toid ID ресурса
         * string type тип ресурса
         * int owner владелец ресурса
         * array res массив ресурса
         * int stoid доп. ID ресурса(для уникальности)
         * string stype доп. тип ресурса(для уникальности)
         */
        tpl::o()->register_function("display_rating", array(
            $this,
            'display'));
    }

    /**
     * Изменение типа рейтинга
     * @param string $type тип рейтинга
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
     * Проверка невозможности голосовать
     * @param int $owner владелец ресурса(ч\с создатель торрента)
     * @param int $toid ID ресурса
     * @param int $stoid доп. ID ресурса(только для проверки на то, голосовал ли)
     * @return bool true, если голосование отключено
     */
    protected function check_voted($owner, $toid, $stoid) {

        $type = $this->type;
        $stype = $this->stype;
        $disabled = false;
        if (!users::o()->perm('vote') || ($owner == users::o()->v('id') && $owner))
            $disabled = true;
        else {
            $u = users::o()->v() ? users::o()->v('id') : users::o()->get_ip();
            db::o()->p($toid, $stoid, $stype, $u, !users::o()->v(), $type);
            $q = db::o()->query('SELECT value FROM ratings WHERE 
                toid = ? AND stoid=? AND stype=? AND user = ? AND ip=? AND type=? LIMIT 1');
            $cur_vote = db::o()->fetch_assoc($q);
            if ($cur_vote)
                $disabled = true;
        }
        return $disabled;
    }

    /**
     * Инициализация рейтинга звёздами
     * @param int $toid ID ресурса
     * @param int $owner владелец ресурса(ч\с создатель торрента)
     * @param array $res массив ресурса
     * @param int $stoid доп. ID ресурса(только для проверки на то, голосовал ли)
     * @return null
     */
    public function display($toid, $owner = null, $res = null, $stoid = 0) {
        if (!$this->state)
            return;
        if (is_array($toid)) {
            if ($toid ["type"])
                $this->change_type($toid ["type"]);
            $owner = $toid ["owner"];
            $res = $toid ["res"];
            $stoid = $toid ["stoid"];
            if ($toid ["stype"])
                $this->change_stype($toid ["stype"]);
            $toid = $toid ["toid"];
        }
        $type = $this->type;
        $stype = $this->stype;
        lang::o()->get('rating');
        $owner = (int) $owner;
        $toid = (int) $toid;
        $stoid = (int) $stoid;
        $count = 0;

        if (is_numeric($res ["rate_count"]) && is_numeric($res ["rnum_count"])) {
            $count = $res ["rnum_count"];
            $cur_votes = ($res ["rnum_count"] ? $res ["rate_count"] / $res ["rnum_count"] : 0);
        } else {
            $where = 'toid = ? AND type = ?';
            $count = db::o()->p($toid, $type)->count_rows("ratings", $where);
            $cur_votes = db::o()->p($toid, $type)->act_rows("ratings", "value", "avg", $where);
        }
        $this->min [$type] = (float) $this->min [$type];
        $this->max [$type] = (float) $this->max [$type];
        $this->part [$type] = (float) $this->part [$type];

        $disabled = $this->check_voted($owner, $toid, $stoid);
        $this->value_to_part($cur_votes);

        self::$count++;
        tpl::o()->assign('subratingid', 'cb' . self::$count . 'ce');
        tpl::o()->assign("rtoid", $toid);
        tpl::o()->assign("rtype", $type);
        tpl::o()->assign("total", $cur_votes);
        tpl::o()->assign("disabled", $disabled);
        tpl::o()->assign("count", $count);
        tpl::o()->assign("min", $this->min [$type]);
        tpl::o()->assign("loop", ($this->part [$type] ? ($this->max [$type] - $this->min [$type]) / $this->part [$type] + 1 : 1)); // section почему-то не хочет обрабатывать последнее значение
        tpl::o()->assign("per", $this->part [$type]);
        tpl::o()->assign("split", ($this->part [$type] ? ($this->part [$type] > 0 && $this->part [$type] <= 1 ? 1 / $this->part [$type] : 1) : 0));
        tpl::o()->assign("rating_inited", self::$inited);
        if (!self::$inited)
            self::$inited = true;
        tpl::o()->display("content/rating.tpl");
    }

    /**
     * Проверка голосования за контент
     * @param int $toid ID контента
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function content_check($toid, $value) {
        $q = db::o()->p($toid)->query('SELECT poster_id FROM content WHERE id = ? LIMIT 1');
        $res = db::o()->fetch_assoc($q);
        if (users::o()->v('id') == $res ["poster_id"])
            throw new EngineException("rating_cant_vote_for_your_content");
    }

    /**
     * Проверка на контент для кармы юзера
     * @param int $toid ID пользователя
     * @param int $stoid ID контента
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function susers_content_check($toid, $stoid, $value) {
        if (!db::o()->p($stoid, $toid)->count_rows('content', 'id=? AND poster_id=?'))
            throw new EngineException("rating_cant_vote_karma_for_content");
    }

    /**
     * Проверка голосования за пользователя
     * @param int $toid ID пользователя
     * @param float $value значение рейтинга
     * @return null
     * @throws EngineException 
     */
    protected function users_check($toid, $value) {
        if ($toid == users::o()->v('id'))
            throw new EngineException("rating_cant_vote_for_you");
    }

    /**
     * Кеш голосования за контент
     * @param int $toid ID контента
     * @param float $value значение рейтинга
     * @return null
     */
    protected function content_vote($toid, $value) {
        $one = 1;
        if ((string) $value == etc::reset_count)
            $one = etc::reset_count;
        /* @var $etc etc */
        $etc = n("etc");
        $etc->add_res(array(
            "rate" => $value,
            "rnum" => $one), null, 'content', $toid);
    }

    /**
     * Кеш голосования за пользователя
     * @param int $toid ID пользователя
     * @param float $value значение рейтинга
     * @return null
     */
    protected function users_vote($toid, $value) {
        /* @var $etc etc */
        $etc = n("etc");
        $etc->signed_res()->add_res(array("karma" => $value), null, 'users', $toid);
    }

    /**
     * Функция голосования
     * @param int $toid ID ресурса
     * @param float $value значение рейтинга
     * @param int $stoid доп. ID ресурса(только для проверки на то, голосовал ли)
     * @return bool true в случае успешного голосования
     * @throws EngineException 
     */
    public function vote($toid, $value, $stoid = 0) {
        if (!$this->state)
            return true;
        $type = $this->type;
        $stype = $this->stype;
        users::o()->check_perms('vote', 1, 2);
        lang::o()->get('rating');
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
        $insert ["ip"] = !users::o()->v();
        $insert ["user"] = users::o()->v() ? users::o()->v('id') : users::o()->get_ip();

        try {
            plugins::o()->pass_data(array('insert' => &$insert), true)->run_hook('rating_vote');
        } catch (PReturn $e) {
            return $e->r();
        }

        if ($value > $this->max [$type] || $value < $this->min [$type])
            throw new EngineException("rating_false");
        if ((floatval($this->part [$type]) ? ($value * 100) % ($this->part [$type] * 100) != 0 : $value != $this->min [$type] && $value != $this->max [$type]))
            throw new EngineException("rating_false");
        db::o()->no_error();
        db::o()->insert($insert, "ratings");
        if (db::o()->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException("rating_rated");
        $funct = $type . "_vote";
        $ret = $this->call_method($funct, array($toid, $value));
        return true;
    }

    /**
     * Получение суммы рейтинга(ч\с для кармы)
     * @param int $toid ID ресурса
     * @return int|float значение рейтинга
     */
    public function get_sum_rating($toid) {
        if (!$this->state)
            return 0;
        $type = $this->type;
        $toid = (int) $toid;
        return db::o()->p($type, $toid)->act_rows("ratings", "value", "sum", 'type = ? AND toid =?');
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
     * @param int $toid ID ресурса
     * @param int $del символов после запятой
     * @return float значение рейтинга
     */
    public function get_avg_rating($toid, $del = 1) {
        if (!$this->state)
            return 0;
        $type = $this->type;
        $toid = (int) $toid;
        $value = floatval(db::o()->p($type, $toid)->act_rows("ratings", "value", "avg", 'type = ? AND toid = ?'));
        $this->value_to_part($value);
        return number_format($value, ($del ? $del : 0), '.', '');
    }

    /**
     * Очистка голосов для ч/либо
     * @param int $id ID ч/либо
     * @return null
     */
    public function clear($id) {
        if (!$this->state)
            return;
        $id = (int) $id;
        $type = $this->type;
        $funct = $type . '_vote';
        $this->call_method($funct, array($id, etc::reset_count));
        db::o()->p($type, $id)->delete('ratings', 'WHERE type=? AND toid=?');
    }

}

?>