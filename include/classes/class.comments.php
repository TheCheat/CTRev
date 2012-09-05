<?php

/**
 * Project:            	CTRev
 * File:                class.comments.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс комментариев
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class comments extends pluginable_object {

    /**
     * Статус системы комментариев
     * @var bool
     */
    protected $state = true;

    /**
     * Столбцы с заголовками для таблиц
     * @var array
     */
    protected $title_cols = array(
        'torrents' => 'title',
        'users' => 'username');

    /**
     * Тип комментариев
     * @var string
     */
    protected $type = 'torrents';

    /**
     * Допустимые типы
     * @var array
     */
    protected $allowed_types = array(
        'torrents',
        'users');

    /**
     * Конструктор класса
     * @global config $config
     * @return null 
     */
    protected function plugin_construct() {
        global $config;
        $this->state = (bool) $config->v('comments_on');
        $this->access_var('title_cols', PVAR_ADD);
        $this->access_var('allowed_types', PVAR_ADD);
    }

    /**
     * Изменение типа комментариев
     * @param string $type тип комментариев
     * @return comments $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Функция отображения комментариев
     * нек. параметры: GET['cid'] - ID желаемого комментария, GET['comments_page'] - страница комментариев
     * @global db $db
     * @global tpl $tpl
     * @global lang $lang
     * @global users $users
     * @global config $config
     * @global display $display
     * @param int $resid ID ресурса
     * @param string $name имя формы
     * @param bool $no_form без формы добавления
     * @return null
     */
    public function display($resid, $name = "comment", $no_form = false) {
        global $db, $tpl, $lang, $users, $config, $display;
        if (!$this->state) {
            //disabled();
            return;
        }
        if (is_array($resid)) {
            if ($resid ["type"])
                $this->change_type($resid ["type"]);
            $name = $resid ["name"];
            if ($resid ["no_form"])
                $no_form = (bool) $resid ["no_form"];
            $resid = $resid ["resid"];
        }
        $type = $this->type;
        $lang->get("comments");
        if (!$users->perm('comment')) {
            mess('comment_you_cannt_view', null, 'error', false);
            return;
        }
        if (!longval($resid))
            return;
        $type = $type ? $type : "torrents";
        $name = $name ? $name : "comment";
        $where = 'toid =' . $resid . ' AND type =' . $db->esc($type);
        $cid = (int) $_GET ['cid'];
        $orderby = "posted_time asc";
        $count = $db->count_rows("comments", $where);
        $perpage = $config->v('comm_perpage');
        if ($cid) {
            $pos = $db->get_current_pos("comments", $where, 'id', $cid, $orderby);
            $page = longval($pos / $perpage) + 1;
            $_GET ["comments_page"] = $page;
        }
        list ( $pages, $limit ) = $display->pages($count, $perpage, 'change_page_comments', 'comments_page', '', true);
        $comments = $db->query('SELECT c.*, u.username,
            u.group, u.settings, u.avatar FROM comments AS c
            LEFT JOIN users AS u ON u.id=c.poster_id
            WHERE ' . $where . ' ORDER BY ' . $orderby . ' LIMIT ' . $limit);
        $tpl->assign("comments", $db->fetch2array($comments));
        $tpl->assign("name", $name);
        $tpl->assign("resid", $resid);
        $tpl->assign("type", $type);
        $tpl->assign("pages", $pages);
        $tpl->display("comments/index.tpl");
        if (!$no_form)
            $this->add_form($resid, $name);
    }

    /**
     * Форма добавления\редактирования комментария
     * @global tpl $tpl
     * @global lang $lang
     * @global users $users
     * @param int $resid ID ресурса
     * @param string $name название формы добавления
     * @param int $id ID комментария
     * @return null
     */
    public function add_form($resid = "", $name = "comment", $id = "") {
        global $tpl, $lang, $users;
        if (!$this->state)
            return;
        $type = $this->type;
        $lang->get("comments");
        if (!$users->perm('comment', 2)) {
            mess('comment_you_cannt_add', null, 'error', false);
            return;
        }
        if ((!longval($resid) && $resid) || (!longval($id) && $id) || (!$id && !$resid))
            return;
        if ($id)
            $tpl->assign("no_js_comm", true);
        $type = $type ? $type : "torrents";
        $name = $name ? $name : "comment";
        $tpl->assign("name", $name);
        $tpl->assign("type", $type);
        $tpl->assign("resid", longval($resid));
        $tpl->display("comments/add.tpl");
    }

    /**
     * Функция удаления комментария
     * @global lang $lang
     * @global users $users
     * @global db $db
     * @global etc $etc
     * @param int $id ID комментария
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        global $lang, $users, $db, $etc;
        if (!$this->state)
            return;
        $lang->get('comments');
        $id = longval($id);
        $poster = $db->fetch_assoc($db->query('SELECT poster_id, type, toid
            FROM comments WHERE id=' . $id . ' LIMIT 1'));
        if (!$poster)
            throw new EngineException("comment_was_deleted");
        if ($poster ['poster_id'] == $users->v('id') ||
                ($poster['type'] == 'users' && $poster['toid'] == $users->v('id')))
            $users->check_perms('del_comm');
        else
            $users->check_perms('del_comm', 2);
        $db->delete("comments", 'WHERE id = ' . $id . ' LIMIT 1');
        $etc->add_res('comm', - 1, '', $poster ['poster_id']);
        $db->no_error();
        $etc->add_res('comm', - 1, $poster ['type'], $poster ['toid']);
        log_add("deleted_comment", "user", array($id));
    }

    /**
     * Очистка комментариев в ресурсе
     * @global users $users
     * @global db $db
     * @param int $toid ID ресурса
     * @param bool $all очистка ВСЕХ комментариев?
     * @return null 
     */
    public function clear($toid, $all = false) {
        global $users, $db;
        if (!$this->state)
            return;
        $toid = (int) $toid;
        $users->check_perms('del_comm', '2');
        $vars = null;
        if ($all)
            $db->truncate_table('comments');
        else {
            $db->delete('comments', 'WHERE toid=' . $toid . ' AND type=' . $db->esc($this->type));
            $vars = array($this->type, $toid);
        }
        log_add("cleared_comments", "user", $vars);
    }

    /**
     * Функция получения контента цитируемого комментария
     * @global users $users
     * @global db $db
     * @global bbcodes $bbcodes
     * @param int $id ID комментария
     * @return string тело комментария
     */
    public function quote($id) {
        global $users, $db, $bbcodes;
        if (!$this->state)
            return;
        $users->check_perms('comment', 2, 2);
        $id = longval($id);
        $comment = $db->fetch_assoc($db->query('SELECT text FROM comments WHERE id =' . $id . " LIMIT 1"));
        return $bbcodes->format_text($comment['text'], "QUOTE");
    }

    /**
     * Функция сохранения комментария
     * @global lang $lang
     * @global db $db
     * @global users $users
     * @global captcha $captcha
     * @global etc $etc
     * @global config $config
     * @global mailer $mailer
     * @global plugins $plugins
     * @param string $title заголовок комментария
     * @param string $content содержание комментария
     * @param int $resid ID ресурса
     * @param int $id ID комментария
     * @return bool true в случае успешного сохранения комментария
     * @throws EngineException 
     */
    public function save($title, $content, $resid = "", $id = "") {
        global $lang, $db, $users, $captcha, $etc, $config, $mailer, $plugins;
        if (!$this->state)
            return;
        $type = $this->type;
        $lang->get('comments');
        $id = longval($id);
        if (!$id) {
            $users->check_perms('comment', 2, 2);
            if (!$users->v()) {
                $error = array();
                ref($captcha)->check($error);
                if ($error)
                    return implode("\n", $error);
            }
        } else {
            $poster = $db->fetch_assoc($db->query('SELECT poster_id FROM comments
                WHERE id = ' . $id . ' LIMIT 1'));
            if (!$poster)
                throw new EngineException('comment_was_deleted');
            if ($poster ['poster_id'] == $users->v('id'))
                $users->check_perms('edit_comm');
            else
                $users->check_perms('edit_comm', 2);
        }
        $type = $type ? $type : "torrents";
        $title = trim($title);
        $content = trim($content);
        $poster = ($users->v('id') ? $users->v('id') : - 1);
        if ((!longval($resid) && $resid) || (!longval($id) && $id) || (!$id && !$resid))
            throw new EngineException('comment_wrong_data');
        if (!$content || mb_strlen($content) < $config->v('min_comm_symb'))
            throw new EngineException('comment_small_text');
        if (preg_match('/^(\s*' . trim($lang->v('comment_re')) . '\s*)+$/siu', $title))
            $title = "";
        if (!$id)
            anti_flood('comments', 'toid=' . $resid . ' AND type=' . $db->esc($type), array('poster_id',
                'edited_time'));
        $upd = array(
            "subject" => $title);
        if (!$id) {
            $id = $this->check_double_comment($resid, $content);
            if ($id) {
                $upd["edited_time"] = time();
                unset($upd["subject"]);
            }
        }
        $upd["text"] = $content;
        
        try {
            $plugins->pass_data(array('update' => &$upd), true)->run_hook('comments_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        
        if (!$id) {
            $upd = array_merge($upd, array("posted_time" => time(),
                "edited_time" => time(),
                "poster_id" => $poster,
                "toid" => $resid,
                "type" => $type));
            $db->insert($upd, "comments");
            $etc->add_res('comm');
            $db->no_error();
            $etc->add_res('comm', 1, $type, $resid);
            $mailer->change_type($type)->update($resid);
        } else {
            $db->update($upd, "comments", 'WHERE id =' . $id . ' LIMIT 1');
            log_add("edited_comment", "user", array($id));
        }
        return true;
    }

    /**
     * Проверка двойного коммментария
     * @global db $db
     * @global config $config
     * @global users $users
     * @param int $resid ID ресурса
     * @param string $content текст комментария
     * @return int ID повторяющегося комментария
     */
    public function check_double_comment($resid, &$content = null) {
        global $db, $config, $users;
        if (!$this->state)
            return;
        $type = $this->type;
        if (!$config->v('dc_prevent') || !$users->v('id'))
            return null;
        $resid = (int) $resid;
        $ret = $db->fetch_assoc($db->query('SELECT id, poster_id, text, edited_time FROM comments
            WHERE toid=' . $resid . ' AND type=' . $db->esc($type) . '
            ' . ($config->v('dc_maxtime') ? ' AND edited_time>=' . (time() - $config->v('dc_maxtime')) : "") . '
            ORDER BY edited_time DESC LIMIT 1'));
        if ($users->v('id') == $ret["poster_id"]) {
            $dc_text = $this->parse_dc_text($config->v('dc_text'), $ret["edited_time"]);
            $content = $ret["text"] . "\n" . $dc_text . "\n" . $content;
            return $ret["id"];
        }
        return 0;
    }

    /**
     * Замена переменных %time% и %time_after% в Anti-Double Comment тексте
     * @global display $display
     * @param string $dc_text текст для парсинга
     * @param int $fromtime время постинга пред. комментария
     * @return string спарсенный текст
     */
    public function parse_dc_text($dc_text, $fromtime) {
        global $display;
        $dc_text = str_replace('%time%', $display->date(time(), 'ymdhis'), $dc_text);
        if ($fromtime)
            $dc_text = str_replace('%time_after%', $display->get_estimated_time($fromtime, time()), $dc_text);
        return $dc_text;
    }

    /**
     * Отображение комментариев пользователя/пользователей
     * @global db $db
     * @global tpl $tpl
     * @global config $config
     * @global lang $lang
     * @param int $id ID пользователя
     * @param string $where доп. условие
     * @return null
     */
    public function usertable($id = null, $where = null) {
        global $db, $tpl, $config, $lang;
        $lang->get('profile');
        if (!$this->state) {
            disabled();
            return false;
        }
        $id = (int) $id;
        $select = "c.id,c.posted_time,c.type,c.toid,c.subject";
        if (!$id)
            $select .= ",c.poster_id";
        $where = ($id ? 'c.poster_id=' . $id : ($where ? $where : ''));
        $comm_row = $db->query('SELECT ' . $select . (!$id ? ",u.username,u.group" : "") . '
            FROM comments AS c
            ' . (!$id ? 'LEFT JOIN users AS u ON c.poster_id=u.id' : '') . '
            ' . ($where ? " WHERE " . $where : "") . '
            ORDER BY c.posted_time DESC
            LIMIT ' . $config->v('last_profile_comments'));
        $cr = array();
        while ($rows = $db->fetch_assoc($comm_row)) {
            $res = $db->query('SELECT ' . $this->title_cols[$rows ["type"]] . ' AS title
                FROM ' . $rows ["type"] . ' WHERE id=' . $rows ["toid"] . ' LIMIT 1');
            $res = $db->fetch_assoc($res);
            $rows ["title"] = $res ["title"];
            $cr [] = $rows;
        }
        $tpl->assign("comm_row", $cr);
        $tpl->display("profile/last_comments.tpl");
    }

}