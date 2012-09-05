<?php

/**
 * Project:             CTRev
 * File:                polls_manage.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление опросами
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class polls_manage {

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Инициализация опросов
     * @global polls $polls
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $polls, $lang;
        $lang->get('polls');
        $act = $_GET ['act'];
        $poll_id = (int) $_GET ['id'];
        switch ($act) {
            case "add" :
            case "edit" :
                $polls->add_form(0, $poll_id, true);
                $this->title = $lang->v('polls_title_add');
                break;
            default :
                $polls->display(0, $poll_id, $_GET ['votes'], $_GET ['short']);
                $this->title = $lang->v('polls_title');
                break;
        }
    }

}

class polls_manage_ajax {

    /**
     * Инициализация опросов AJAX методов
     * @global polls $polls
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $polls, $lang;
        $lang->get('polls');
        $act = $_GET ['act'];
        $poll_id = (int) $_GET ['id'];
        switch ($act) {
            case "vote" :
                $answers = $_POST ['answers'];
                $this->vote($poll_id, $answers);
                break;
            case "save" :
                $this->save($_POST, $poll_id);
                break;
            case "delete" :
                $this->delete($poll_id);
                break;
            default :
                break;
        }
    }

    /**
     * Сохранение опросов
     * @global polls $polls
     * @param array $data массив данных
     * @param int $poll_id ID опроса
     * @return null
     * @throws EngineException
     */
    protected function save($data, $poll_id) {
        global $polls;
        check_formkey();
        $ret = $polls->save($data, 0, $poll_id);
        die("OK!" . $ret);
        die();
    }

    /**
     * Голосование за опрос
     * @global polls $polls
     * @param int $poll_id ID опроса
     * @param integer|array $answers голоса
     * @return null
     */
    protected function vote($poll_id, $answers) {
        global $polls;
        $ret = $polls->vote($poll_id, $answers);
        die("OK!");
    }

    /**
     * Голосование за опрос
     * @global polls $polls
     * @param int $poll_id ID опроса
     * @return null
     * @throws EngineException
     */
    protected function delete($poll_id) {
        global $polls;
        check_formkey();
        $ret = $polls->delete($poll_id);
        die("OK!");
    }

}

?>