<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.etc.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Кое-что ещё
 * @tutorial            Действия над пользователями не относятся к классу users,
 * ибо он подразумевает действие лишь над конкретным пользователем и группами пользователей.
 * Да и потому, что я так хочу.
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class etc {

    /**
     * Знаковый счётчик?
     * @var bool $signed_res
     */
    protected $signed_res = false;

    /**
     * Значение для параметра кол-ва, когда обнуляется счётчик 
     * @see add_res()
     */

    const reset_count = '<reset>';

    /**
     * Выборка пользователей
     * @param int $id ID пользователя
     * @param string $username имя пользователя
     * @param string $columns выбираемые столбцы
     * @return array результат
     */
    public function select_user($id, $username = null, $columns = null) {
        $id = (int) $id;
        if (!$id && !$username)
            return;
        $where = "";
        if ($id)
            $where .= "id = " . $id;
        if ($username)
            $where .= ( $where ? " AND " : "") . "username_lower=" . db::o()->esc(mb_strtolower($username));
        $ret = db::o()->query('SELECT ' . ($columns ? $columns : "*") . ' FROM users' . ($where ? " WHERE " . $where : "") . ' LIMIT 1');
        return db::o()->fetch_assoc($ret);
    }

    /**
     * Включает знаковый счётчик(отключает проверку на меньше 0 для add_res)
     * @param bool $state true, если включить
     * @return etc $this
     */
    public function signed_res($state = true) {
        $this->signed_res = $state;
        return $this;
    }

    /**
     * Увеличение какого-либо счётчика
     * @param string|array $type тип ресурса(напр. torrents, comments)
     * @param int|string $count добавляемое кол-во(если равняется reset_count - обнуляется)
     * @param string $table обновляемая таблица
     * @param int $id ID обновляемый
     * @param string $column проверяемый столбец(для условия)
     * @return null
     */
    public function add_res($type = 'torrents', $count = 1, $table = "users", $id = null, $column = 'id') {
        if (!$column)
            $column = 'id';
        if ($column == 'id') {
            $id = (int) $id;
            if (!$id)
                $id = users::o()->v('id');
            if (!$id)
                return;
        }
        if (!$table)
            $table = "users";
        $columns = array();
        if (!is_array($type))
            $types = array(
                $type => $count);
        else
            $types = $type;
        foreach ($types as $type => $count) {
            $str = $type . '_count';
            $stre = '`' . $type . '_count`';
            if ((string) $count == self::reset_count)
                $columns[$str] = 0;
            else {
                $e = floatval($count) . '+' . $stre;
                if ($count < 0 && !$this->signed_res)
                    $e = 'IF(' . $stre . '>=' . (-floatval($count)) . ',' . $e . ',0)';
                $columns['_cb_' . $str] = $e;
            }
        }
        db::o()->update($columns, $table, 'WHERE `' . $column . '`=' . db::o()->esc($id) . ' LIMIT 1');
        $this->signed_res(false);
    }

    /**
     * Отправка сообщения с подстановкой переменных
     * @param string|array $email E-mail пользователя
     * @param string $shablon шаблон сообщения
     * @param array $vars массив переменных
     * @return string|int статус отыслки или список ошибок
     */
    public function send_mail($email, $shablon, $vars = array()) {
        $baseurl = globals::g('baseurl');
        lang::o()->get("mail_shablons");
        $body = lang::o()->v("mail_body_" . $shablon);
        $vars['siteurl'] = $baseurl;
        $vars['sitename'] = $vars['site_title'] = config::o()->v('site_title');
        foreach ($vars as $key => $value)
            $body = str_replace('$' . $key, $value, $body);
        send_mail(lang::o()->v("mail_subject_" . $shablon), $body, $email, $error);
        return $error;
    }

    /**
     * Отправка ЛС пользователю
     * @param string $title заголовок сообщения
     * @param string $body тело сообщения
     * @param int $uid ID пользователя
     * @param string $email E-mail получателя
     * @param int $sender ID отправителя
     * @return bool статус отправки
     */
    public function send_message($title, $body, $uid, $email = null, $sender = null) {
        if (!$sender)
            $sender = users::o()->v('id');
        $uid = (int) $uid;
        if (!$title)
            $title = lang::o()->v('pmessage_empty_subject');
        $id = db::o()->insert(array(
            "subject" => $title,
            "text" => $body,
            "sender" => $sender,
            "receiver" => $uid,
            "time" => time()), "pmessages");
        if ($email) {
            $link = furl::o()->construct("pm", array(
                "act" => "read",
                "id" => $id));
            $this->send_mail($email, "getted_pm", array(
                "link" => $link,
                "subject" => $title,
                "body" => bbcodes::o()->format_text($body, "SIMPLE")));
        }
        return ((bool) $id);
    }

    /**
     * Блокировка пользователя
     * @param int $uid ID пользователя
     * @param int $period период блокировки
     * @param string $reason причина блокировки
     * @param string $email E-mail
     * @param int $ip_f начальный IP
     * @param int $ip_t конечный IP
     * @param int $id ID бана
     * @return array массив присвоенных значений столбцов
     */
    public function ban_user($uid, $period = 1, $reason = null, $email = null, $ip_f = null, $ip_t = null, $id = 0) {
        $uid = (int) $uid;
        $ip_f = longval($ip_f);
        $ip_t = longval($ip_t);
        $period = (int) $period;
        if ($ip_t && !$ip_f)
            $ip_f = $ip_t;
        if ($ip_f && !$ip_t)
            $ip_t = $ip_f;
        if ($ip_f > $ip_t) {
            $t = $ip_f;
            $ip_f = $ip_t;
            $ip_t = $t;
        }
        if (!$uid && !$email && !$ip_f)
            return false;
        $columns = array(
            "uid" => $uid,
            "email" => $email,
            "ip_f" => $ip_f,
            "ip_t" => $ip_t,
            "reason" => $reason);
        $columns["byuid"] = users::o()->v('id');
        if ($period) {
            $columns["to_time"] = ($period ? time() + $period * 3600 : 0 );
            $columns["period"] = $period;
        }
        try {
            plugins::o()->pass_data(array('update' => &$columns), true)->run_hook('users_ban');
        } catch (PReturn $e) {
            return $e->r();
        }
        if (!$id)
            db::o()->insert($columns, "bans");
        else
            db::o()->update($columns, "bans", "WHERE id = " . $id . " LIMIT 1");
        if ($uid)
            db::o()->update(array(
                "_cb_old_group" => '`group`',
                "group" => users::banned_group), "users", "WHERE id=" . $uid . " AND `old_group`=0 LIMIT 1");
        log_add("banned", 'admin', array(
            $email ? $email : "-",
            $ip_f ? $ip_f . ($ip_t ? " - " . $ip_t : "") : "-",
            $uid ? $uid : "-"), $uid);
        return $columns;
    }

    /**
     * Разблокировка пользователя
     * @param int $uid ID пользователя
     * @param int $id ID бана
     * @return bool статус разбана
     */
    public function unban_user($uid, $id = null) {
        $uid = (int) $uid;
        $id = (int) $id;
        if (!$uid && !$id)
            return false;
        if (!$uid)
            list($uid) = db::o()->fetch_row(db::o()->query('SELECT uid FROM bans WHERE id=' . $id . ' LIMIT 1'));
        try {
            plugins::o()->pass_data(array('uid' => $uid,
                'id' => $id), true)->run_hook('users_unban');
        } catch (PReturn $e) {
            return $e->r();
        }
        db::o()->update(array(
            "_cb_group" => '`old_group`',
            "old_group" => 0), "users", "WHERE id=" . $uid . " AND `old_group`<>0 LIMIT 1");
        db::o()->delete("bans", " WHERE " . ($id ? "id=" . $id . ($uid ? " AND " : "") : "") .
                ($uid ? "uid=" . $uid : "") . " LIMIT 1");
        log_add("unbanned", 'admin', $id, $uid);
        return true;
    }

    /**
     * Предупреждение пользователя
     * @param int $uid ID пользователя
     * @param string $reason причина
     * @param int $warns кол-во предупреждений
     * @param bool $notify оповестить о предупреждении пользователя?
     * @param string $email E-mail пользователя
     * @param bool $check_ban проверять кол-во предупреждений для бана пользователя?
     * @param int $id ID предупреждения
     * @return array массив присвоенных значений столбцов
     */
    public function warn_user($uid, $reason, $warns = null, $notify = false, $email = null, $check_ban = true, $id = null) {
        lang::o()->get('admin/bans');
        $uid = (int) $uid;
        if ((!$uid && !$id) || !$reason)
            return false;
        $columns = array(
            "reason" => $reason);
        if ($uid) {
            $columns["uid"] = $uid;
            $columns["byuid"] = users::o()->v('id');
            $columns["time"] = time();
            if (is_null($warns) || $warns === true || (!$email && $notify)) {
                $r = $this->select_user($uid, null, "email,warnings_count");
                $warns = $r["warnings_count"];
                $email = $r["email"];
            }
        }
        try {
            plugins::o()->pass_data(array('update' => &$columns), true)->run_hook('users_warn');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($uid)
            $this->add_res('warnings', 1, "users", $uid);
        if (!$id)
            db::o()->insert($columns, "warnings");
        else
            db::o()->update($columns, "warnings", "WHERE id = " . $id . " LIMIT 1");
        if ($uid) {
            if (config::o()->v('warn2ban') && $warns + 1 >= config::o()->v('warn2ban') && $warns !== false)
                $this->ban_user($uid, config::o()->v('warn2ban_days'), sprintf(lang::o()->v('warnings_ban_reason'), config::o()->v('warn2ban')));
            if ($notify) {
                $title = lang::o()->v('warnings_user_warned_title');
                $body = sprintf(lang::o()->v('warnings_user_warned_body'), smarty_group_color_link(users::o()->v('username'), users::o()->v('group'), true), $reason);
                $this->send_message($title, $body, $uid, $email);
            }
            log_add("warned", 'admin', null, $uid);
        }
        return $columns;
    }

    /**
     * Снятие предупреждения с пользователя
     * @param int $uid ID пользователя
     * @param int $warns кол-во предупреждений
     * @param int $id ID предупреждения
     * @return bool статус снятия
     */
    public function unwarn_user($uid, $warns = null, $id = null) {
        $uid = (int) $uid;
        $id = (int) $id;
        if (!$uid && !$id)
            return false;
        if (!$uid)
            list($uid) = db::o()->fetch_row(db::o()->query('SELECT uid FROM warnings WHERE id=' . $id . ' LIMIT 1'));
        if (is_null($warns) || $warns === true) {
            $r = $this->select_user($uid, null, "warnings_count");
            $warns = $r["warnings_count"];
        }
        try {
            plugins::o()->pass_data(array('uid' => $uid,
                'id' => $id,
                'warns' => $warns), true)->run_hook('users_unwarn');
        } catch (PReturn $e) {
            return $e->r();
        }
        $this->add_res('warnings', -1, "users", $uid);
        if (config::o()->v('warn2ban') && $warns - 1 < config::o()->v('warn2ban') && $warns !== false)
            $this->unban_user($uid);
        db::o()->delete("warnings", "WHERE " . ($id ? "id=" . $id . " AND " : "") . "uid=" . $uid, 1);
        log_add("unwarned", 'admin', null, $uid);
        return true;
    }

    /**
     * Изменение группы пользователя
     * @param int $uid ID пользователя
     * @param int $gid ID новой группы
     * @param bool $nu без обновления
     * @return bool статус изменения
     */
    public function change_group($uid, $gid, $nu = false) {
        if (!users::o()->perm('system'))
            return false;
        $gid = (int) $gid;
        $uid = (int) $uid;
        $gr = users::o()->get_group($gid);
        if (!$gr || $gr['guest'])
            return false;
        try {
            plugins::o()->pass_data(array('gid' => $gid,
                'uid' => $uid), true)->run_hook('users_change_group');
        } catch (PReturn $e) {
            return $e->r();
        }
        /* $r = $this->select_user($id, null, '`group`, old_group');
          $gr = users::o()->get_group($r['group']);
          if ($gr['system'] || $r['old_group'])
          return false; */
        if (!$nu)
            db::o()->update(array('group' => $gid), 'users', 'WHERE id =' . $uid . ' LIMIT 1');
        log_add('changed_group', 'admin', array(users::o()->get_group_name($gid), $gid), $uid);
        return true;
    }

    /**
     * Удаление торрента
     * @param int $id ID торрента
     * @param EngineException $exc исключение, если есть
     * @return null
     */
    public function delete_torrent($id, &$exc = null) {
        $id = (int) $id;
        /* @var $torrents torrents_ajax */
        $torrents = plugins::o()->get_module('torrents', false, true);
        try {
            $torrents->delete($id, true);
        } catch (EngineException $e) {
            $exc = $e; // PHP Bydlokod
            return false;
        }
        return true;
    }

    /**
     * Удаление пользователя
     * @param int $id ID пользователя
     * @return bool статус удаления
     */
    public function delete_user($id) {
        $id = (int) $id;
        $r = $this->select_user($id, null, '`group`, avatar, username');
        if (!$r)
            return;
        $gr = users::o()->get_group($r['group']);
        if (!$gr['can_bedeleted'])
            return false;
        try {
            plugins::o()->pass_data(array('r' => $r,
                'id' => $id), true)->run_hook('users_delete');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($r['avatar'])
            $this->remove_user_avatar($id, $r['avatar']);
        $b = users::o()->admin_mode(true);
        db::o()->delete("bans", "WHERE uid = " . $id);
        db::o()->delete("warnings", "WHERE uid = " . $id);
        db::o()->delete("downloaded", "WHERE uid = " . $id);
        /* @var $pm messages_ajax */
        $pm = plugins::o()->get_module('messages', false, true);
        $pm->clear($id);
        db::o()->delete("read_torrents", "WHERE user_id = " . $id);
        /* @var $mailer mailer */
        $mailer = n("mailer");
        /* @var $rating rating */
        $rating = n("rating");
        /* @var $comments */
        $comments = n("comments");
        $mailer->remove($id, true);
        $rating->change_type('users')->clear($id);
        db::o()->delete("zebra", "WHERE user_id = " . $id . " OR to_userid = " . $id);
        db::o()->delete("bookmarks", "WHERE user_id = " . $id);
        db::o()->delete("invites", "WHERE user_id = " . $id);
        db::o()->delete("peers", "WHERE uid = " . $id);
        $comments->change_type('users')->clear($id);
        db::o()->delete("users", "WHERE id = " . $id);
        if (!$b)
            users::o()->admin_mode();
        log_add('deleted_user', 'admin', array($r['username'], $id));
        return true;
    }

    /**
     * Удаление аватары пользователя
     * @param int $id ID пользователя
     * @param string $avatar значение поля vatar пользователя
     * @return null
     */
    public function remove_user_avatar($id, $avatar) {
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $avatar_name = display::avatar_prefix . $id;
        $aft = $uploader->filetypes('avatars');
        if (preg_match('/^' . mpc($avatar_name) . '\.(' . str_replace(";", "|", $aft ['types']) . ')$/siu', $avatar))
            @unlink(ROOT . config::o()->v('avatars_folder') . "/" . $avatar);
    }

    /**
     * Осуществление подтверждения пользователя
     * @param int $act степень подтверждения
     * @param int $now_conf степень подтверждения на данный момент
     * @param int $id ID пользователя
     * @return int степень подтверждения
     */
    public function confirm_user($act, $now_conf, $id = null) {
        $now_conf = (int) $now_conf;
        if ($now_conf < 0 || $now_conf > 2)
            return $now_conf;
        $id = (int) $id;
        switch ($act) {
            case 3:
                $confirm = 3;
                break;
            case 2:
                if ($now_conf < 1)
                    return lang::o()->v('not_confirmed_email');
                if (!config::o()->v('confirm_admin'))
                    $confirm = 3;
                else
                    $confirm = 2;
                break;
            default:
                if (config::o()->v('confirm_email')) {
                    $confirm = 0;
                    break;
                }
            case 1:
                if (!config::o()->v('confirm_admin') && (config::o()->v('allowed_register') || !config::o()->v('allowed_invite')))
                    $confirm = 3;
                elseif (config::o()->v('allowed_register') || !config::o()->v('allowed_invite'))
                    $confirm = 2;
                else
                    $confirm = 1;
                break;
        }
        if ($now_conf >= $confirm)
            return $now_conf;
        if ($id)
            db::o()->update(array(
                "confirmed" => $confirm), "users", ( 'id=' . $id), 1);
        return $confirm;
    }

    /**
     * Функция для запроса подтверждения пользователя и получения ключа подтверждения
     * @param string $email E-mail пользователя
     * @param string $shablon шаблон E-mail сообщения
     * @return string ключ подтверждения
     */
    public function confirm_request($email, $shablon) {
        $key = md5(users::o()->generate_salt() . md5($email) . users::o()->generate_salt());
        $link = furl::o()->construct("registration", array(
            "ckey" => $key));
        $this->send_mail($email, $shablon, array(
            "link" => $link));
        return $key;
    }

}

?>