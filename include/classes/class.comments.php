<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.comments.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var bool $state
     */
    protected $state = true;

    /**
     * Столбцы с заголовками для таблиц
     * @var array $title_cols
     */
    protected $title_cols = array(
        'content' => 'title',
        'users' => 'username');

    /**
     * Тип комментариев
     * @var string $type
     */
    protected $type = 'content';

    /**
     * Допустимые типы
     * @var array $allowed_types
     */
    protected $allowed_types = array(
        'content',
        'users');

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->state = (bool) config::o()->mstate('comments_manage');
        $this->access_var('title_cols', PVAR_ADD);
        $this->access_var('allowed_types', PVAR_ADD);
        /**
         * @note Отображение комментариев(display_comments)
         * params: 
         * int toid ID ресурса
         * string type тип ресурса
         * string name имя формы
         * bool no_form без формы добавления
         */
        tpl::o()->register_function("display_comments", array(
            $this,
            'display'));
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
     * @param int $toid ID ресурса
     * @param string $name имя формы
     * @param bool $no_form без формы добавления
     * @return null
     */
    public function display($toid, $name = "comment", $no_form = false) {
        if (!$this->state) {
            //disabled(false);
            return;
        }
        if (is_array($toid)) {
            if ($toid ["type"])
                $this->change_type($toid ["type"]);
            $name = $toid ["name"];
            if ($toid ["no_form"])
                $no_form = (bool) $toid ["no_form"];
            $toid = $toid ["toid"];
        }
        $type = $this->type;
        lang::o()->get("comments");
        if (!users::o()->perm('comment')) {
            n("message")->stype("error")->info('comment_you_cannt_view');
            return;
        }
        if (!longval($toid))
            return;
        $name = $name ? $name : "comment";
        $params = array($toid, $type);
        $where = 'toid = ? AND type = ?';
        $cid = (int) $_GET ['cid'];
        $orderby = "posted_time asc";
        $count = db::o()->p($params)->count_rows("comments", $where);
        $perpage = config::o()->v('comm_perpage');
        if ($cid) {
            $pos = db::o()->p($params)->get_current_pos("comments", $where, 'id', $cid, $orderby);
            $page = longval($pos / $perpage) + 1;
            $_GET ["comments_page"] = $page;
        } elseif (!$page)
            $page = intval($count / $perpage) + 1; // на последнюю страницу

        list ( $pages, $limit ) = display::o()->pages($count, $perpage, 'change_page_comments', 'comments_page', '', true);
        $comments = db::o()->p($params)->query('SELECT c.*, u.username,
            u.group, u.settings, u.avatar FROM comments AS c
            LEFT JOIN users AS u ON u.id=c.poster_id
            WHERE ' . $where . ' ORDER BY ' . $orderby . ' LIMIT ' . $limit);
        tpl::o()->assign("comments", db::o()->fetch2array($comments));
        tpl::o()->assign("name", $name);
        tpl::o()->assign("toid", $toid);
        tpl::o()->assign("type", $type);
        tpl::o()->assign("pages", $pages);
        tpl::o()->display("comments/index.tpl");
        if (!$no_form)
            $this->add($toid, $name);
    }

    /**
     * Форма добавления/редактирования комментария
     * @param int $resid ID ресурса
     * @param string $name название формы добавления
     * @param int $id ID комментария
     * @return null
     */
    public function add($resid = "", $name = "comment", $id = "") {
        if (!$this->state)
            return;
        $type = $this->type;
        lang::o()->get("comments");
        if (!users::o()->perm('comment', 2)) {
            //n("message")->stype("error")->info('comment_you_cannt_add');
            return;
        }
        if ((!longval($resid) && $resid) || (!longval($id) && $id) || (!$id && !$resid))
            return;
        if ($id)
            tpl::o()->assign("no_js_comm", true);
        $name = $name ? $name : "comment";
        tpl::o()->assign("name", $name);
        tpl::o()->assign("type", $type);
        tpl::o()->assign("resid", longval($resid));
        tpl::o()->display("comments/add.tpl");
    }

    /**
     * Функция удаления комментария
     * @param int $id ID комментария
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        if (!$this->state)
            return;
        /* @var $etc etc */
        $etc = n("etc");
        lang::o()->get('comments');
        $id = longval($id);
        $q = db::o()->p($id)->query('SELECT poster_id, type, toid FROM comments WHERE id=? LIMIT 1');
        $poster = db::o()->fetch_assoc($q);
        if (!$poster)
            throw new EngineException("comment_was_deleted");
        if ($poster ['poster_id'] == users::o()->v('id') ||
                ($poster['type'] == 'users' && $poster['toid'] == users::o()->v('id')))
            users::o()->check_perms('del_comm');
        else
            users::o()->check_perms('del_comm', 2);
        db::o()->p($id)->delete("comments", 'WHERE id = ? LIMIT 1');
        $etc->add_res('comm', - 1, '', $poster ['poster_id']);
        db::o()->no_error();
        $etc->add_res('comm', - 1, $poster ['type'], $poster ['toid']);
        log_add("deleted_comment", "user", array($id));
    }

    /**
     * Очистка комментариев в ресурсе
     * @param int $toid ID ресурса
     * @param bool $all очистка ВСЕХ комментариев?
     * @return null 
     */
    public function clear($toid, $all = false) {
        if (!$this->state)
            return;
        $toid = (int) $toid;
        users::o()->check_perms('del_comm', '2');
        $vars = null;
        if ($all) {
            db::o()->truncate_table('comments');
            foreach ($this->allowed_types as $table)
                db::o()->update(array('comm_count' => 0), $table);
        } else {
            /* @var $etc etc */
            $etc = n("etc");
            $etc->add_res('comm', etc::reset_count, $this->type, $toid);
            db::o()->p($toid, $this->type)->delete('comments', 'WHERE toid=? AND type=?');
            $vars = array($this->type, $toid);
        }
        log_add("cleared_comments", "user", $vars);
    }

    /**
     * Функция получения контента цитируемого комментария
     * @param int $id ID комментария
     * @return string тело комментария
     */
    public function quote($id) {
        if (!$this->state)
            return;
        users::o()->check_perms('comment', 2, 2);
        $id = longval($id);
        $q = db::o()->p($id)->query('SELECT text FROM comments WHERE id = ? LIMIT 1');
        $comment = db::o()->fetch_assoc($q);
        return bbcodes::o()->format_text($comment['text'], "QUOTE");
    }

    /**
     * Функция сохранения комментария
     * @param string $content содержание комментария
     * @param int $resid ID ресурса
     * @param int $id ID комментария
     * @return bool true в случае успешного сохранения комментария
     * @throws EngineException 
     */
    public function save($content, $resid = "", $id = "") {
        if (!$this->state)
            return;
        $type = $this->type;
        lang::o()->get('comments');
        $id = longval($id);
        /* @var $etc etc */
        $etc = n("etc");
        if (!$id) {
            users::o()->check_perms('comment', 2, 2);
            if (!users::o()->v()) {
                $error = array();
                n("captcha")->check($error);
                if ($error)
                    return implode("\n", $error);
            }
        } else {
            $q = db::o()->p($id)->query('SELECT poster_id FROM comments WHERE id = ? LIMIT 1');
            $poster = db::o()->fetch_assoc($q);
            if (!$poster)
                throw new EngineException('comment_was_deleted');
            if ($poster ['poster_id'] == users::o()->v('id'))
                users::o()->check_perms('edit_comm');
            else
                users::o()->check_perms('edit_comm', 2);
        }
        $content = trim($content);
        $poster = (users::o()->v('id') ? users::o()->v('id') : - 1);
        if ((!longval($resid) && $resid) || (!longval($id) && $id) || (!$id && !$resid))
            throw new EngineException('comment_wrong_data');
        if (!$content || mb_strlen($content) < config::o()->v('min_comm_symb'))
            throw new EngineException('comment_small_text');
        if (!$id)
            $etc->anti_flood('comments', 'toid=' . $resid . ' AND type=' . db::o()->esc($type), array('poster_id',
                'edited_time'));
        $upd = array();
        if (!$id) {
            $id = $this->check_double_comment($resid, $content);
            if ($id) {
                $upd["edited_time"] = time();
                unset($upd["subject"]);
            }
        }
        $upd["text"] = $content;

        try {
            plugins::o()->pass_data(array('update' => &$upd,
                "id" => $id), true)->run_hook('comments_save');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$id) {
            $upd = array_merge($upd, array("posted_time" => time(),
                "edited_time" => time(),
                "poster_id" => $poster,
                "toid" => $resid,
                "type" => $type));
            db::o()->insert($upd, "comments");
            $etc->add_res('comm');
            db::o()->no_error();
            $etc->add_res('comm', 1, $type, $resid);
            /* @var $mailer mailer */
            $mailer = n("mailer");
            $mailer->change_type($type)->update($resid);
        } else {
            db::o()->p($id)->update($upd, "comments", 'WHERE id = ? LIMIT 1');
            log_add("edited_comment", "user", array($id));
        }
        return true;
    }

    /**
     * Проверка двойного коммментария
     * @param int $resid ID ресурса
     * @param string $content текст комментария
     * @return int ID повторяющегося комментария
     */
    public function check_double_comment($resid, &$content = null) {
        if (!$this->state)
            return;
        $type = $this->type;
        if (!config::o()->v('dc_prevent') || !users::o()->v('id'))
            return null;
        $resid = (int) $resid;
        $et = time() - config::o()->v('dc_maxtime');
        $where = 'toid=? AND type=?' . (config::o()->v('dc_maxtime') ? ' AND edited_time>=?' : "");
        $q = db::o()->p($resid, $type, $et)->query('SELECT id, poster_id, text, edited_time FROM comments
            WHERE ' . $where . ' ORDER BY edited_time DESC LIMIT 1');
        $ret = db::o()->fetch_assoc($q);
        if (users::o()->v('id') == $ret["poster_id"]) {
            $dc_text = $this->parse_dc_text(config::o()->v('dc_text'), $ret["edited_time"]);
            $content = $ret["text"] . "\n" . $dc_text . "\n" . $content;
            return $ret["id"];
        }
        return 0;
    }

    /**
     * Замена переменных %time% и %time_after% в Anti-Double Comment тексте
     * @param string $dc_text текст для парсинга
     * @param int $fromtime время постинга пред. комментария
     * @return string спарсенный текст
     */
    public function parse_dc_text($dc_text, $fromtime) {
        $dc_text = str_replace('%time%', display::o()->date(time(), 'ymdhis'), $dc_text);
        if ($fromtime)
            $dc_text = str_replace('%time_after%', display::o()->estimated_time($fromtime, time()), $dc_text);
        return $dc_text;
    }

    /**
     * Отображение комментариев пользователя/пользователей
     * @param int $id ID пользователя
     * @param string $where доп. условие
     * @return null
     */
    public function usertable($id = null, $where = null) {
        lang::o()->get('profile');
        if (!$this->state) {
            disabled(false);
            return false;
        }
        if (!users::o()->perm('comment')) {
            n("message")->stype("error")->info('comment_you_cannt_view');
            return;
        }
        $id = (int) $id;
        $select = "c.id,c.posted_time,c.type,c.toid";
        if (!$id)
            $select .= ",c.poster_id";
        $where = ($id ? 'c.poster_id=' . $id : ($where ? $where : ''));
        $comm_row = db::o()->no_parse()->query('SELECT ' . $select . (!$id ? ",u.username,u.group" : "") . '
            FROM ' . db::table('comments') . ' AS c
            ' . (!$id ? 'LEFT JOIN ' . db::table('users') . ' AS u ON c.poster_id=u.id' : '') . '
            ' . ($where ? " WHERE " . $where : "") . '
            ORDER BY c.posted_time DESC
            LIMIT ' . config::o()->v('last_profile_comments'));
        $cr = array();
        while ($rows = db::o()->fetch_assoc($comm_row)) {
            $res = db::o()->p($rows ["toid"])->query('SELECT ' . $this->title_cols[$rows ["type"]] . ' AS title
                FROM ' . $rows ["type"] . ' WHERE id=? LIMIT 1');
            $res = db::o()->fetch_assoc($res);
            $rows ["title"] = $res ["title"];
            $cr [] = $rows;
        }
        tpl::o()->assign("comm_row", $cr);
        tpl::o()->display("profile/last_comments.tpl");
    }

}