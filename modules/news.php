<?php

/**
 * Project:             CTRev
 * File:                news.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
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
     * @var string
     */
    public $title = "";

    /**
     * Инициализация новостей
     * @global lang $lang
     * @global users $users
     * @return null
     */
    public function init() {
        global $lang, $users;
        $act = $_GET ['act'];
        $id = (int) $_GET['id'];
        $lang->get('news');
        switch ($act) {
            case "add":
            case "edit":
                if (!$id) {
                    $this->title = $lang->v('news_adding');
                    $users->check_perms('news');
                } else
                    $this->title = $lang->v('news_editing');
                if (!$_POST['confirm'])
                    $this->add($id);
                else
                    $this->save($_POST, $id);
                break;
        }
    }

    /**
     * Добавление новости
     * @global db $db
     * @global tpl $tpl
     * @global users $users
     * @param int $id ID новости
     * @return null
     * @throws EngineException
     */
    protected function add($id) {
        global $db, $tpl, $users;
        $id = (int) $id;
        if ($id) {
            $row = $db->query('SELECT * FROM news WHERE id=' . longval($id) . ' LIMIT 1');
            $row = $db->fetch_assoc($row);
            if ($row) {
                if ($users->v('id') == $row ['poster_id'])
                    $users->check_perms('edit_news');
                else
                    $users->check_perms('edit_news', '2');
            } else
                throw new EngineException('news_are_not_exists');
            $tpl->assign('row', $row);
        }
        $tpl->display('news/add.tpl');
    }

    /**
     * Сохранение новости
     * @global db $db
     * @global users $users
     * @global furl $furl
     * @global config $config
     * @global cache $cache
     * @global plugins
     * @param array $data массив данных новости
     * @param int $id ID новости
     * @return null
     * @throws EngineException
     */
    protected function save($data, $id) {
        global $db, $users, $furl, $config, $cache, $plugins;
        check_formkey();
        $id = (int) $id;
        if ($id) {
            list($pid, $title) = $db->fetch_row($db->query('SELECT poster_id, title FROM news
                WHERE id=' . $id . ' LIMIT 1'));
            if ($pid) {
                if ($users->v('id') == $pid)
                    $users->check_perms('edit_news');
                else
                    $users->check_perms('edit_news', '2');
            } else
                throw new EngineException('news_are_not_exists');
        }
        $update = array('title' =>
            $data['title'],
            'content' => $data['content']);
        
        try {
            $plugins->pass_data(array('update' => &$update,
                'id' => $id), true)->run_hook('news_save');
        } catch (PReturn $e) {
            return $e->r();
        }
        
        if (!$id) {
            $update['poster_id'] = $users->v('id');
            $update['posted_time'] = time();
            $db->insert($update, 'news');
            if ($config->v('news_max') && $config->v('news_autodelete')) {
                $c = $db->count_rows('news') - $config->v('news_max');
                if ($c > 0)
                    $db->delete('news', 'ORDER BY posted_time LIMIT ' . $c);
            }
        } else {
            $db->update($update, 'news', 'WHERE id=' . $id . ' LIMIT 1');
            log_add("edited_news", "user", array($title, $id));
        }
        $cache->remove('news');
        $furl->location('');
    }

}

class news_ajax {

    /**
     * Инициализация AJAX части новостей
     * @global lang $lang
     * @return null
     */
    public function init() {
        global $lang;
        $id = (int) $_GET['id'];
        $lang->get('news');
        $act = $_GET ['act'];
        switch ($act) {
            case "delete":
                $this->delete($id);
                break;
        }
    }

    /**
     * Удаление всех новостей
     * @global db $db
     * @global cache $cache
     * @global users $users
     * @return null
     * @throws EngineException
     */
    public function clear() {
        global $db, $cache, $users;
        check_formkey();
        $users->check_perms('del_news', '2');
        $db->truncate_table('news');
        $cache->remove('news');
        log_add("cleared_news", "user");
    }

    /**
     * Удаление новости
     * @global db $db
     * @global users $users
     * @global cache $cache
     * @param int $id ID новости
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        global $db, $users, $cache;
        $id = (int) $id;
        check_formkey();
        list($pid, $title) = $db->fetch_row($db->query('SELECT poster_id, title FROM news
                WHERE id=' . $id . ' LIMIT 1'));
        if ($pid) {
            if ($users->v('id') == $pid)
                $users->check_perms('del_news');
            else
                $users->check_perms('del_news', '2');
        } else
            throw new EngineException('news_are_not_exists');
        $db->delete('news', 'WHERE id=' . $id . ' LIMIT 1');
        $cache->remove('news');
        log_add("deleted_news", "user", array($title));
    }

}

?>