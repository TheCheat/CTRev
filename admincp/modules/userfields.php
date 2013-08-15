<?php

/**
 * Project:            	CTRev
 * @file                admincp/modules/userfields.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Управление доп. полями профиля
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class userfields_man {

    /**
     * Инициализация модуля доп. полей
     * @return null
     */
    public function init() {
        lang::o()->get('admin/userfields');
        $act = $_GET["act"];
        switch ($act) {
            case "save":
                $POST = globals::g('POST');
                $_POST['descr'] = $POST['descr'];
                $this->save($_POST);
                die();
                break;
            case "edit":
            case "add":
                $this->add($_GET['id']);
                break;
            default:
                $this->show();
                break;
        }
    }

    /**
     * Добавление/редактирование доп. полей
     * @param string $id имя поля
     * @return null
     */
    protected function add($id = null) {
        if ($id) {
            $r = db::o()->p($id)->query('SELECT * FROM users_fields WHERE field=? LIMIT 1');
            $row = db::o()->fetch_assoc($r);
            if ($row['allowed'])
                $values = @unserialize($row['allowed']);
            tpl::o()->assign('row', $row);
        }
        if (!$values)
            $values = array('', '');
        /* @var $uf userfields */
        $uf = n("userfields");
        tpl::o()->assign('types', array_keys($uf->get_var('types')));
        tpl::o()->assign('types_array', display::o()->array_export_to_js($uf->get_var('types')));
        tpl::o()->assign('values', $values);
        tpl::o()->display('admin/userfields/add.tpl');
    }

    /**
     * Функция отображения доп. полей
     * @return null
     */
    protected function show() {
        $r = db::o()->query('SELECT * FROM users_fields');
        tpl::o()->assign('res', db::o()->fetch2array($r));
        tpl::o()->register_modifier('cut_type_descr', array($this, 'cut_type_descr'));
        tpl::o()->display('admin/userfields/index.tpl');
    }

    /**
     * Очистка описани типа
     * @param string $var значение языковой переменной
     * @return string очищенное значение
     */
    public function cut_type_descr($var) {
        preg_match('/^\'[^\']+\'(.*?)$/su', $var, $matches);
        if ($matches)
            return $matches[1];
        return $var;
    }

    /**
     * Сохранение доп. полей
     * @param array $data массив данных
     * @return null
     * @throws EngineException 
     */
    public function save($data) {
        $admin_file = globals::g('admin_file');
        $oname = $data['old_field'];
        $values = (array) $data['values'];
        $keys = (array) $data['keys'];
        $cols = array(
            'field',
            'name',
            'allowed',
            'descr',
            'type',
            'show_register',
            'show_profile');
        $data = rex($data, $cols);
        $data['show_register'] = (bool) $data['show_register'];
        $data['show_profile'] = (bool) $data['show_profile'];
        if (!validword($data['field']))
            throw new EngineException('userfields_empty_field');
        if (!$data['name'])
            throw new EngineException('userfields_empty_name');
        /* @var $uf userfields */
        $uf = n("userfields");
        $ct = $uf->get_var('types', $data['type']);
        if (is_null($ct))
            throw new EngineException('userfields_empty_type');
        if ($ct) {
            $allowed = &$data['allowed'];
            if ($ct == 2) {
                $allowed = array();
                $cv = count($values);
                if ($cv == count($keys) && $cv >= 2) {
                    for ($i = 0; $i < $cv; $i++) {
                        $key = $keys[$i];
                        $value = $values[$i];
                        if (!validword($key, 'latin', 1) && !is_numeric($key))
                            continue;
                        if (!$value)
                            continue;
                        $allowed[$key] = $value;
                    }
                    $allowed = serialize($allowed);
                }
            }
            if (!$allowed)
                throw new EngineException('userfields_empty_allowed');
        }
        try {
            plugins::o()->pass_data(array('data' => &$data), true)->run_hook('admin_userfields_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($oname)
            db::o()->p($oname)->update($data, 'users_fields', 'WHERE field=? LIMIT 1');
        else {
            db::o()->insert($data, 'users_fields');
            log_add('added_userfield', 'admin', $data['field']);
        }
        cache::o()->remove('userfields');
        furl::o()->location($admin_file);
    }

}

class userfields_man_ajax {

    /**
     * Инициализация AJAX-части модуля
     * @return null
     */
    public function init() {
        lang::o()->get('admin/userfields');
        $act = $_GET["act"];
        $id = $_POST["id"];
        switch ($act) {
            case "switch":
                $this->switch_state($id, $_POST['type']);
                break;
            case "order":
                $this->save_order($_POST['ufid']);
                break;
            case "delete":
                $this->delete($id);
                break;
        }
        cache::o()->remove('userfields');
        ok();
    }

    /**
     * Сохранение порядка полей
     * @return null
     * @throws EngineException
     */
    public function save_order($sort) {
        if (!$sort)
            throw new EngineException;
        foreach ($sort as $s => $id)
            db::o()->p($id)->update(array('sort' => (int) $s), 'users_fields', 'WHERE field=? LIMIT 1');
        db::o()->query('ALTER TABLE `users_fields` ORDER BY `sort`');
    }

    /**
     * Изменение хар-ки поля
     * @param string $id имя поля
     * @param string $type тип хар-ки
     * @return null
     */
    public function switch_state($id, $type = "show_profile") {
        switch ($type) {
            case "show_register":
            case "show_profile":
                break;
            default:
                $type = 'show_profile';
                break;
        }
        db::o()->p($id)->update(array('_cb_' . $type => 'IF(' . $type . '="1","0","1")'), 'users_fields', '
            WHERE field=? LIMIT 1');
    }

    /**
     * Удаление поля
     * @param string $id имя поля
     * @return null
     */
    public function delete($id) {
        db::o()->p($id)->delete('users_fields', 'WHERE field=? LIMIT 1');
        log_add('deleted_userfield', 'admin', $id);
    }

}

?>