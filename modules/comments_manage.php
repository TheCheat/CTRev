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
     * Объект комментариев
     * @var comments $comments
     */
    protected $comments = null;

    /**
     * Инициализация управления комментариями
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        $this->comments = n("comments");
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
     * @param int $id ID комментария
     * @return null
     */
    protected function edit_form($id) {
        $id = (int) $id;
        lang::o()->get('comments');
        $poster = db::o()->query('SELECT poster_id, subject, text FROM comments WHERE id=' . $id . ' LIMIT 1');
        $poster = db::o()->fetch_assoc($poster);
        if (!$poster)
            return;
        if ($poster ['poster_id'] == users::o()->v('id'))
            users::o()->check_perms('edit_comm');
        else
            users::o()->check_perms('edit_comm', 2);
        $name = "comment_" . $id;
        tpl::o()->assign("subject", $poster ['subject']);
        tpl::o()->assign("text", $poster ['text']);
        tpl::o()->assign("id", $id);
        tpl::o()->assign("name", $name);
        $this->comments->add_form("", $name, $id);
    }

    /**
     * Метод сохранения комментария
     * @param string $title заголовок
     * @param string $content текст
     * @param int $resid ID ресурса
     * @param string $type тип комментариев
     * @param int $id ID комментария
     * @return null
     * @throws EngineException
     */
    protected function save($title, $content, $resid, $type, $id) {
        check_formkey();
        $id = (int) $id;
        $resid = (int) $resid;
        $ret = $this->comments->change_type($type)->save($title, $content, $resid, $id);
        if ($ret !== true)
            die($ret);
        die("OK!");
    }

    /**
     * Метод отображения комментариев
     * @param int $resid ID ресурса
     * @param string $type тип комментариев
     * @return null
     */
    protected function show($resid, $type) {
        $resid = (int) $resid;
        lang::o()->get('comments');
        $this->comments->change_type($type)->display($resid, '', true);
    }

    /**
     * Метод удаления комментария
     * @param int $id ID комментария
     * @return null
     * @throws EngineException
     */
    protected function delete($id) {
        check_formkey();
        $id = (int) $id;
        lang::o()->get('comments');
        if (!$id)
            throw new EngineException;
        $this->comments->delete($id);
        die("OK!");
    }

    /**
     * Метод цитирования комментария
     * @param int $id ID комментария
     * @return null
     */
    protected function quote($id) {
        $id = (int) $id;
        print($this->comments->quote($id));
        die();
    }

}

?>