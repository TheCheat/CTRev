<?php

/**
 * Project:             CTRev
 * @file                modules/comments_manage.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
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
                $content = $_POST ['body'];
                $resid = longval($_POST ['resid']);
                $type = $_POST ['type'];
                $id = longval($_POST ['id']);
                $this->save($content, $resid, $type, $id);
                ok();
                break;
            case "show" :
                $resid = longval($_POST ['resid']);
                $type = $_POST ['type'];
                $this->show($resid, $type);
                break;
            case "del" :
                $id = (int) $_POST ["id"];
                $this->delete($id);
                ok();
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
        $poster = db::o()->p($id)->query('SELECT poster_id, text FROM comments WHERE id=? LIMIT 1');
        $poster = db::o()->fetch_assoc($poster);
        if (!$poster)
            return;
        if ($poster ['poster_id'] == users::o()->v('id'))
            users::o()->check_perms('edit_comm');
        else
            users::o()->check_perms('edit_comm', 2);
        $name = "comment_" . $id;
        tpl::o()->assign("text", $poster ['text']);
        tpl::o()->assign("id", $id);
        tpl::o()->assign("name", $name);
        $this->comments->add("", $name, $id);
    }

    /**
     * Метод сохранения комментария
     * @param string $content текст
     * @param int $resid ID ресурса
     * @param string $type тип комментариев
     * @param int $id ID комментария
     * @return null
     * @throws EngineException
     */
    protected function save($content, $resid, $type, $id) {
        check_formkey();
        $id = (int) $id;
        $resid = (int) $resid;
        $ret = $this->comments->change_type($type)->save($content, $resid, $id);
        if ($ret !== true)
            throw new EngineException($ret);
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
    }

    /**
     * Метод цитирования комментария
     * @param int $id ID комментария
     * @return null
     */
    protected function quote($id) {
        $id = (int) $id;
        print($this->comments->quote($id));
    }

}

?>