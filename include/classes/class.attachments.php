<?php

/**
 * Project:             CTRev
 * @file                include/classes/class.attachments.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Реализация вложений
 * @version             1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class attachments extends pluginable_object {
    /**
     * Префикс в имени файла вложений, хранимых на сервере
     */

    const attach_prefix = "a";

    /**
     * Статус системы вложений
     * @var bool $state
     */
    protected $state = true;

    /**
     * ID последнего вложения
     * @var int $curid
     */
    protected $curid = 0;

    /**
     * Данный тип вложений
     * @var type $type 
     */
    protected $type = "content";

    /**
     * Допустимые типы вложений
     * @var array $allowed_types
     */
    protected $allowed_types = array('content');

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->state = (bool) config::o()->mstate('attach_manage');
        $this->access_var('allowed_types', PVAR_ADD);
        /**
         * @note Добавление вложений(add_attachments)
         * params:
         * int toid ID ресурса
         * string type тип ресурса
         */
        tpl::o()->register_function("add_attachments", array(
            $this,
            "add"));
        /**
         * @note Отображение вложений(display_attachments)
         * params:
         * int toid ID ресурса
         * string type тип ресурса
         */
        tpl::o()->register_function("display_attachments", array(
            $this,
            "display"));
    }

    /**
     * Изменение типа вложений
     * @param string $type имя типа
     * @return attachments $this
     */
    public function change_type($type) {
        if (!in_array($type, $this->allowed_types))
            return $this;
        $this->type = $type;
        return $this;
    }

    /**
     * Форма загрузки
     * @param int $toid ID ресурса
     * @return null
     */
    public function add($toid = null) {
        if (!$this->state)
            return;
        if (is_array($toid)) {
            if ($toid ['type'])
                $this->change_type($toid ['type']);
            $toid = $toid['toid'];
        }
        try {
            users::o()->perm_exception()->check_perms('attach', 2);
        } catch (EngineException $e) {
            n("message")->stype('error')->info($e->getEMessage());
            return;
        }
        $type = $this->type;
        $toid = (int) $toid;
        $postfix = self::attach_prefix . $this->curid . $type;
        if ($toid) {
            $rows = db::o()->p($toid, $type)->query("SELECT id, filename FROM attachments 
                WHERE toid = ? AND type = ?");
            tpl::o()->assign('attachments', db::o()->fetch2array($rows));
        }
        tpl::o()->assign('postfix_att', $postfix);
        tpl::o()->display("attachments/list.tpl");
        display::o()->uploadify($postfix, "", "", array(
            "module" => "attach_manage",
            "act" => "upload",
            "type" => $type,
            "toid" => $toid), "attach_add", true, false);
        $this->curid++;
    }

    /**
     * Присвоение вложению ресурса
     * @param array $data массив данных
     * @param int $toid ID ресурса
     * @return bool true, в случае успешного выполнения
     */
    public function define_toid($data, $toid) {
        if (!$this->state)
            return;
        if (!users::o()->perm('attach', 2) || !users::o()->v())
            return;
        $id = $data ["attachments"];
        if (!$id)
            return;
        $toid = (int) $toid;
        $where = (!is_array($id) ? 'id = ?' : 'id IN (@' . count($id) . '?)');
        $where .= ' AND toid = 0 AND user = ?';
        return db::o()->p($id, users::o()->v("id"))->update(array(
                    "toid" => $toid,
                    "type" => $this->type), "attachments", 'WHERE ' . $where);
    }

    /**
     * Загрузка вложения
     * @param int $toid ID ресурса
     * @param string $files_var ключ для массива $_FILES
     * @return int ID вложения
     * @throws EngineException
     */
    public function upload($toid = null, $files_var = "Filedata") {
        if (!$this->state)
            return;
        users::o()->check_perms('attach', 2);
        lang::o()->get("file");
        if (!$files_var)
            $files_var = "Filedata";
        $type = $this->type;
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $toid = (int) $toid;
        $time = timer(true);
        $user = users::o()->v('id');
        $new_name = self::attach_prefix . default_filename($time, $user);
        $filesize = @filesize($_FILES [$files_var] ['tmp_name']);
        $uploader->upload_preview(config::o()->v("attachpreview_folder"), "", true);
        $uploader->upload($_FILES [$files_var], config::o()->v("attachments_folder"), $filetype, $new_name, true);
        $preview = $uploader->get_preview();
        $insert = array(
            "filename" => display::o()->html_encode($_FILES [$files_var] ['name']), // Автоматически не экранируется
            'preview' => $preview,
            "size" => $filesize,
            "time" => $time,
            "ftype" => $filetype,
            "toid" => $toid,
            "type" => $type,
            "user" => $user);
        try {
            plugins::o()->pass_data(array("insert" => &$insert), true)->run_hook('attachments_upload');
        } catch (PReturn $e) {
            return $e->r();
        }
        $id = db::o()->insert($insert, "attachments");
        return $id;
    }

    /**
     * Отображение вложений для данного ресурса
     * @param int $toid ID ресурса
     * @return null
     */
    public function display($toid) {
        if (!$this->state)
            return;
        if (is_array($toid)) {
            if ($toid["type"])
                $this->change_type($toid["type"]);
            $toid = $toid["toid"];
        }
        if (!users::o()->perm('attach', 1, 2))
            return;
        $type = $this->type;
        $toid = (int) $toid;
        $rows = db::o()->p($toid, $type)->query("SELECT a.*, aft.image AS ftimage FROM attachments AS a
            LEFT JOIN allowed_ft AS aft ON aft.name=a.ftype 
            WHERE toid = ? AND type=?");
        tpl::o()->assign('attach_rows', db::o()->fetch2array($rows));
        tpl::o()->assign('slbox_mbinited', true); // Инициализовать SexyLightbox
        tpl::o()->display("attachments/show.tpl");
    }

    /**
     * Удаление вложения
     * @param int|array $id ID вложения
     * @return bool true, в случае успешного выполнения
     */
    public function delete($id) {
        if (!$this->state)
            return;
        users::o()->check_perms('attach', 2);
        if (!$id)
            return;
        $where = (!is_array($id) ? 'id = ?' : 'id IN(@' . count($id) . '?)');
        $rows = db::o()->p($id)->query("SELECT * FROM attachments
            WHERE " . $where);
        $uf = ROOT . config::o()->v("attachments_folder") . "/" . self::attach_prefix;
        $ufp = ROOT . config::o()->v("attachpreview_folder") . "/";
        while ($row = db::o()->fetch_assoc($rows)) {
            $type = $row['type'];
            if (($row ["user"] == users::o()->v("id") && users::o()->perm('edit_' . $type)) || users::o()->perm('edit_' . $type, 2)) {
                try {
                    plugins::o()->pass_data(array("row" => &$row), true)->run_hook('attachments_delete');
                } catch (PReturn $e) {
                    if (!$e->r())
                        continue;
                    return $e->r();
                }
                $ids [] = $row ["id"];
                @unlink($uf . default_filename($row['time'], $row['user']));
                @unlink($ufp . $row ["preview"]);
            }
        }
        if ($ids)
            return db::o()->p($ids)->delete("attachments", 'WHERE id IN(@' . count($ids) . '?)');
    }

    /**
     * Очистка вложений
     * @param int $toid ID ресурса
     * если не указано, то будут удалены все вложения без ресурсов
     * @param bool $all все?
     * @return bool true, в случае успешного выполнения
     */
    public function clear($toid = 0, $all = false) {
        if (!$this->state)
            return;
        $toid = (int) $toid;
        $type = $this->type;
        $where = $toid ? "toid=? AND type=?" : 'toid=0';
        $r = db::o()->p($toid, $type)->query('SELECT id FROM attachments' . (!$all ? ' WHERE ' . $where : ''));
        $ids = db::o()->fetch2array($r, "assoc", array("id"));
        return $this->delete($ids);
    }

    /**
     * Скачивание вложения
     * @param int $id ID вложения
     * @return null
     * @throws EngineException
     */
    public function download($id) {
        if (!$this->state)
            return;
        users::o()->check_perms('attach', 1, 2);

        $id = (int) $id;
        $q = db::o()->p($id)->query("SELECT * FROM attachments WHERE id=? LIMIT 1");
        $row = db::o()->fetch_assoc($q);
        if (!$row)
            throw new EngineException('file_not_exists');
        $file = config::o()->v("attachments_folder") . "/" . self::attach_prefix . default_filename($row['time'], $row['user']);
        try {
            plugins::o()->pass_data(array("row" => &$row))->run_hook('attachments_download');
        } catch (PReturn $e) {
            return $e->r();
        }
        db::o()->p($id)->update(array(
            "_cb_downloaded" => 'downloaded+1'), "attachments", 'WHERE id = ? LIMIT 1');
        /* @var $uploader uploader */
        $uploader = n("uploader");
        $uploader->download($file, display::o()->html_decode($row ["filename"]));
    }

}

?>