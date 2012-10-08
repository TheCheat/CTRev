<?php

/**
 * Project:            	CTRev
 * File:                class.polls.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Класс опросов системы
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class polls extends pluginable_object {

    /**
     * Статус системы опросов
     * @var bool $state
     */
    protected $state = true;

    /**
     * Цвета опросов
     * @var array $styles
     */
    protected $styles = array(
        "gray_votes",
        "blue_votes",
        "orange_votes");

    /**
     * Тип опросов
     * @var string $type
     */
    protected $type = 'torrents';

    /**
     * Допустимые типы
     * @var array $allowed_types
     */
    protected $allowed_types = array(
        'torrents');

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->state = (bool) config::o()->v('polls_on');
        $this->access_var('allowed_types', PVAR_ADD);
        $this->access_var('styles', PVAR_ADD);

        /**
         * @tutorial Добавление опроса(add_polls)
         * params:
         * int toid ID ресурса
         * string type тип ресурса
         * int pollid ID опроса 
         * bool full полностью загружать страницу с опросом?
         */
        tpl::o()->register_function("add_polls", array(
            $this,
            "add_form"));
        /**
         * @tutorial Отображение опроса(display_polls)
         * params:
         * int toid ID ресурса
         * string type тип ресурса
         * int pollid ID опроса
         * bool votes показывать результат опроса?
         * bool short показывать результаты опроса как в блоке?
         */
        tpl::o()->register_function("display_polls", array(
            $this,
            "display"));
    }

    /**
     * Вычисление процента голосов
     * @param int $x голоса за данный вариант
     * @param int $y всего голосов
     * @return int результат
     */
    public function make_percent($x, $y) {
        $r = 0;
        if ($y > 0)
            $r = round($x / $y * 100, 2);
        return $r;
    }

    /**
     * Изменение типа опросов
     * @param string $type тип опросов
     * @return polls $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Префильтер опроса
     * @param array $row массив данных опроса
     * @return null
     */
    public function prefilter($row) {
        $row ['answers'] = @unserialize($row ['answers']);
        $counts = array();
        $usernames = array();
        if (!config::o()->v('cache_pollvotes') || !($arr = cache::o()->read('polls/v-id' . $row['id']))) {
            $sum = 0;
            $ssum = 0;
            if ($row ['show_voted'] && users::o()->perm('votersview'))
                $cres = db::o()->query('SELECT pv.answers_id, u.username, u.group
                    FROM poll_votes AS pv
                    LEFT JOIN users AS u ON u.id=pv.user_id
                    WHERE pv.question_id =' . $row ["id"]);
            else
                $cres = db::o()->query('SELECT answers_id
                    FROM poll_votes
                    WHERE question_id =' . $row ["id"]);
            while ($currow = db::o()->fetch_assoc($cres)) {
                if ($currow ['username'])
                    $username = smarty_group_color_link($currow ['username'], $currow ['group']);
                else
                    $username = "";
                $answers = @unserialize($currow ['answers_id']);
                foreach ($answers as $id) {
                    $counts [$id]++;
                    if ($username)
                        $usernames [$id] .= ( $usernames [$id] ? ", " : "") . $username;
                    $sum++;
                }
                $ssum++;
            }
            $arr = array($counts, $usernames, $sum, $ssum);
            cache::o()->write($arr);
        } elseif (config::o()->v('cache_pollvotes'))
            list($counts, $usernames, $sum, $ssum) = $arr;
        $row ['voted_answers'] = @unserialize($row ['voted_answers']);
        $row ['answers_counts'] = $counts;
        $row ['usernames'] = $usernames;
        $row ['votes_count'] = $sum;
        $row ['votes_count_real'] = $ssum;
        return $row;
    }

    /**
     * Отображение опроса
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @param bool $votes показывать результат опроса?
     * @param bool $short показывать результаты опроса как в блоке?
     * @return null
     * @throws EngineException
     */
    public function display($toid = 0, $poll_id = 0, $votes = false, $short = false) {
        if (!$this->state) {
            //disabled();
            return;
        }
        lang::o()->get('polls');
        if (is_array($toid)) {
            if ($toid ['type'])
                $this->type = $toid ['type'];
            $poll_id = $toid ['poll_id'];
            $short = $toid ['short'];
            $votes = $toid ['votes'];
            $toid = $toid ['toid'];
        }
        $type = $this->type;
        if (!$type)
            $type = "torrents";
        $toid = (int) $toid;
        $poll_id = (int) $poll_id;
        $user_id = (int) users::o()->v('id');
        $user_ip = users::o()->get_ip();
        $where = ($poll_id || ($toid && $type)) ? ($poll_id ? 'p.id =' . $poll_id : 'p.toid =' . $toid . '
            AND p.type =' . db::o()->esc($type)) : 'p.toid =0';
        $limit = ($poll_id || ($toid && $type) || $short) ? 1 : null;
        $res = db::o()->query('SELECT p.*, pv.answers_id AS voted_answers FROM polls AS p
            LEFT JOIN poll_votes AS pv ON pv.question_id=p.id
            AND ' . ($user_id ? 'pv.user_id =' . $user_id : 'pv.user_id = 0 AND pv.user_ip =' . $user_ip) . '
            WHERE ' . $where . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        $count = db::o()->num_rows($res);
        if (!$count && $poll_id)
            throw new EngineException('polls_this_poll_not_exists');
        if (!$count && !$toid && !$short) {
            $var = lang::o()->v('polls_no_exists') . (users::o()->perm('polls', 3) ? ' ' . sprintf(lang::o()->v('polls_want_add'), furl::o()->construct("polls", array(
                                        'act' => 'add'))) : '');
            message($var, null, 'info', false);
            return;
        }
        if (!$count)
            return;
        tpl::o()->assign("votes_styles", $this->styles);
        tpl::o()->assign("styles_count", count($this->styles));
        tpl::o()->assign("show_voting", $votes);
        tpl::o()->assign("short_votes", $short);
        tpl::o()->assign("curtime", time());
        tpl::o()->assign("single_poll", ((bool) $poll_id));
        tpl::o()->register_modifier('polls_votepercent', array($this, 'make_percent'));
        tpl::o()->register_modifier('polls_prefilter', array($this, 'prefilter'));
        $multi_polls = !(($poll_id || ($toid && $type))) && !$short;
        if (!tpl::o()->displayed('polls/scripts.tpl'))
            tpl::o()->display('polls/scripts.tpl');
        if (!$multi_polls) {
            tpl::o()->assign("poll_row", db::o()->fetch_assoc($res));
            tpl::o()->display('polls/show_single.tpl');
        } elseif ($count) {
            tpl::o()->assign("poll_rows", db::o()->fetch2array($res));
            tpl::o()->display('polls/show_multi.tpl');
        }
    }

    /**
     * Форма добавления опроса
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @param bool $full полностью загружать страницу с опросом?
     * @return null
     */
    public function add_form($toid = 0, $poll_id = 0, $full = false) {
        if (!$this->state)
            return;
        if (is_array($toid)) {
            if ($toid ['type'])
                $this->type = $toid ['type'];
            $poll_id = $toid ['poll_id'];
            $full = $toid ['full'];
            $toid = $toid ['toid'];
        }
        $type = $this->type;
        $toid = (int) $toid;
        $poll_id = (int) $poll_id;
        if (!$type)
            $type = "torrents";
        $row ['answers'] = array(
            '',
            '');
        if (!$poll_id && (!$toid || !$type)) {
            users::o()->check_perms('polls', 3);
        } else {
            $row = db::o()->query('SELECT * FROM polls WHERE ' . (
                    $poll_id ? 'id =' . $poll_id : 'toid =' . $toid . ' AND type =' .
                            db::o()->esc($type)) . " LIMIT 1");
            $row = db::o()->fetch_assoc($row);
            if (!$row) {
                $row ['answers'] = array(
                    '',
                    '');
                users::o()->check_perms('polls', 2);
            } else {
                $row ['answers'] = @unserialize($row ['answers']);
                if ($row ['poster_id'] == users::o()->v('id'))
                    users::o()->check_perms('edit_polls');
                else
                    users::o()->check_perms('edit_polls', 2);
            }
        }
        lang::o()->get('polls');
        tpl::o()->assign('poll_row', $row);
        tpl::o()->assign('fully_page', $full);
        tpl::o()->display('polls/add_form.tpl');
    }

    /**
     * Сохранение опроса
     * @param array $data массив данных
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @return int ID опроса, в случае успешного завершения
     * @throws EngineException 
     */
    public function save($data, $toid = 0, $poll_id = 0) {
        if (!$this->state)
            return;
        $type = $this->type;
        lang::o()->get('polls');
        $cols = array('question',
            'max_votes',
            'poll_ends',
            'show_voted',
            'change_votes',
            'answers');
        extract(rex($data, $cols));
        $max_votes = (int) $max_votes;
        $poll_ends = (int) $poll_ends;
        $toid = (int) $toid;
        $poll_id = (int) $poll_id;
        if (!$type)
            $type = "torrents";
        if (!$poll_id && (!$toid || !$type)) {
            users::o()->check_perms('polls', 3);
        } else {
            $row = db::o()->query('SELECT id, poster_id, answers, question FROM polls
                WHERE ' . ($poll_id ? 'id =' .
                            $poll_id : 'toid =' .
                            $toid . ' AND type=' . db::o()->esc($type)) . " LIMIT 1");
            $row = db::o()->fetch_assoc($row);
            if (!$row)
                users::o()->check_perms('polls', 2);
            else {
                $poll_id = $row ['id'];
                if ($row ['poster_id'] == users::o()->v('id'))
                    users::o()->check_perms('edit_polls');
                else
                    users::o()->check_perms('edit_polls', 2);
            }
        }
        $show_voted = (bool) ($show_voted);
        $change_votes = (bool) ($change_votes);
        $answers = array_values(array_filter(array_map('trim', (array) $answers)));
        if (!$question || !$answers || !$max_votes || $poll_ends < 0)
            throw new EngineException('polls_areas_cant_be_empty', null, 0);
        if (count($answers) < 2)
            throw new EngineException('polls_so_few_answers', null, 1);
        $answers = serialize($answers);
        $update = array(
            'question' => $question,
            'answers' => $answers,
            'show_voted' => $show_voted,
            'change_votes' => $change_votes,
            'poll_ends' => $poll_ends,
            'max_votes' => $max_votes);
        if (!$poll_id) {
            $update['toid'] = $toid;
            $update['type'] = $type;
            $update['posted_time'] = time();
            $update['poster_id'] = users::o()->v('id');
            $id = db::o()->insert($update, 'polls');
        } else {
            $id = db::o()->update($update, 'polls', ('WHERE id =' . $poll_id . " LIMIT 1"));
            if ($row ['answers'] != $answers)
                db::o()->delete('poll_votes', ('WHERE question_id =' . $poll_id));
            if ($row ['answers'] != $answers || $row ['question'] != $question)
                log_add("edited_poll", "user", array($row ['question'], $id));
            $this->uncache($poll_id);
        }
        return $id;
    }

    /**
     * Удаление опроса
     * @param int $poll_id ID опроса
     * @return bool true, в случае успешного завершения
     * @throws EngineException 
     */
    public function delete($poll_id) {
        if (!$this->state)
            return;
        lang::o()->get('polls');
        $poll_id = (int) $poll_id;
        $poster = db::o()->query('SELECT poster_id, question FROM polls WHERE id =' . $poll_id . " LIMIT 1");
        $poster = db::o()->fetch_assoc($poster);
        if (!$poster)
            throw new EngineException("polls_not_exists");
        if ($poster ['poster_id'] == users::o()->v('id') && $poster ['poster_id'])
            users::o()->check_perms('del_polls');
        else
            users::o()->check_perms('del_polls', 2);
        db::o()->delete('polls', ('WHERE id =' . $poll_id . ' LIMIT 1'));
        db::o()->delete('poll_votes', ('WHERE question_id =' . $poll_id));
        log_add("deleted_poll", "user", array($poster ['question']));
        $this->uncache($poll_id);
        return true;
    }

    /**
     * Удаление всех опросов
     * @return null 
     */
    public function clear() {
        if (!$this->state)
            return;
        users::o()->check_perms('del_polls', 2);
        db::o()->truncate_table('polls');
        db::o()->truncate_table('poll_votes');
        log_add("cleared_polls", "user");
    }

    /**
     * Голосование в опросе
     * @param int $poll_id ID опроса
     * @param integer|array $answers ответы
     * @return bool true, в случае успешного завершения
     * @throws EngineException 
     */
    public function vote($poll_id, $answers) {
        if (!$this->state)
            return;
        users::o()->check_perms('polls', 1, 2);
        $poll_id = (int) $poll_id;
        if (!$answers)
            throw new EngineException('polls_so_much_votes');
        $answers = (array) (is_array($answers) ? array_map('intval', $answers) : array(
                    longval($answers)));
        $user_id = (int) users::o()->v('id');
        $user_ip = users::o()->get_ip();
        $day = 60 * 60 * 24;
        $row = db::o()->query('SELECT p.change_votes, p.max_votes, p.posted_time, p.poll_ends,
            pv.question_id, pv.user_ip, pv.user_id FROM polls AS p
            LEFT JOIN poll_votes AS pv ON pv.question_id=p.id AND ' .
                ($user_id ? 'pv.user_id =' . $user_id : 'pv.user_id=0 AND pv.user_ip =' . $user_ip) . '
            WHERE p.id =' . $poll_id . '
            LIMIT 1');
        $row = db::o()->fetch_assoc($row);
        if (!$row)
            throw new EngineException;
        if ((!$row ['change_votes'] || !users::o()->v()) && $row ['question_id'])
            throw new EngineException('polls_you_re_voted');
        if ($row ['max_votes'] < count($answers) || !$answers)
            throw new EngineException('polls_so_much_votes');
        if ($row ["poll_ends"])
            if (time() - $row ["posted_time"] > $row ["poll_ends"] * $day)
                throw new EngineException('polls_already_ends');
        if (!$row ['question_id'])
            db::o()->insert(array(
                'user_id' => $user_id,
                'answers_id' => serialize($answers),
                'question_id' => $poll_id,
                'user_ip' => $user_ip), 'poll_votes');
        else
            db::o()->update(array(
                'answers_id' => serialize($answers)), 'poll_votes', 'WHERE user_id = ' . $row["user_id"] . '
                    AND user_ip = ' . $row["user_ip"] . '
                    AND question_id=' . $row ['question_id'] . " LIMIT 1");
        $this->uncache($poll_id, true);
        return true;
    }

    /**
     * Очистка кеша
     * @param int $id ID опроса
     * @param bool $onvotes очистка при голосовании?
     * @return null 
     */
    protected function uncache($id, $onvotes = false) {
        if ($onvotes && !config::o()->v('clearonvote_pollcache'))
            return;
        cache::o()->remove('polls/v-id' . $id);
    }

}

?>