<?php

/**
 * Project:             CTRev
 * File:                comments_manage.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление комментариями
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class comments_manage {

    /**
     * Инициализация управления комментариями
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        switch ($act) {
            case "edit" :
                $id = longval($_POST ['id']);
                $this->edit_form($id);
                break;
            case "add" :
            case "edit_save" :
                $title = $_POST ['title'];
                $content = $_POST ['body'];
                $resid = longval($_POST ['resid']);
                $type = $_POST ['type'];
                $id = longval($_POST ['id']);
                $this->save($title, $content, $resid, $type, $id);
                break;
            case "show" :
                $resid = longval($_POST ['resid']);
                $type = $_POST ['type'];
                $this->show($resid, $type);
                break;
            case "del" :
                $id = (int) $_POST ["id"];
                $this->delete($id);
                break;
            case "quote":
                $id = (int) $_POST ["id"];
                $this->quote($id);
            default :
                break;
        }
    }

    /**
     * Метод редактирования комментария
     * @global comments $comments
     * @global lang $lang
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @param int $id ID комментария
     * @return null
     */
    protected function edit_form($id) {
        global $comments, $lang, $db, $users, $tpl;
        $id = (int) $id;
        $lang->get('comments');
        $poster = $db->query('SELECT poster_id, subject, text FROM comments WHERE id=' . $id . ' LIMIT 1');
        $poster = $db->fetch_assoc($poster);
        if (!$poster)
            return;
        if ($poster ['poster_id'] == $users->v('id'))
            $users->check_perms('edit_comm');
        else
            $users->check_perms('edit_comm', 2);
        $name = "comment_" . $id;
        $tpl->assign("subject", $poster ['subject']);
        $tpl->assign("text", $poster ['text']);
        $tpl->assign("id", $id);
        $tpl->assign("name", $name);
        $comments->add_form("", $name, $id);
    }

    /**
     * Метод сохранения комментария
     * @global comments $comments
     * @global lang $lang
     * @param string $title заголовок
     * @param string $content текст
     * @param int $resid ID ресурса
     * @param string $type тип комментариев
     * @param int $id ID комментария
     * @return null
     * @throws EngineException
     */
    protected function save($title, $content, $resid, $type, $id) {
        global $comments, $lang;
        check_formkey();
        $id = (int) $id;
        $resid = (int) $resid;
        $ret = $comments->change_type($type)->save($title, $content, $resid, $id);
        if ($ret !== true)
            die($ret);
        die("OK!");
    }

    /**
     * Метод отображения комментариев
     * @global comments $comments
     * @global lang $lang
     * @param int $resid ID ресурса
     * @param string $type тип комментариев
     * @return null
     */
    protected function show($resid, $type) {
        global $comments, $lang;
        $resid = (int) $resid;
        $lang->get('comments');
        $comments->change_type($type)->display($resid, '', true);
    }

    /**
     * Метод удаления комментария
     * @global comments $comments
     * @global lang $lang
     * @param int $id ID комментария
     * @return null
     * @throws EngineException
     */
    protected function delete($id) {
        global $comments, $lang;
        check_formkey();
        $id = (int) $id;
        $lang->get('comments');
        if (!$id)
            throw new EngineException;
        $comments->delete($id);
        die("OK!");
    }

    /**
     * Метод цитирования комментария
     * @global comments $comments
     * @param int $id ID комментария
     * @return null
     */
    protected function quote($id) {
        global $comments;
        $id = (int) $id;
        print($comments->quote($id));
        die();
    }

}

?>