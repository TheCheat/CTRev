<?php

/**
 * Project:             CTRev
 * File:                messages.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление ЛС
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class messages {

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Функция инициализации ЛС
     * @global lang $lang
     * @global users $users
     * @global tpl $tpl
     * @return null
     */
    public function init() {
        global $lang, $users, $tpl;
        $users->check_perms('pm');
        $lang->get('messages');
        $send = $out = $sended = false;
        switch ($_GET ['act']) {
            case "resend":
            case "send":
                $send = true;
                $this->title = $lang->v('pm_sending_msg');
                break;
            case "sended":
                $sended = true;
                $this->title = $lang->v('pm_sended_msgs');
            case "output":
                $out = true;
                if (!$this->title)
                    $this->title = $lang->v('pm_output_msgs');
                break;
            default:
                $this->title = $lang->v('pm_input_msgs');
                break;
        }
        $tpl->assign("to_pm", $_GET ['to']);
        $tpl->assign("resend_id", $_GET ['id']);
        $tpl->assign("out", $out);
        $tpl->assign("sended", $sended);
        $tpl->assign("send", $send);
        $tpl->display("messages/index.tpl");
    }

}

class messages_ajax {

    /**
     * Функция инициализации Ajax-части ЛС
     * @global lang $lang
     * @global users $users
     * @return null
     */
    public function init() {
        global $lang, $users;
        $users->check_perms('pm');
        $lang->get('messages');
        $act = $_GET ['act'];
        switch ($act) {
            case "send_ok" :
                $to_unames = $_POST ['to_usernames'];
                $to_groups = $_POST ['to_groups'];
                $title = $_POST ['title'];
                $descr = $_POST ['body'];
                $this->confirm_send($to_unames, $to_groups, $title, $descr);
                die("OK!");
                break;
            case "send" :
                $to = $_GET ['to'];
                $id = longval($_GET ['id']);
                $this->send($to, $id);
                break;
            case "read" :
                $id = (int) $_POST ['id'];
                $this->show_simple($id);
                break;
            case "s_read" :
                $ids = $_POST ['item'];
                $this->read($ids);
                die("OK!");
                break;
            case "delete" :
                $ids = $_POST ['item'];
                $this->delete($ids);
                die("OK!");
                break;
            default :
                $out = (bool) $_GET ['out'];
                $sended = (bool) $_GET ['sended'];
                $this->show($out, $sended);
                break;
        }
    }

