<?php

/**
 * Project:            	CTRev
 * File:                class.etc.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @var bool
     */
    protected $signed_res = false;

    /**
     * Значение для {@link $count}, когда обнуляется счётчик 
     * @see etc::add_res()
     */

    const reset_count = '<reset>';

    /**
     * Выборка пользователей
     * @global db $db
     * @param int $id ID пользователя
     * @param string $username имя пользователя
     * @param string $columns выбираемые столбцы
     * @return array результат
     */
    public function select_user($id, $username = null, $columns = null) {
        global $db;
        $id = (int) $id;
        if (!$id && !$username)
            return;
        $where = "";
        if ($id)
            $where .= "id = " . $id;
        if ($username)
            $where .= ( $where ? " AND " : "") . "username_lower=" . $db->esc(mb_strtolower($username));
        $ret = $db->query('SELECT ' . ($columns ? $columns : "*") . ' FROM users' . ($where ? " WHERE " . $where : "") . ' LIMIT 1');
        return $db->fetch_assoc($ret);
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
     * @global db $db
     * @global users $users
     * @param string|array $type тип ресурса(напр. torrents, comments)
     * @param int|string $count добавляемое кол-во(если равняется reset_count - обнуляется)
     * @param string $table обновляемая таблица
     * @param int $id ID обновляемый
     * @param string $column проверяемый столбец(для условия)
     * @return null
     */
    public function add_res($type = 'torrents', $count = 1, $table = "users", $id = null, $column = 'id') {
        global $db, $users;
        if (!$column)
            $column = 'id';
        if ($column == 'id') {
            $id = (int) $id;
            if (!$id)
                $id = $users->v('id');
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
        $db->update($columns, $table, 'WHERE `' . $column . '`=' . $db->esc($id) . ' LIMIT 1');
        $this->signed_res(false);
    }

    /**
     * Отправка сообщения с подстановкой переменных
     * @global string $BASEURL
     * @global config $config
     * @global lang $lang
     * @param string|array $email E-mail пользователя
     * @param string $shablon шаблон сообщения
     * @param array $vars массив переменных
     * @return string|int статус отыслки или список ошибок
     */
    public function send_mail($email, $shablon, $vars = array()) {
        global $BASEURL, $config, $lang;
        $lang->get("mail_shablons");
        $body = $lang->v("mail_body_" . $shablon);
        $vars['siteurl'] = $BASEURL;
        $vars['sitename'] = $vars['site_title'] = $config->v('site_title');
        foreach ($vars as $key => $value)
            $body = str_replace('$' . $key, $value, $body);
        send_mail($lang->v("mail_subject_" . $shablon), $body, $email, $error);
        return $error;
    }

    /**
     * Отправка ЛС пользователю
     * @global db $db
     * @global furl $furl
     * @global bbcodes $bbcodes
     * @global users $users
     * @global lang $lang
     * @param string $title заголовок сообщения
     * @param string $body тело сообщения
     * @param int $uid ID пользователя
     * @param string $email E-mail получателя
     * @param int $sender ID отправителя
     * @return bool статус отправки
     */
    public function send_message($title, $body, $uid, $email = null, $sender = null) {
        global $db, $furl, $bbcodes, $users, $lang;
        if (!$sender)
            $sender = $users->v('id');
        $uid = (int) $uid;
        if (!$title)
            $title = $lang->v('pmessage_empty_subject');
        $id = $db->insert(array(
            "subject" => $title,
            "text" => $body,
            "sender" => $sender,
            "receiver" => $uid,
            "time" => time()), "pmessages");
        if ($email) {
            $link = $furl->construct("pm", array(
                "act" => "read",
                "id" => $id));
            $this->send_mail($email, "getted_pm", array(
                "link" => $link,
                "subject" => $title,
                "body" => $bbcodes->format_text($body, "SIMPLE")));
        }
        return ((bool) $id);
    }

    /**
     * Блокировка пользователя
     * @global db $db
     * @global users $users
     * @global plugins $plugins
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
        global $db, $users, $plugins;
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
        $columns["byuid"] = $users->v('id');
        if ($period) {
            $columns["to_time"] = ($period ? time() + $period * 3600 : 0 );
            $columns["period"] = $period;
        }
        try {
            $plugins->pass_data(array('update' => &$columns), true)->run_hook('users_ban');
        } catch (PReturn $e) {
            return $e->r();
        }
        if (!$id)
            $db->insert($columns, "bans");
        else
            $db->update($columns, "bans", "WHERE id = " . $id . " LIMIT 1");
        if ($uid)
            $db->update(array(
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
     * @global db $db
     * @global plugins $plugins
     * @param int $uid ID пользователя
     * @param int $id ID бана
     * @return bool статус разбана
     */
    public function unban_user($uid, $id = null) {
        global $db, $plugins;
        $uid = (int) $uid;
        $id = (int) $id;
        if (!$uid && !$id)
            return false;
        if (!$uid)
            list($uid) = $db->fetch_row($db->query('SELECT uid FROM bans WHERE id=' . $id . ' LIMIT 1'));
        try {
            $plugins->pass_data(array('uid' => $uid,
                'id' => $id), true)->run_hook('users_unban');
        } catch (PReturn $e) {
            return $e->r();
        }
        $db->update(array(
            "_cb_group" => '`old_group`',
            "old_group" => 0), "users", "WHERE id=" . $uid . " AND `old_group`<>0 LIMIT 1");
        $db->delete("bans", " WHERE " . ($id ? "id=" . $id . ($uid ? " AND " : "") : "") .
                ($uid ? "uid=" . $uid : "") . " LIMIT 1");
        log_add("unbanned", 'admin', $id, $uid);
        return true;
    }

    /**
     * Предупреждение пользователя
     * @global db $db
     * @global lang $lang
     * @global users $users
     * @global config $config
     * @global plugins $plugins
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
        global $db, $lang, $users, $config, $plugins;
        $lang->get('admin/bans');
        $uid = (int) $uid;
        if ((!$uid && !$id) || !$reason)
            return false;
        $columns = array(
            "reason" => $reason);
        if ($uid) {
            $columns["uid"] = $uid;
            $columns["byuid"] = $users->v('id');
            $columns["time"] = time();
            if (is_null($warns) || $warns === true || (!$email && $notify)) {
                $r = $this->select_user($uid, null, "email,warnings_count");
                $warns = $r["warnings_count"];
                $email = $r["email"];
            }
        }
        try {
            $plugins->pass_data(array('update' => &$columns), true)->run_hook('users_warn');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($uid)
            $this->add_res('warnings', 1, "users", $uid);
        if (!$id)
            $db->insert($columns, "warnings");
        else
            $db->update($columns, "warnings", "WHERE id = " . $id . " LIMIT 1");
        if ($uid) {
            if ($config->v('warn2ban') && $warns + 1 >= $config->v('warn2ban') && $warns !== false)
                $this->ban_user($uid, $config->v('warn2ban_days'), sprintf($lang->v('warnings_ban_reason'), $config->v('warn2ban')));
            if ($notify) {
                $title = $lang->v('warnings_user_warned_title');
                $body = sprintf($lang->v('warnings_user_warned_body'), smarty_group_color_link($users->v('username'), $users->v('group'), true), $reason);
                $this->send_message($title, $body, $uid, $email);
            }
            log_add("warned", 'admin', null, $uid);
        }
        return $columns;
    }

    /**
     * Снятие предупреждения с пользователя
     * @global db $db
     * @global config $config
     * @global plugins $plugins
     * @param int $uid ID пользователя
     * @param int $warns кол-во предупреждений
     * @param int $id ID предупреждения
     * @return bool статус снятия
     */
    public function unwarn_user($uid, $warns = null, $id = null) {
        global $db, $config, $plugins;
        $uid = (int) $uid;
        $id = (int) $id;
        if (!$uid && !$id)
            return false;
        if (!$uid)
            list($uid) = $db->fetch_row($db->query('SELECT uid FROM warnings WHERE id=' . $id . ' LIMIT 1'));
        if (is_null($warns) || $warns === true) {
            $r = $this->select_user($uid, null, "warnings_count");
            $warns = $r["warnings_count"];
        }
        try {
            $plugins->pass_data(array('uid' => $uid,
                'id' => $id,
                'warns' => $warns), true)->run_hook('users_unwarn');
        } catch (PReturn $e) {
            return $e->r();
        }
        $this->add_res('warnings', -1, "users", $uid);
        if ($config->v('warn2ban') && $warns - 1 < $config->v('warn2ban') && $warns !== false)
            $this->unban_user($uid);
        $db->delete("warnings", "WHERE " . ($id ? "id=" . $id . " AND " : "") . "uid=" . $uid, 1);
        log_add("unwarned", 'admin', null, $uid);
        return true;
    }

    /**
     * Изменение группы пользователя
     * @global db $db
     * @global users $users
     * @global plugins $plugins
     * @param int $uid ID пользователя
     * @param int $gid ID новой группы
     * @param bool $nu без обновления
     * @return bool статус изменения
     */
    public function change_group($uid, $gid, $nu = false) {
        global $db, $users, $plugins;
        if (!$users->perm('system'))
            return false;
        $gid = (int) $gid;
        $uid = (int) $uid;
        $gr = $users->get_group($gid);
        if (!$gr || $gr['guest'])
            return false;
        try {
            $plugins->pass_data(array('gid' => $gid,
                'uid' => $uid), true)->run_hook('users_change_group');
        } catch (PReturn $e) {
            return $e->r();
        }
        /* $r = $this->select_user($id, null, '`group`, old_group');
          $gr = $users->get_group($r['group']);
          if ($gr['system'] || $r['old_group'])
          return false; */
        if (!$nu)
            $db->update(array('group' => $gid), 'users', 'WHERE id =' . $uid . ' LIMIT 1');
        log_add('changed_group', 'admin', array($users->get_group_name($gid), $gid), $uid);
        return true;
    }

    /**
     * Удаление торрента
     * @global plugins $plugins
     * @param int $id ID торрента
     * @param EngineException $exc исключение, если есть
     * @return null
     */
    public function delete_torrent($id, &$exc = null) {
        global $plugins;
        $id = (int) $id;
        $torrents = $plugins->get_module('torrents', false, true);
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
     * @global db $db
     * @global etc $etc
     * @global plugins $plugins
     * @global comments $comments
     * @global mailer $mailer
     * @global rating $rating
     * @param int $id ID пользователя
     * @return bool статус удаления
     */
    public function delete_user($id) {
        global $db, $users, $plugins, $comments, $mailer, $rating;
        $id = (int) $id;
        $r = $this->select_user($id, null, '`group`, avatar, username');
        if (!$r)
            return;
        $gr = $users->get_group($r['group']);
        if (!$gr['can_bedeleted'])
            return false;
        try {
            $plugins->pass_data(array('r' => $r,
                'id' => $id), true)->run_hook('users_delete');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($r['avatar'])
            $this->remove_user_avatar($id, $r['avatar']);
        $b = $users->admin_mode(true);
        $db->delete("bans", "WHERE uid = " . $id);
        $db->delete("warnings", "WHERE uid = " . $id);
        $db->delete("downloaded", "WHERE uid = " . $id);
        $pm = $plugins->get_module('messages', false, true);
        $pm->clear($id);
        $db->delete("read_torrents", "WHERE user_id = " . $id);
        $mailer->remove($id, true);
        $rating->change_type('users')->clear($id);
        $db->delete("zebra", "WHERE user_id = " . $id . " OR to_userid = " . $id);
        $db->delete("bookmarks", "WHERE user_id = " . $id);
        $db->delete("invites", "WHERE user_id = " . $id);
        $db->delete("peers", "WHERE uid = " . $id);
        $comments->change_type('users')->clear($id);
        $db->delete("users", "WHERE id = " . $id);
        if (!$b)
            $users->admin_mode();
        log_add('deleted_user', 'admin', array($r['username'], $id));
        return true;
    }

    /**
     * Удаление аватары пользователя
     * @global uploader $uploader
     * @global config $config
     * @param int $id ID пользователя
     * @param string $avatar значение поля vatar пользователя
     * @return null
     */
    public function remove_user_avatar($id, $avatar) {
        global $uploader, $config;
        $uploader->init_ft();
        $avatar_name = display::avatar_prefix . $id;
        $aft = $uploader->filetypes('avatars');
        if (preg_match('/^' . mpc($avatar_name) . '\.(' . str_replace(";", "|", $aft ['types']) . ')$/siu', $avatar))
            @unlink(ROOT . $config->v('avatars_folder') . "/" . $avatar);
    }

    /**
     * Осуществление подтверждения пользователя
     * @global db $db
     * @global config $config
     * @global lang $lang
     * @param int $act степень подтверждения
     * @param int $now_conf степень подтверждения на данный момент
     * @param int $id ID пользователя
     * @return int степень подтверждения
     */
    public function confirm_user($act, $now_conf, $id = null) {
        global $db, $config, $lang;
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
                    return $lang->v('not_confirmed_email');
                if (!$config->v('confirm_admin'))
                    $confirm = 3;
                else
                    $confirm = 2;
                break;
            default:
                if ($config->v('confirm_email')) {
                    $confirm = 0;
                    break;
                }
            case 1:
                if (!$config->v('confirm_admin') && ($config->v('allowed_register') || !$config->v('allowed_invite')))
                    $confirm = 3;
                elseif ($config->v('allowed_register') || !$config->v('allowed_invite'))
                    $confirm = 2;
                else
                    $confirm = 1;
                break;
        }
        if ($now_conf >= $confirm)
            return $now_conf;
        if ($id)
            $db->update(array(
                "confirmed" => $confirm), "users", ( 'id=' . $id), 1);
        return $confirm;
    }

    /**
     * Функция для запроса подтверждения пользователя и получения ключа подтверждения
     * @global furl $furl
     * @global users $users
     * @param string $email E-mail пользователя
     * @param string $shablon шаблон E-mail сообщения
     * @return string ключ подтверждения
     */
    public function confirm_request($email, $shablon) {
        global $furl, $users;
        $key = md5($users->generate_salt() . md5($email) . $users->generate_salt());
        $link = $furl->construct("registration", array(
            "ckey" => $key));
        $this->send_mail($email, $shablon, array(
            "link" => $link));
        return $key;
    }

}

?>