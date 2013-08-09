<?php

/**
 * Project:             CTRev
 * @file                modules/news.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Новости
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class news {

    /**
     * Заголовок модуля
     * @var string $title
     */
    public $title = "";

    /**
     * Инициализация новостей
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        $id = (int) $_GET['id'];
        lang::o()->get('news');
        switch ($act) {
            case "add":
            case "edit":
                if (!$id) {
                    $this->title = lang::o()->v('news_add');
                    users::o()->check_perms('news');
                }
                else
                    $this->title = lang::o()->v('news_edit');
                if (!$_POST['confirm'])
                    $this->add($id);
                else
                    $this->save($_POST, $id);
                break;
        }
    }

    /**
     * Добавление новости
     * @param int $id ID новости
     * @return null
     * @throws EngineException
     */
    protected function add($id) {
        $id = (int) $id;
        if ($id) {
            $row = db::o()->p($id)->query('SELECT * FROM news WHERE id=? LIMIT 1');
            $row = db::o()->fetch_assoc($row);
            if ($row) {
                if (users::o()->v('id') == $row ['poster_id'])
                    users::o()->check_perms('edit_news');
                else
                    users::o()->check_perms('edit_news', '2');
            }
            else
                throw new EngineException('news_are_not_exists');
            tpl::o()->assign('row', $row);
        }
        tpl::o()->display('news/add.tpl');
    }

    /**
     * Сохранение новости
     * @param array $data массив данных новости
     * @param int $id ID новости
     * @return null
     * @throws EngineException
     */
    public function save($data, $id) {
        check_formkey();
        $id = (int) $id;
        if ($id) {
            list($pid, $title) = db::o()->fetch_row(db::o()->p($id)->query('SELECT poster_id, title FROM news
                WHERE id=? LIMIT 1'));
            if ($pid) {
                if (users::o()->v('id') == $pid)
                    users::o()->check_perms('edit_news');
                else
                    users::o()->check_perms('edit_news', '2');
            }
            else
                throw new EngineException('news_are_not_exists');
        }
        $update = array('title' =>
            $data['title'],
            'content' => $data['content']);

        try {
            plugins::o()->pass_data(array('update' => &$update,
                'id' => $id), true)->run_hook('news_save');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$id) {
            $update['poster_id'] = users::o()->v('id');
            $update['posted_time'] = time();
            db::o()->insert($update, 'news');
            if (config::o()->v('news_max') && config::o()->v('news_autodelete')) {
                $m = (int) config::o()->v('news_max');
                $c = db::o()->count_rows('news') - $m;
                if ($c > 0)
                    db::o()->delete('news', 'ORDER BY posted_time LIMIT ' . $c);
            }
        } else {
            db::o()->p($id)->update($update, 'news', 'WHERE id=? LIMIT 1');
            log_add("edited_news", "user", array($title, $id));
        }
        cache::o()->remove('news');
        furl::o()->location('');
    }

}

class news_ajax {

    /**
     * Инициализация AJAX части новостей
     * @return null
     */
    public function init() {
        $id = (int) $_GET['id'];
        lang::o()->get('news');
        $act = $_GET ['act'];
        switch ($act) {
            case "delete":
                $this->delete($id);
                break;
        }
    }

    /**
     * Удаление всех новостей
     * @return null
     * @throws EngineException
     */
    public function clear() {
        check_formkey();
        users::o()->check_perms('del_news', '2');
        db::o()->truncate_table('news');
        cache::o()->remove('news');
        log_add("cleared_news", "user");
    }

    /**
     * Удаление новости
     * @param int $id ID новости
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        $id = (int) $id;
        check_formkey();
        list($pid, $title) = db::o()->fetch_row(db::o()->p($id)->query('SELECT poster_id, title FROM news
                WHERE id=? LIMIT 1'));
        if ($pid) {
            if (users::o()->v('id') == $pid)
                users::o()->check_perms('del_news');
            else
                users::o()->check_perms('del_news', '2');
        }
        else
            throw new EngineException('news_are_not_exists');
        db::o()->p($id)->delete('news', 'WHERE id=? LIMIT 1');
        cache::o()->remove('news');
        log_add("deleted_news", "user", array($title));
    }

}

?>