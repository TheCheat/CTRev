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
     * @var bool
     */
    protected $state = true;

    /**
     * Цвета опросов
     * @var array
     */
    protected $styles = array(
        "gray_votes",
        "blue_votes",
        "orange_votes");

    /**
     * Тип опросов
     * @var string
     */
    protected $type = 'torrents';

    /**
     * Допустимые типы
     * @var array
     */
    protected $allowed_types = array(
        'torrents');

    /**
     * Конструктор класса
     * @global config $config
     * @return null 
     */
    protected function plugin_construct() {
        global $config;
        $this->state = (bool) $config->v('polls_on');
        $this->access_var('allowed_types', PVAR_ADD);
        $this->access_var('styles', PVAR_ADD);
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
     * @global users $users
     * @global db $db
     * @global cache $cache
     * @global config $config
     * @param array $row массив данных опроса
     * @return null
     */
    public function prefilter($row) {
        global $users, $db, $cache, $config;
        $row ['answers'] = @unserialize($row ['answers']);
        $counts = array();
        $usernames = array();
        if (!$config->v('cache_pollvotes') || !($arr = $cache->read('polls/v-id' . $row['id']))) {
            $sum = 0;
            $ssum = 0;
            if ($row ['show_voted'] && $users->perm('votersview'))
                $cres = $db->query('SELECT pv.answers_id, u.username, u.group
                    FROM poll_votes AS pv
                    LEFT JOIN users AS u ON u.id=pv.user_id
                    WHERE pv.question_id =' . $row ["id"]);
            else
                $cres = $db->query('SELECT answers_id
                    FROM poll_votes
                    WHERE question_id =' . $row ["id"]);
            while ($currow = $db->fetch_assoc($cres)) {
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
            $cache->write($arr);
        } elseif ($config->v('cache_pollvotes'))
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
     * @global lang $lang
     * @global users $users
     * @global tpl $tpl
     * @global db $db
     * @global furl $furl
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @param bool $votes показывать результат опроса?
     * @param bool $short показывать результаты опроса как в блоке?
     * @return null
     * @throws EngineException
     */
    public function display($toid = 0, $poll_id = 0, $votes = false, $short = false) {
        global $lang, $users, $tpl, $db, $furl;
        if (!$this->state) {
            //disabled();
            return;
        }
        $lang->get('polls');
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
        $user_id = (int) $users->v('id');
        $user_ip = $users->get_ip();
        $where = ($poll_id || ($toid && $type)) ? ($poll_id ? 'p.id =' . $poll_id : 'p.toid =' . $toid . '
            AND p.type =' . $db->esc($type)) : 'p.toid =0';
        $limit = ($poll_id || ($toid && $type) || $short) ? 1 : null;
        $res = $db->query('SELECT p.*, pv.answers_id AS voted_answers FROM polls AS p
            LEFT JOIN poll_votes AS pv ON pv.question_id=p.id
            AND ' . ($user_id ? 'pv.user_id =' . $user_id : 'pv.user_id = 0 AND pv.user_ip =' . $user_ip) . '
            WHERE ' . $where . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        $count = $db->num_rows($res);
        if (!$count && $poll_id)
            throw new EngineException('polls_this_poll_not_exists');
        if (!$count && !$toid && !$short) {
            $var = $lang->v('polls_no_exists') . ($users->perm('polls', 3) ? ' ' . sprintf($lang->v('polls_want_add'), $furl->construct("polls", array(
                                        'act' => 'add'))) : '');
            mess($var, null, 'info', false);
            return;
        }
        if (!$count)
            return;
        $tpl->assign("votes_styles", $this->styles);
        $tpl->assign("styles_count", count($this->styles));
        $tpl->assign("show_voting", $votes);
        $tpl->assign("short_votes", $short);
        $tpl->assign("curtime", time());
        $tpl->assign("single_poll", ((bool) $poll_id));
        $tpl->register_modifier('polls_votepercent', array($this, 'make_percent'));
        $tpl->register_modifier('polls_prefilter', array($this, 'prefilter'));
        $multi_polls = !(($poll_id || ($toid && $type))) && !$short;
        if (!$tpl->displayed('polls/scripts.tpl'))
            $tpl->display('polls/scripts.tpl');
        if (!$multi_polls) {
            $tpl->assign("poll_row", $db->fetch_assoc($res));
            $tpl->display('polls/show_single.tpl');
        } elseif ($count) {
            $tpl->assign("poll_rows", $db->fetch2array($res));
            $tpl->display('polls/show_multi.tpl');
        }
    }

    /**
     * Форма добавления опроса
     * @global tpl $tpl
     * @global lang $lang
     * @global users $users
     * @global db $db
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @param bool $full полностью загружать страницу с опросом?
     * @return null
     */
    public function add_form($toid = 0, $poll_id = 0, $full = false) {
        global $tpl, $lang, $users, $db;
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
            $users->check_perms('polls', 3);
        } else {
            $row = $db->query('SELECT * FROM polls WHERE ' . (
                    $poll_id ? 'id =' . $poll_id : 'toid =' . $toid . ' AND type =' .
                            $db->esc($type)) . " LIMIT 1");
            $row = $db->fetch_assoc($row);
            if (!$row) {
                $row ['answers'] = array(
                    '',
                    '');
                $users->check_perms('polls', 2);
            } else {
                $row ['answers'] = @unserialize($row ['answers']);
                if ($row ['poster_id'] == $users->v('id'))
                    $users->check_perms('edit_polls');
                else
                    $users->check_perms('edit_polls', 2);
            }
        }
        $lang->get('polls');
        $tpl->assign('poll_row', $row);
        $tpl->assign('fully_page', $full);
        $tpl->display('polls/add_form.tpl');
    }

    /**
     * Сохранение опроса
     * @global lang $lang
     * @global db $db
     * @global users $users
     * @param array $data массив данных
     * @param int $toid ID ресурса
     * @param int $poll_id ID опроса
     * @return int ID опроса, в случае успешного завершения
     * @throws EngineException 
     */
    public function save($data, $toid = 0, $poll_id = 0) {
        global $lang, $db, $users;
        if (!$this->state)
            return;
        $type = $this->type;
        $lang->get('polls');
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
            $users->check_perms('polls', 3);
        } else {
            $row = $db->query('SELECT id, poster_id, answers, question FROM polls
                WHERE ' . ($poll_id ? 'id =' .
                            $poll_id : 'toid =' .
                            $toid . ' AND type=' . $db->esc($type)) . " LIMIT 1");
            $row = $db->fetch_assoc($row);
            if (!$row)
                $users->check_perms('polls', 2);
            else {
                $poll_id = $row ['id'];
                if ($row ['poster_id'] == $users->v('id'))
                    $users->check_perms('edit_polls');
                else
                    $users->check_perms('edit_polls', 2);
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
            $update['poster_id'] = $users->v('id');
            $id = $db->insert($update, 'polls');
        } else {
            $id = $db->update($update, 'polls', ('WHERE id =' . $poll_id . " LIMIT 1"));
            if ($row ['answers'] != $answers)
                $db->delete('poll_votes', ('WHERE question_id =' . $poll_id));
            if ($row ['answers'] != $answers || $row ['question'] != $question)
                log_add("edited_poll", "user", array($row ['question'], $id));
            $this->uncache($poll_id);
        }
        return $id;
    }

    /**
     * Удаление опроса
     * @global users $users
     * @global db $db
     * @global lang $lang
     * @param int $poll_id ID опроса
     * @return bool true, в случае успешного завершения
     * @throws EngineException 
     */
    public function delete($poll_id) {
        global $users, $db, $lang;
        if (!$this->state)
            return;
        $lang->get('polls');
        $poll_id = (int) $poll_id;
        $poster = $db->query('SELECT poster_id, question FROM polls WHERE id =' . $poll_id . " LIMIT 1");
        $poster = $db->fetch_assoc($poster);
        if (!$poster)
            throw new EngineException("polls_not_exists");
        if ($poster ['poster_id'] == $users->v('id') && $poster ['poster_id'])
            $users->check_perms('del_polls');
        else
            $users->check_perms('del_polls', 2);
        $db->delete('polls', ('WHERE id =' . $poll_id . ' LIMIT 1'));
        $db->delete('poll_votes', ('WHERE question_id =' . $poll_id));
        log_add("deleted_poll", "user", array($poster ['question']));
        $this->uncache($poll_id);
        return true;
    }

    /**
     * Удаление всех опросов
     * @return null 
     */
    public function clear() {
        global $users, $db;
        if (!$this->state)
            return;
        $users->check_perms('del_polls', 2);
        $db->truncate_table('polls');
        $db->truncate_table('poll_votes');
        log_add("cleared_polls", "user");
    }

    /**
     * Голосование в опросе
     * @global lang $lang
     * @global users $users
     * @global db $db
     * @param int $poll_id ID опроса
     * @param integer|array $answers ответы
     * @return bool true, в случае успешного завершения
     * @throws EngineException 
     */
    public function vote($poll_id, $answers) {
        global $users, $db;
        if (!$this->state)
            return;
        $users->check_perms('polls', 1, 2);
        $poll_id = (int) $poll_id;
        if (!$answers)
            throw new EngineException('polls_so_much_votes');
        $answers = (array) (is_array($answers) ? array_map('intval', $answers) : array(
                    longval($answers)));
        $user_id = (int) $users->v('id');
        $user_ip = $users->get_ip();
        $day = 60 * 60 * 24;
        $row = $db->query('SELECT p.change_votes, p.max_votes, p.posted_time, p.poll_ends,
            pv.question_id, pv.user_ip, pv.user_id FROM polls AS p
            LEFT JOIN poll_votes AS pv ON pv.question_id=p.id AND ' .
                ($user_id ? 'pv.user_id =' . $user_id : 'pv.user_id=0 AND pv.user_ip =' . $user_ip) . '
            WHERE p.id =' . $poll_id . '
            LIMIT 1');
        $row = $db->fetch_assoc($row);
        if (!$row)
            throw new EngineException;
        if ((!$row ['change_votes'] || !$users->v()) && $row ['question_id'])
            throw new EngineException('polls_you_re_voted');
        if ($row ['max_votes'] < count($answers) || !$answers)
            throw new EngineException('polls_so_much_votes');
        if ($row ["poll_ends"])
            if (time() - $row ["posted_time"] > $row ["poll_ends"] * $day)
                throw new EngineException('polls_already_ends');
        if (!$row ['question_id'])
            $db->insert(array(
                'user_id' => $user_id,
                'answers_id' => serialize($answers),
                'question_id' => $poll_id,
                'user_ip' => $user_ip), 'poll_votes');
        else
            $db->update(array(
                'answers_id' => serialize($answers)), 'poll_votes', 'WHERE user_id = ' . $row["user_id"] . '
                    AND user_ip = ' . $row["user_ip"] . '
                    AND question_id=' . $row ['question_id'] . " LIMIT 1");
        $this->uncache($poll_id, true);
        return true;
    }

    /**
     * Очистка кеша
     * @global cache $cache
     * @global config $config
     * @param int $id ID опроса
     * @param bool $onvotes очистка при голосовании?
     * @return null 
     */
    protected function uncache($id, $onvotes = false) {
        global $cache, $config;
        if ($onvotes && !$config->v('clearonvote_pollcache'))
            return;
        $cache->remove('polls/v-id' . $id);
    }

}

?>