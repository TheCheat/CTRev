<?php

/**
 * Project:             CTRev
 * @file                modules/messages.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
     * @var string $title
     */
    public $title = "";

    /**
     * Функция инициализации ЛС
     * @return null
     */
    public function init() {
        users::o()->check_perms('pm');
        lang::o()->get('messages');
        $send = $out = $sended = false;
        switch ($_GET ['act']) {
            case "resend":
            case "send":
                $send = true;
                $this->title = lang::o()->v('pm_sending_msg');
                break;
            case "sended":
                $sended = true;
                $this->title = lang::o()->v('pm_sended_msgs');
            case "output":
                $out = true;
                if (!$this->title)
                    $this->title = lang::o()->v('pm_output_msgs');
                break;
            default:
                $this->title = lang::o()->v('pm_input_msgs');
                break;
        }
        tpl::o()->assign("to_pm", $_GET ['to']);
        tpl::o()->assign("resend_id", $_GET ['id']);
        tpl::o()->assign("out", $out);
        tpl::o()->assign("sended", $sended);
        tpl::o()->assign("send", $send);
        tpl::o()->display("messages/index.tpl");
    }

}

class messages_ajax {

    /**
     * Функция инициализации Ajax-части ЛС
     * @return null
     */
    public function init() {
        users::o()->check_perms('pm');
        lang::o()->get('messages');
        $act = $_GET ['act'];
        switch ($act) {
            case "send_ok" :
                $to_unames = $_POST ['to_usernames'];
                $to_groups = $_POST ['to_groups'];
                $title = $_POST ['title'];
                $descr = $_POST ['body'];
                $this->confirm_send($to_unames, $to_groups, $title, $descr);
                ok();
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
                ok();
                break;
            case "delete" :
                $ids = $_POST ['item'];
                $this->delete($ids);
                ok();
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
     * @param string $to_unames получатель/получатели(имена пользователей)
     * @param string $to_groups получатель/получатели(гурппы пользователей)
     * @param string $title заголовок сообщения
     * @param string $body текст сообщения
     * @return null
     * @throws EngineException
     */
    protected function confirm_send($to_unames, $to_groups, $title, $body) {
        check_formkey();
        /* @var $etc etc */
        $etc = n("etc");
        if (users::o()->perm("pm_count")) {
            $cofmessgs = db::o()->p(users::o()->v('id'))->count_rows("pmessages", ('sender=? AND deleted<>"1"'));
            if ($cofmessgs > users::o()->perm("pm_count"))
                throw new EngineException("pm_send_count_error");
        }
        $title = trim($title);
        $body = trim($body);
        $to_unames = trim($to_unames);
        $etc->anti_flood('pmessages', 'deleted<>"1"', array('sender', 'time'));
        if ((!$to_unames && (!is_array($to_groups) || !$to_groups)) || !$body || mb_strlen($body) < config::o()->v('min_message_symb') || mb_strlen($body) > config::o()->v('max_message_symb'))
            throw new EngineException('pm_all_areas_cant_be_empty', array(
        config::o()->v('min_message_symb'),
        config::o()->v('max_message_symb')));
        if ($to_unames) {
            $to_unames = array_map("mb_strtolower", explode(";", $to_unames));
            $where = 'username_lower IN(@' . count($to_unames) . '?) AND u.id>0';
        }
        $rec = array();
        if ($to_groups && users::o()->perm('masspm')) {
            $users_db = db::o()->p($to_groups)->query('SELECT id, email FROM users
                    WHERE `group` IN(@' . count($to_groups) . '?) AND id>0');
            while ($user = db::o()->fetch_assoc($users_db))
                $rec [$user ["id"]] = $user ["email"];
        }
        if ($where) {
            $res = db::o()->p($to_unames)->query('SELECT u.id, u.email, g.pm_count FROM users AS u
                    LEFT JOIN groups AS g ON g.id=u.group
                    WHERE ' . $where);
            while ($row = db::o()->fetch_assoc($res)) {
                if ($row ["id"] == users::o()->v('id'))
                    continue;
                if ($row ["pm_count"]) {
                    $cofmessgs = db::o()->p($row ["id"])->count_rows("pmessages", 'receiver = ? AND deleted<>"2"');
                    if ($cofmessgs >= $row ["pm_count"])
                        continue;
                }
                $rec[$row ["id"]] = $row ["email"];
            }
        }
        if (!$rec)
            throw new EngineException('pm_no_messages_send');
        if (count($rec) > config::o()->v('max_pmessages'))
            throw new EngineException('pm_too_much_messages_send', config::o()->v('max_pmessages'));

        try {
            plugins::o()->pass_data(array('rows' => &$rec), true)->run_hook('pmessages_confirm_send');
        } catch (PReturn $e) {
            return $e->r();
        }

        foreach ($rec as $id => $email)
            $etc->send_message($title, $body, $id, $email);
    }

    /**
     * Просмотр ЛС
     * @param bool $out если true - исходящие, иначе - входящие
     * @param bool $sended отправлено?
     * @return null
     * @throws EngineException
     */
    public function show($out, $sended) {
        if (!$out) {
            $where = 'p.receiver=? AND p.deleted<>"2"';
            $rez = "sender";
        } else {
            $where = 'p.sender=? AND p.deleted<>"1"';
            if ($sended)
                $where .= ' AND p.unread="0"';
            $rez = "receiver";
        }
        $res = db::o()->p(users::o()->v('id'))->query('SELECT p.*, u.group, u.username FROM pmessages AS p
            LEFT JOIN users AS u ON u.id=p.' . $rez . '
            WHERE ' . $where . ' ORDER BY p.time');
        tpl::o()->assign("out", $out);
        tpl::o()->assign("sended", $sended);
        tpl::o()->assign("res", db::o()->fetch2array($res));
        tpl::o()->display("messages/show_pm.tpl");
    }

    /**
     * Просмотр данного ЛС
     * @param int $id ID письма
     * @return null
     * @throws EngineException
     */
    public function show_simple($id) {
        $id = (int) $id;
        $res = db::o()->p(users::o()->v('id'), users::o()->v('id'), $id)->query('SELECT 
            p.*, u.group, u.username FROM pmessages AS p
            LEFT JOIN users AS u ON p.sender=u.id WHERE
            ((p.receiver=? AND p.deleted<>"2") OR 
                (p.sender=? AND p.deleted<>"1")) AND p.id=? LIMIT 1');
        $res = db::o()->fetch_assoc($res);
        tpl::o()->assign("row", $res);
        tpl::o()->display('messages/show_pm_single.tpl');
        if ($res ["unread"] && $res['receiver'] == users::o()->v('id'))
            $this->read($id);
    }

    /**
     *
     * Форма отправки ЛС
     * @param string $to получатель/получатели
     * @param int $id ID пересылаемого письма
     * @return null
     * @throws EngineException
     */
    protected function send($to, $id = 0) {
        $id = (int) $id;
        if (users::o()->perm("pm_count")) {
            $cofmessgs = db::o()->p(users::o()->v('id'))->count_rows("pmessages", ('sender=? AND deleted<>"1"'));
            if ($cofmessgs > users::o()->perm("pm_count"))
                throw new EngineException("pm_send_count_error");
        }
        if ($id) {
            $res = db::o()->p(users::o()->v('id'), $id)->query('SELECT p.*, u.username FROM pmessages AS p
                LEFT JOIN users AS u ON u.id=p.sender
                WHERE p.receiver = ? AND p.deleted<>"2" 
                    AND p.id=? LIMIT 1');
            $res = db::o()->fetch_assoc($res);
            $to .= ( $to ? ";" : "") . $res ["username"];
        }
        else
            $res = array();
        tpl::o()->assign("to_pm", $to);
        tpl::o()->assign("row", $res);
        tpl::o()->assign("send", true);
        tpl::o()->display("messages/send_pm.tpl");
    }

    /**
     * Функция проверки кол-ва сообщений для данного пользователя
     * @return array массив из входящих, исходящих и непрочитанных входящих
     * @throws EngineException
     */
    public function count() {
        $inbox = db::o()->p(users::o()->v('id'))->count_rows("pmessages", 'receiver = ? AND deleted<>"2"');
        $outbox = db::o()->p(users::o()->v('id'))->count_rows("pmessages", 'sender=? AND deleted<>"1"');
        $unread = db::o()->p(users::o()->v('id'))->count_rows("pmessages", 'unread="1" AND receiver = ? AND deleted<>"2"');
        return array($inbox, $outbox, $unread);
    }

    /**
     * Функция прочтения последнего непрочитанного сообщения
     * @param int $time время данного сообщения
     * @param bool $after читать сообщения после данного времени, иначе до
     * @return array массив столбцов непрочитанного сообщения
     * @throws EngineException
     */
    public function unread($time = 0, $after = false) {
        $time = (int) $time;
        $where = "";
        if ($time)
            $where = ' AND p.time' . (!$after ? "<" : ">") . '?';
        $unread = db::o()->p(users::o()->v('id'), $time)->query('SELECT 
            p.id,p.subject,p.text,p.time,u.username,u.group FROM pmessages AS p
            LEFT JOIN users AS u ON u.id=p.sender
            WHERE p.unread="1" AND p.deleted<>"2" AND p.receiver = ?' . $where . "
                ORDER BY p.time " . (!$after ? "ASC" : "DESC") . '
                LIMIT 1');
        return db::o()->fetch_assoc($unread);
    }

    /**
     * Функция подсчёта кол-ва непрочитанных сообщений до или после данного времени
     * @param int $time время данного сообщения
     * @param bool $after читать сообщения после данного времени, иначе до
     * @return int кол-во искомых сообщений
     * @throws EngineException
     */
    public function unread_count($time, $after = false) {
        $time = (int) $time;
        $where = "";
        if ($time)
            $where = ' AND time' . (!$after ? "<" : ">") . "?";
        $unreads = db::o()->p(users::o()->v('id'), $time)->count_rows("pmessages", 'unread="1" AND deleted<>"2" AND receiver = ?' . $where);
        return $unreads;
    }

    /**
     * Функция удаления ЛС
     * @param int $id ID пользователя
     * @return null
     * @throws EngineException
     */
    public function clear($id = null) {
        check_formkey();
        users::o()->check_perms('acp', 2);
        $id = (int) $id;
        if (!$id)
            db::o()->truncate_table('pmessages');
        else
            db::o()->p($id, $id)->delete("pmessages", "WHERE sender = ? OR receiver=?");
    }

    /**
     * Функция удаления ЛС
     * @param int|array $ids ID ЛС
     * @return null
     * @throws EngineException
     */
    public function delete($ids) {
        check_formkey();
        if (!$ids)
            return;
        if (!is_array($ids))
            $where = 'id=?';
        else
            $where = 'id IN(@' . count($ids) . '?)';
        if (!users::o()->check_adminmode()) {
            $uid = users::o()->v('id');
            $uwhere = $where;
            $where .= ' AND ((sender=? AND deleted="2") OR (receiver=? AND deleted="1"))';
            $uwhere .= ' AND (sender=? OR receiver=?) AND deleted="0"';
            db::o()->p($ids, $uid, $uid)->update(array("_cb_deleted" => 'IF(sender=' . $uid . ',"1","2")'), "pmessages", 'WHERE ' . $uwhere);
        }
        db::o()->p($ids, $uid, $uid)->delete("pmessages", 'WHERE ' . $where);
    }

    /**
     * Функция прочтения ЛС
     * @param integer|array $ids ID ЛС
     * @return null
     * @throws EngineException
     */
    public function read($ids) {
        if (!$ids)
            return;
        if (!is_array($ids))
            $where = 'id=?';
        else
            $where = 'id IN(@' . count($ids) . '?)';
        db::o()->p(users::o()->v('id'), $ids)->update(array(
            "unread" => '0'), "pmessages", 'WHERE receiver=? AND ' . $where);
    }

}

?>