    /**
     * Функция отправки ЛС
     * @global db $db
     * @global config $config
     * @global users $users
     * @global etc $etc
     * @global plugins $plugins
     * @param string $to_unames получатель/получатели(имена пользователей)
     * @param string $to_groups получатель/получатели(гурппы пользователей)
     * @param string $title заголовок сообщения
     * @param string $body текст сообщения
     * @return null
     * @throws EngineException
     */
    protected function confirm_send($to_unames, $to_groups, $title, $body) {
        global $db, $config, $users, $etc, $plugins;
        check_formkey();
        if ($users->perm("pm_count")) {
            $cofmessgs = $db->count_rows("pmessages", ('sender=' . $users->v('id')));
            if ($cofmessgs > $users->perm("pm_count"))
                throw new EngineException("pm_send_count_error");
        }
        $title = trim($title);
        $body = trim($body);
        $to_unames = trim($to_unames);
        anti_flood('pmessages', '', array('sender', 'time'));
        if ((!$to_unames && (!is_array($to_groups) || !$to_groups)) || !$body || mb_strlen($body) < $config->v('min_message_symb') || mb_strlen($body) > $config->v('max_message_symb'))
            throw new EngineException('pm_all_areas_cant_be_empty', array(
                $config->v('min_message_symb'),
                $config->v('max_message_symb')));
        if ($to_unames) {
            $to_unames = implode(", ", array_map('mb_strtolower', array_map(array($db, 'esc'), explode(";", $to_unames))));
            $where = 'username_lower IN(' . $to_unames . ') AND u.id>0';
        }
        $rec = array();
        if ($to_groups && $users->perm('masspm')) {
            $to_groups = array_map('intval', $to_groups);
            $users_db = $db->query('SELECT id, email FROM users
                    WHERE `group` IN(' . implode(", ", $to_groups) . ') AND id>0');
            while ($user = $db->fetch_assoc($users_db)) {
                $rec [$user ["id"]] = $user ["email"];
            }
        }
        if ($where) {
            $res = $db->query('SELECT u.id, u.email, g.pm_count FROM users AS u
                    LEFT JOIN groups AS g ON g.id=u.group
                    WHERE ' . $where);
            while ($row = $db->fetch_assoc($res)) {
                if ($row ["id"] == $users->v('id'))
                    continue;
                if ($row ["pm_count"]) {
                    $cofmessgs = $db->count_rows("pmessages", ('receiver = ' . $row ["id"]));
                    if ($cofmessgs >= $row ["pm_count"])
                        continue;
                }
                $rec[$row ["id"]] = $row ["email"];
            }
        }
        if (!$rec)
            throw new EngineException('pm_no_messages_send');
        if ($rec > $config->v('max_pmessages'))
            throw new EngineException('pm_too_much_messages_send', $config->v('max_pmessages'));
        
        try {
            $plugins->pass_data(array('rows' => &$rec), true)->run_hook('pmessages_confirm_send');
        } catch (PReturn $e) {
            return $e->r();
        }
        
        foreach ($rec as $id => $email)
            $etc->send_message($title, $body, $id, $email);
    }

    /**
     * Просмотр ЛС
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @param bool $out если true - исходящие, иначе - входящие
     * @return null
     * @throws EngineException
     */
    protected function show($out, $sended) {
        global $tpl, $db, $users;
        if (!$out) {
            $where = 'p.receiver = ' . $users->v('id');
            $rez = "sender";
        } else {
            $where = 'p.sender=' . $users->v('id');
            if ($sended)
                $where .= ' AND p.unread="0"';
            $rez = "receiver";
        }
        $res = $db->query('SELECT p.*, u.group, u.username FROM pmessages AS p
            LEFT JOIN users AS u ON u.id=p.' . $rez . '
            WHERE ' . $where . ' ORDER BY p.time');
        $tpl->assign("out", $out);
        $tpl->assign("sended", $sended);
        $tpl->assign("res", $db->fetch2array($res));
        $tpl->display("messages/show_pm.tpl");
    }

    /**
     * Просмотр данного ЛС
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @param int $id ID письма
     * @return null
     * @throws EngineException
     */
    protected function show_simple($id) {
        global $tpl, $db, $users;
        $id = (int) $id;
        $res = $db->query('SELECT p.*, u.group, u.username FROM pmessages AS p
            LEFT JOIN users AS u ON p.sender=u.id WHERE
            (p.receiver=' . $users->v('id') . ' OR p.sender=' . $users->v('id') . ') AND p.id=' . longval($id) . " LIMIT 1");
        $res = $db->fetch_assoc($res);
        $tpl->assign("row", $res);
        $tpl->display('messages/show_pm_single.tpl');
        if ($res ["unread"] && $res['receiver'] == $users->v('id'))
            $this->read($id);
    }

    /**
     *
     * Форма отправки ЛС
     * @global tpl $tpl
     * @global db $db
     * @global users $users
     * @param string $to получатель/получатели
     * @param int $id ID пересылаемого письма
     * @return null
     * @throws EngineException
     */
    protected function send($to, $id = 0) {
        global $tpl, $db, $users;
        $id = (int) $id;
        if ($users->perm("pm_count")) {
            $cofmessgs = $db->count_rows("pmessages", ('sender=' . $users->v('id')));
            if ($cofmessgs > $users->perm("pm_count"))
                throw new EngineException("pm_send_count_error");
        }
        if ($id) {
            $res = $db->query('SELECT p.*, u.username FROM pmessages AS p
                LEFT JOIN users AS u ON u.id=p.sender
                WHERE p.receiver = ' . $users->v('id') . ' AND p.id=' . longval($id) . ' LIMIT 1');
            $res = $db->fetch_assoc($res);
            $to .= ( $to ? ";" : "") . $res ["username"];
        } else
            $res = array();
        $tpl->assign("to_pm", $to);
        $tpl->assign("row", $res);
        $tpl->assign("send", true);
        $tpl->display("messages/send_pm.tpl");
    }

    /**
     * Функция проверки кол-ва сообщений для данного пользователя
     * @global db $db
     * @global users $users
     * @return array массив из входящих, исходящих и непрочитанных входящих
     * @throws EngineException
     */
    public function count() {
        global $db, $users;
        $inbox = $db->count_rows("pmessages", ('receiver = ' . $users->v('id')));
        $outbox = $db->count_rows("pmessages", ('sender=' . $users->v('id')));
        $unread = $db->count_rows("pmessages", ('unread="1" AND receiver = ' . $users->v('id')));
        return array($inbox, $outbox, $unread);
    }

    /**
     * Функция прочтения последнего непрочитанного сообщения
     * @global db $db
     * @global users $users
     * @param int $time время данного сообщения
     * @param bool $after читать сообщения после данного времени, иначе до
     * @return array массив столбцов непрочитанного сообщения
     * @throws EngineException
     */
    public function unread($time = 0, $after = false) {
        global $db, $users;
        $time = (int) $time;
        $where = "";
        if (longval($time))
            $where = ' AND p.time' . (!$after ? "<" : ">") . $time;
        $unread = $db->query('SELECT p.id,p.subject,p.text,p.time,u.username,u.group FROM pmessages AS p
            LEFT JOIN users AS u ON u.id=p.sender
            WHERE p.unread="1" AND p.receiver = ' . $users->v('id') . $where . "
                ORDER BY p.time " . (!$after ? "ASC" : "DESC") . '
                LIMIT 1');
        return $db->fetch_assoc($unread);
    }

    /**
     * Функция подсчёта кол-ва непрочитанных сообщений до или после данного времени
     * @global db $db
     * @global users $users
     * @param int $time время данного сообщения
     * @param bool $after читать сообщения после данного времени, иначе до
     * @return int кол-во искомых сообщений
     * @throws EngineException
     */
    public function unread_count($time, $after = false) {
        global $db, $users;
        $time = (int) $time;
        $where = "";
        if (longval($time))
            $where = ' AND time' . (!$after ? "<" : ">") . $time;
        $unreads = $db->count_rows("pmessages", ('unread="1" AND receiver = ' . $users->v('id') . $where));
        return $unreads;
    }

    /**
     * Функция удаления ЛС
     * @global db $db
     * @global users $users
     * @param int $uid ID пользователя
     * @return null
     * @throws EngineException
     */
    public function clear($id = null) {
        global $db, $users;
        check_formkey();
        $users->check_perms('acp', 2);
        $id = (int) $id;
        if (!$id)
            $db->truncate_table('pmessages');
        else
            $db->delete("pmessages", "WHERE sender = " . $id . " OR receiver=" . $id);
    }

    /**
     * Функция удаления ЛС
     * @global db $db
     * @global users $users
     * @param int|array $ids ID ЛС
     * @return null
     * @throws EngineException
     */
    public function delete($ids) {
        global $db, $users;
        check_formkey();
        if (!is_array($ids)) {
            $ids = longval($ids);
            if (!$ids)
                return;
            $where = 'id=' . $ids;
        } else {
            $ids = implode(", ", array_map("intval", $ids));
            $where = 'id IN(' . $ids . ')';
        }
        if (!$users->check_adminmode()) {
            $uid = $users->v('id');
            $where .= ' AND (sender=' . $uid . ' OR receiver=' . $uid . ')';
        }
        $db->delete("pmessages", 'WHERE ' . $where);
    }

    /**
     * Функция прочтения ЛС
     * @global db $db
     * @global users $users
     * @param integer|array $ids ID ЛС
     * @return null
     * @throws EngineException
     */
    protected function read($ids) {
        global $db, $users;
        if (!is_array($ids)) {
            $ids = longval($ids);
            if (!$ids)
                return;
            $where = 'id=' . $ids;
        } else {
            $ids = implode(", ", array_map("intval", $ids));
            $where = 'id IN(' . $ids . ')';
        }
        $db->update(array("unread" => '0'), "pmessages", 'WHERE receiver=' . $users->v('id') . ' AND 
            ' . $where);
    }

}

?>