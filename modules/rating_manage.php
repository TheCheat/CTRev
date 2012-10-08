<?php

/**
 * Project:             CTRev
 * File:                rating_manage.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление рейтингом
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class rating_manage {
    
    /**
     * Объект рейтинга
     * @var rating
     */
    protected $rating = null;

    /**
     * Инициализация рейтинга
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        $type = $_POST ['type'];
        $toid = $_POST ['toid'];
        $this->rating = n("rating");
        switch ($act) {
            case "vote" :
                $value = $_POST ['value'];
                $stype = $_POST['stype'];
                $stoid = $_POST['stoid'];
                $this->vote_to($type, $toid, $value, $stoid, $stype);
                break;
            case "get" :
                $this->get($type, $toid);
                break;
            default :
                break;
        }
    }

    /**
     * Метод голосования
     * @param string $type тип ресурса
     * @param int $toid ID ресурса
     * @param float $value значение голоса
     * @param int $stoid доп. ID ресурса
     * @param string $stype доп. тип ресурса
     * @return null
     */
    protected function vote_to($type, $toid, $value, $stoid, $stype) {
        $error = $this->rating->change_type($type)->change_stype($stype)->vote($toid, $value, $stoid);
        die("OK!");
    }

    /**
     * Получение среднего значения рейтинга
     * @param string $type тип ресурса
     * @param int $toid ID ресурса
     * @return null
     */
    protected function get($type, $toid) {
        die($this->rating->change_type($type)->get_avg_rating($toid));
    }

}

?>