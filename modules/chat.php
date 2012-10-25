<?php

/**
 * Project:             CTRev
 * @file                modules/chat.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Чат
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class chat {

    /**
     * Инициализация Ajax-части чата
     * @return null
     */
    public function init() {
        lang::o()->get("blocks/chat");
        $id = (int) $_GET['id'];
        switch ($_GET["act"]) {
            case "text":
                $this->get_text($id);
                die();
                break;
            case "delete":
                $this->delete($id);
                die("OK!");
                break;
            case "truncate":
                $this->truncate();
                print(lang::o()->v('chat_no_messages'));
                die();
                break;
            case "save":
                $this->save($_POST['text'], $id);
                die("OK!");
                break;
            default:
                $this->show((int) $_GET['time'], (bool) $_GET['prev']);
                break;
        }
    }

    /**
     * Удаление сообщения из чата
     * @param int $id ID сообщения
     * @return null
     * @throws EngineException
     */
    public function delete($id) {
        $id = (int) $id;
        check_formkey();
        users::o()->check_perms('del_chat');
        db::o()->delete('chat', 'WHERE id=' . $id .
                (!users::o()->perm('del_chat', 2) ? " AND poster_id=" . users::o()->v('id') : "") . ' LIMIT 1');
        $this->delete_logs($id);
    }

    /**
     * Запись в логи чата
     * @param int $id ID сообщения
     * @return null 
     */
    protected function delete_logs($id = 0) {
        $id = (int) $id;
        if (!db::o()->affected_rows() || !config::o()->v('chat_clearlogs'))
            return;
        db::o()->delete('chat_deleted', 'WHERE time<=' . (time() - config::o()->v('chat_clearlogs')));
        db::o()->insert(array('id' => $id, 'time' => time()), 'chat_deleted');
        if (!$id && db::o()->affected_rows() < 1)
            db::o()->update(array('time' => time()), 'chat_deleted', 'WHERE id="' . $id . '" LIMIT 1');
    }

    /**
     * Очистка сообщений чата
     * @return null
     * @throws EngineException
     */
    public function truncate() {
        check_formkey();
        users::o()->check_perms('del_chat', 3);
        db::o()->truncate_table('chat');
        $this->delete_logs();
    }

    /**
     * Сохранение сообщения чата
     * @param string $text
     * @param int $id ID сообщения
     * @return null
     * @throws EngineException
     */
    public function save($text, $id = null) {
        check_formkey();
        $text = trim($text);
        if (!$text)
            return;
        $id = (int) $id;
        if (!$id)
            users::o()->check_perms('chat', 2, 2);
        else
            users::o()->check_perms('edit_chat');
        $update = array();
        $update["text"] = $text;
        $update["edited_time"] = time();

        try {
            plugins::o()->pass_data(array('update' => &$update,
                'id' => $id), true)->run_hook('chat_save');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$id) {
            $update["poster_id"] = users::o()->v('id') ? users::o()->v('id') : -1;
            $update["posted_time"] = time();
            db::o()->delete("chat_deleted", "WHERE id=0");
            db::o()->insert($update, 'chat');
        } else
            db::o()->update($update, 'chat', 'WHERE id=' . $id .
                    (!users::o()->perm('edit_chat', 2) ? " AND poster_id=" . users::o()->v('id') : "") . ' LIMIT 1');
    }

    /**
     * Вывод неформатированного текста сообщения для редактирования
     * @param int $id ID сообщения
     * @return null
     */
    protected function get_text($id) {
        users::o()->check_perms('edit_chat');
        $id = (int) $id;
        list ($text) = db::o()->fetch_row(db::o()->query("SELECT text FROM chat WHERE id=" . $id . ' LIMIT 1'));
        print($text);
    }

    /**
     * Форматирование сообщения
     * @param array $row массив данных сообщения
     * @return null
     */
    public function chat_mf(&$row) {
        $t = $row["text"];
        $cmds = 'private|me|hello|bye';
        preg_match('/^\/(' . $cmds . ')\s*(?:\((.+)(?:,\s*([0-9]+))?\)\s+)?(.*)$/siu', $t, $matches);
        if (!$matches)
            return;
        list(, $cmd, $p1, $p2, $t) = $matches;
        $u = null;
        switch ($cmd) {
            case "private":
                if (!users::o()->check_login($p1))
                    return;
                if (!$row['poster_id'] || !$p1 || mb_strtolower($row['username']) == mb_strtolower($p1))
                    return;
                if (!users::o()->perm('chat_sprivate') &&
                        users::o()->v('username_lower') != mb_strtolower($p1) &&
                        users::o()->v('id') != $row['poster_id']) {
                    $row["text"] = '';
                    return;
                }
                if (users::o()->v('id') == $row['poster_id']) {
                    $cmd = 'fprivate';
                    $u = $p1;
                }
            case "me":
                if (!$u)
                    $u = smarty_group_color_link($row["username"], $row["group"]);
                $row["text"] = sprintf(lang::o()->v("chat_" . $cmd . "_saying"), $u, $t);
                break;
            case "hello":
            case "bye":
                $row["text"] = sprintf(lang::o()->v("chat_" . $cmd . "_saying"), smarty_group_color_link($row["username"], $row["group"]));
                break;
            default:
                try {
                    plugins::o()->pass_data(array('row' => &$row,
                        'matches' => $matches), true)->run_hook('chat_formatting');
                } catch (PReturn $e) {
                    return $e->r();
                }
                break;
        }
        $row["spec"] = true;
    }

    /**
     * Вывод сообщений чата
     * @param int $time время последней проверки или ID сообщения
     * @param bool $prev показать пред. сообщения, до этого ID
     * @return null
     */
    protected function show($time, $prev = false) {
        $time = (int) $time;
        users::o()->check_perms('chat', 2, 2);
        if ($time && !$prev) {
            $r = db::o()->query('SELECT id FROM chat_deleted WHERE time>=' . $time);
            $del = "";
            while (list($i) = db::o()->fetch_row($r))
                $del .= ( $del ? "," : "") . $i;
            tpl::o()->assign('deleted', $del);
        }
        $orderby = " ORDER BY c.posted_time DESC ";
        $limit = $orderby . (config::o()->v('chat_maxmess') ? " LIMIT " . config::o()->v('chat_maxmess') : "");
        if ($prev) {
            $where = ' WHERE c.id < ' . $time . $limit;
            tpl::o()->assign('prev', true);
        } else
            $where = $time ? ' WHERE c.edited_time>=' . $time . $orderby : $limit;
        $r = db::o()->query('SELECT c.*, u.username, u.group FROM chat AS c
                LEFT JOIN users AS u ON u.id=c.poster_id ' . $where);
        tpl::o()->assign('rows', array_reverse(db::o()->fetch2array($r)));
        tpl::o()->register_modifier('chat_mf', array($this, 'chat_mf'));
        tpl::o()->display('chat/chat.tpl');
    }

}

?>