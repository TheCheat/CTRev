<?php

/**
 * Project:             CTRev
 * @file                modules/attach_manage.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление вложениями
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class attach_manage {

    /**
     * Преинициализация AJAX части для объявления констант
     * @return null 
     */
    public function pre_init() {
        $act = $_GET ['act'];
        if ($act == "upload")
            define('ALLOW_REQUEST_COOKIES', true);
    }

    /**
     * Инициализация управления вложениями
     * @return null
     */
    public function init() {
        /* @var $attach attachments */
        $attach = n("attachments");
        $act = $_GET ["act"];
        switch ($act) {
            case "upload" :
                $type = $_GET ["type"];
                $toid = $_GET ["toid"];
                $ret = $attach->change_type($type)->upload($toid);
                ok(true);
                print($ret);
                break;
            case "download" :
                $attach_id = (int) $_GET ["id"];
                $preview = (int) $_GET ["preview"];
                $attach->download($attach_id, $preview);
                break;
            case "delete" :
                $attach_id = $_POST ["id"];
                $ret = $attach->delete($attach_id);
                if (!$ret)
                    throw new Exception;
                ok();
                break;
        }
        die();
    }

}

?>