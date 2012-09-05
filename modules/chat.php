<?php

/**
 * Project:             CTRev
 * File:                chat.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $lang->get("blocks/chat");
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
                print($lang->v('chat_no_messages'));
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
     * @global db $db
     * @global users $users
     * @param int $id ID сообщения
     * @return null
     * @throws EngineException
     */
    public function delete($id) {
        global $db, $users;
        $id = (int) $id;
        check_formkey();
        $users->check_perms('del_chat');
        $db->delete('chat', 'WHERE id=' . $id .
                (!$users->perm('del_chat', 2) ? " AND poster_id=" . $users->v('id') : "") . ' LIMIT 1');
        $this->delete_logs($id);
    }

    /**
     * Запись в логи чата
     * @global db $db
     * @global config $config
     * @param int $id ID сообщения
     * @return null 
     */
    protected function delete_logs($id = 0) {
        global $db, $config;
        $id = (int) $id;
        if (!$db->affected_rows() || !$config->v('chat_clearlogs'))
            return;
        $db->delete('chat_deleted', 'WHERE time<=' . (time() - $config->v('chat_clearlogs')));
        $db->insert(array('id' => $id, 'time' => time()), 'chat_deleted');
        if (!$id && $db->affected_rows() < 1)
            $db->update(array('time' => time()), 'chat_deleted', 'WHERE id="' . $id . '" LIMIT 1');
    }

    /**
     * Очистка сообщений чата
     * @global db $db
     * @global users $users
     * @return null
     * @throws EngineException
     */
    public function truncate() {
        global $db, $users;
        check_formkey();
        $users->check_perms('del_chat', 3);
        $db->truncate_table('chat');
        $this->delete_logs();
    }

    /**
     * Сохранение сообщения чата
     * @global db $db
     * @global users $users
     * @global plugins $plugins
     * @param string $text
     * @param int $id ID сообщения
     * @return null
     * @throws EngineException
     */
    public function save($text, $id = null) {
        global $db, $users, $plugins;
        check_formkey();
        $text = trim($text);
        if (!$text)
            return;
        $id = (int) $id;
        if (!$id)
            $users->check_perms('chat', 2, 2);
        else
            $users->check_perms('edit_chat');
        $update = array();
        $update["text"] = $text;
        $update["edited_time"] = time();

        try {
            $plugins->pass_data(array('update' => &$update,
                'id' => $id), true)->run_hook('chat_save');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$id) {
            $update["poster_id"] = $users->v('id') ? $users->v('id') : -1;
            $update["posted_time"] = time();
            $db->delete("chat_deleted", "WHERE id=0");
            $db->insert($update, 'chat');
        } else
            $db->update($update, 'chat', 'WHERE id=' . $id .
                    (!$users->perm('edit_chat', 2) ? " AND poster_id=" . $users->v('id') : "") . ' LIMIT 1');
    }

    /**
     * Вывод неформатированного текста сообщения для редактирования
     * @global db $db
     * @global users $users
     * @param int $id ID сообщения
     * @return null
     */
    protected function get_text($id) {
        global $db, $users;
        $users->check_perms('edit_chat');
        $id = (int) $id;
        list ($text) = $db->fetch_row($db->query("SELECT text FROM chat WHERE id=" . $id . ' LIMIT 1'));
        print($text);
    }

    /**
     * Форматирование сообщения
     * @global users $users
     * @global lang $lang
     * @global plugin $plugins
     * @param array $row массив данных сообщения
     * @return null
     */
    public function chat_mf(&$row) {
        global $users, $lang, $plugins;
        $t = $row["text"];
        $cmds = 'private|me|hello|bye';
        preg_match('/^\/(' . $cmds . ')\s*(?:\((\w+)(?:,\s*([0-9]+))?\)\s+)?(.*)$/siu', $t, $matches);
        if (!$matches)
            return;
        list(, $cmd, $p1, $p2, $t) = $matches;
        $u = null;
        switch ($cmd) {
            case "private":
                if (!$row['poster_id'] || mb_strtolower($row['username']) == mb_strtolower($p1))
                    return;
                if (!$users->perm('chat_sprivate') &&
                        $users->v('username_lower') != mb_strtolower($p1) &&
                        $users->v('id') != $row['poster_id']) {
                    $row["text"] = '';
                    return;
                }
                if ($users->v('id') == $row['poster_id']) {
                    $cmd = 'fprivate';
                    $u = $p1;
                }
            case "me":
                if (!$u)
                    $u = smarty_group_color_link($row["username"], $row["group"]);
                $row["text"] = sprintf($lang->v("chat_" . $cmd . "_saying"), $u, $t);
                break;
            case "hello":
            case "bye":
                $row["text"] = sprintf($lang->v("chat_" . $cmd . "_saying"), smarty_group_color_link($row["username"], $row["group"]));
                break;
            default:
                try {
                    $plugins->pass_data(array('row' => &$row,
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
     * @global db $db
     * @global tpl $tpl
     * @global config $config
     * @global users $users
     * @param int $time время последней проверки или ID сообщения
     * @param bool $prev показать пред. сообщения, до этого ID
     * @return null
     */
    protected function show($time, $prev = false) {
        global $db, $tpl, $config, $users;
        $time = (int) $time;
        $users->check_perms('chat', 2, 2);
        if ($time && !$prev) {
            $r = $db->query('SELECT id FROM chat_deleted WHERE time>=' . $time);
            $del = "";
            while (list($i) = $db->fetch_row($r))
                $del .= ( $del ? "," : "") . $i;
            $tpl->assign('deleted', $del);
        }
        $orderby = " ORDER BY c.posted_time DESC ";
        $limit = $orderby . ($config->v('chat_maxmess') ? " LIMIT " . $config->v('chat_maxmess') : "");
        if ($prev) {
            $where = ' WHERE c.id < ' . $time . $limit;
            $tpl->assign('prev', true);
        } else
            $where = $time ? ' WHERE c.edited_time>=' . $time . $orderby : $limit;
        $r = $db->query('SELECT c.*, u.username, u.group FROM chat AS c
                LEFT JOIN users AS u ON u.id=c.poster_id ' . $where);
        $tpl->assign('rows', array_reverse($db->fetch2array($r)));
        $tpl->register_modifier('chat_mf', array($this, 'chat_mf'));
        $tpl->display('chat/chat.tpl');
    }

}

?>