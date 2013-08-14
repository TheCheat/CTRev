<?php

/**
 * Project:             CTRev
 * @file                modules/content.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Модуль контента
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class content_add {
    /**
     * Префикс в имени скриншота, хранимого на сервере
     */

    const image_prefix = "i";

    /**
     * Заголовок модуля
     * @var string $title
     */
    public $title = "";

    /**
     * Объект категорий
     * @var categories $cats
     */
    protected $cats = null;

    /**
     * Макс. ширина для превью постера
     * @var int $poster_width
     */
    protected $poster_width = 200;

    /**
     * Макс. высота для превью постера
     * @var int $poster_height
     */
    protected $poster_height = 300;

    /**
     * Макс. ширина для превью скриншота
     * @var int $scr_width
     */
    protected $scr_width = 150;

    /**
     * Макс. высота для превью скриншота
     * @var int $scr_height
     */
    protected $scr_height = 150;

    /**
     * Включены ли торренты?
     * @var bool $tstate
     */
    protected $tstate = true;

    /**
     * Конструктор
     * @return null
     */
    public function __construct() {
        $this->cats = n("categories");
        $this->tstate = config::o()->v('torrents_on');
    }

    /**
     * Добавление контента
     * @param string $cat имя категории
     * @param int $id ID контента
     * @return null
     * @throws EngineException 
     */
    public function add($cat, $id = null) {
        lang::o()->get('content');
        $id = (int) $id;
        if ($id) {
            $lj = $cols = "";
            if ($this->tstate) {
                $cols = ", t.*";
                $lj = " LEFT JOIN content_torrents AS t ON t.cid=c.id";
            }
            $row = db::o()->p($id)->query('SELECT c.* ' . $cols . ' FROM content AS c ' . $lj . ' WHERE c.id=? LIMIT 1');
            $row = db::o()->fetch_assoc($row);
            if ($row) {
                if ($this->tstate && $row["banned"] == 2)
                    throw new EngineException("content_torrent_cant_be_edited");
                $this->title .= ' "' . $row["title"] . '"';
                $adder = $row ['poster_id'];
                $cat = $row ['category_id'];
                if (users::o()->v('id') == $adder)
                    users::o()->check_perms('edit_content');
                else
                    users::o()->check_perms('edit_content', '2');
                if ($this->tstate)
                    $row["screenshots"] = unserialize($row["screenshots"]);
                tpl::o()->assign('nrow', $row);
                tpl::o()->assign('id', $id);
            }
            else
                throw new EngineException('content_not_exists');
        }
        if ($this->tstate && !$row['screenshots']) {
            $row['screenshots'] = array(array(), array());
            tpl::o()->assign('nrow', $row);
        }

        try {
            plugins::o()->pass_data(array('row' => &$row), true)->run_hook('content_add');
        } catch (PReturn $e) {
            return $e->r();
        }

        tpl::o()->assign('categories_selector', $this->cats->ajax_selector($cat));
        tpl::o()->assign("num", 0);
        n('polls'); // для add_polls
        n('attachments'); // для add_attachments
        tpl::o()->display('content/add.tpl');
    }

    /**
     * Получение файлового массива в случае, если несколько файлов
     * @param array $f переменная $_FILES
     * @param int $i номер элемента
     * @return array файловый массив
     */
    protected function get_files_array($f, $i) {
        $v = array('tmp_name', 'name', 'size', 'type', 'error');
        $c = count($v);
        $r = array();
        for ($j = 0; $j < $c; $j++)
            $r[$v[$j]] = $f[$v[$j]][$i];
        return $r;
    }

    /**
     * Проверка, был ли файлом старый скриншот
     * @param array $c массив, если файл
     * @param string $ni URL изображения для сравнения, чтобы не удалять новый файл
     * @param string $np URL превью для сравнения, чтобы не удалять новый файл
     * @return null
     */
    protected function check_isfile($c, $ni = null, $np = null) {
        if (!is_array($c))
            return;
        $path = ROOT . config::o()->v('screenshots_folder') . '/';
        $i = $p = "";
        list($i, $p) = $c;
        if ($ni != $i && $i)
            @unlink($path . $i);
        if ($np != $p && $p)
            @unlink($path . $p);
    }

    /**
     * Выборка старых скриншотов
     * @param string $old старый сериализованный массив скриншотов
     * @param string $purl начало имени скриншотов
     * @param array $surl массив загружаемых с URL
     * @param array $sfile массив загружаемых файлами
     * @return array массив массива скриншотов и имени последнего
     */
    protected function screenshots_old(&$old, $purl, $surl, $sfile) {
        if (!$old)
            return array(array(), 0);
        $r = array();
        $l = null;
        $old = @unserialize($old);
        foreach ($old as $n => $c) {
            if (!isset($surl[$n]) && !isset($sfile[$n]) && $n > 1) { // удалено тобишь, но не 1-й скриншот и постер
                $this->check_isfile($old[$n]);
                continue;
            }
            if (is_array($old[$n]))
                $l = $old[$n][0];
            $r[$n] = $c; // если надо будет - перезапишется
        }

        $fi = 0;
        if ($l) {
            preg_match('/^' . mpc($purl) . '([0-9]+)\./', $l, $matches);
            $fi = $matches[1] + 1;
        }
        return array($r, $fi);
    }

    /**
     * "Загрузка" скриншотов URL
     * @param array $r массив скриншотов
     * @param array $old старый массив скриншотов
     * @param string $err строка ошибок
     * @param array $data данные функции
     * @return array массив кол-ва загруженных по URL и кол-ва суммы
     */
    protected function screenshots_url(&$r, &$old, &$err, $data) {
        list($surl, $maxscreenshots, $uploader) = $data;
        $u = $s = 0;
        $inum = lang::o()->v('content_torrent_image_n');

        foreach ($surl as $n => $url) {
            $n = (int) $n;
            $u++;
            $s++;
            if ($s > $maxscreenshots)
                break;
            if (!preg_match('/^' . display::url_pattern . '$/siu', $url) || (config::o()->v('check_rimage') && !$uploader->is_image($url))) {
                $err .= ( $err ? "\n" : "") . ($inum . $u . '. ' . lang::o()->v('content_torrent_image_invalid_url'));
                continue;
            }
            $this->check_isfile($old[$n]);
            $r[$n] = $url;
        }
        return array($u, $s);
    }

    /**
     * Загрузка скриншотов файлами
     * @param array $r массив скриншотов
     * @param array $old старый массив скриншотов
     * @param string $err строка ошибок
     * @param array $data данные функции
     * @return array массив кол-ва загруженных по URL и кол-ва суммы
     */
    protected function screenshots_file(&$r, &$old, &$err, $data) {
        list($sfile, $stfile, $purl, $u, $s, $fi, $maxscreenshots, $uploader) = $data;
        $f = $i = 0;
        $inum = lang::o()->v('content_torrent_image_n');
        foreach ($sfile as $n => $tmp) {
            $s++;
            if ($s > $maxscreenshots)
                break;
            if (!$tmp)
                continue;
            $n = (int) $n;
            $fvar = $this->get_files_array($stfile, $n);
            $f++;
            $url = $purl . $fi;
            try {
                if ($n > 0) // тобишь не постер
                    $uploader->set_preview_size($this->scr_width, $this->scr_height);
                else
                    $uploader->set_preview_size($this->poster_width, $this->poster_height);
                $uploader->upload_preview()->upload($fvar, config::o()->v('screenshots_folder'), /* ссылка */ $tmp = 'images', $url);
                if (config::o()->v('watermark_text'))
                    $uploader->watermark(config::o()->v('screenshots_folder') . '/' . $url, config::o()->v('watermark_text'), 'auto', true, null, config::o()->v('watermark_pos'));
                $preview = $uploader->get_preview();
                $this->check_isfile($old[$n], $url, $preview);
                $r[$n] = array($url, $preview);
                $fi++;
            } catch (EngineException $e) {
                $err .= ( $err ? "\n" : "") . ($inum . ($u + $f) . '. ' . $e->getEMessage());
            }
        }
    }

    /**
     * Загрузка скриншотов
     * @param int $id ID торрента
     * @param string $filevars имя массива($_REQUEST или $_FILES) для загрузки
     * @param string $err ошибки, возникшие при добавлении скриншотов
     * @param string $old старый сериализованный массив скриншотов
     * @return string сериализованный массив скриншотов
     */
    protected function screenshots($id, $filevars, &$err = "", $old = null) {
        if (!$this->tstate)
            return;
        $surl = (array) $_REQUEST[$filevars];
        /* @var $uploader uploader */
        $uploader = n("uploader");
        if (!is(config::o()->v('allowed_screenshots'), ALLOWED_IMG_URL))
            $surl = array();
        $stfile = (array) $_FILES[$filevars];
        $sfile = $stfile['tmp_name'];
        if (!is(config::o()->v('allowed_screenshots'), ALLOWED_IMG_PC))
            $sfile = $stfile = array();


        $maxscreenshots = config::o()->v('max_screenshots') + 1; // 1 постер
        if ($maxscreenshots < 2)
            $maxscreenshots = 2;

        $purl = self::image_prefix . $id;
        list($r, $fi) = $this->screenshots_old($old, $purl, $surl, $sfile);

        $data = array($surl, $maxscreenshots, $uploader);
        list($u, $s) = $this->screenshots_url($r, $old, $err, $data);

        $data = array($sfile, $stfile, $purl, $u, $s, $fi, $maxscreenshots, $uploader);
        $this->screenshots_file($r, $old, $err, $data);

        ksort($r);
        return serialize(array_values($r));
    }

    /**
     * Сохранение добавления
     * @param array $update массив обновления
     * @param string $error строка ошибки
     * @param array $data данные для сохранения
     * @return int ID статьи
     * @throws EngineException
     * @throws PReturn
     */
    protected function save_add(&$update, &$error, $data) {
        list($mcats, $tfname, $imname, $getpeers, $bt, $torrent) = $data;
        $time = time(); // Важно для именования файлов! Пишется в posted_time
        $poster_id = users::o()->v('id'); // Важно для именования файлов! Пишется в poster_id
        if ($this->tstate) {
            $filelist = "";
            $size = 0;
            $announce_list = "";
            $sid = default_filename($time, $poster_id);
            $infohash = $bt->torrent_file($sid, $_FILES[$tfname], $filelist, $size, $announce_list);
            $screenshots = $this->screenshots($sid, $imname, $error);
            $torrent['info_hash'] = $infohash;
            $torrent['size'] = $size;
            $torrent['filelist'] = $filelist;
            $torrent['screenshots'] = $screenshots;
            $torrent['announce_list'] = $announce_list;
            plugins::o()->pass_data(array('torrent' => &$torrent));
        }
        $update['posted_time'] = $time;
        $update['poster_id'] = $poster_id;

        plugins::o()->run_hook('content_save_add');

        $id = db::o()->no_error()->insert($update, 'content');
        if ($this->tstate && !db::o()->errno()) {
            $torrent["cid"] = $id;
            db::o()->no_error()->insert($torrent, 'content_torrents');
        }
        if (db::o()->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException('content_already_exists');
        elseif (db::o()->errno())
            db::o()->err();
        if ($this->tstate && config::o()->v('getpeers_after_upload'))
            $getpeers->get_peers($id, $announce_list, $infohash);
        /* @var $etc etc */
        $etc = n("etc");
        $etc->add_res();
        n("mailer")->change_type('categories')->update($mcats);
        return $id;
    }

    /**
     * Сохранение редактирования
     * @param array $update массив обновления
     * @param string $error строка ошибки
     * @param array $data данные для сохранения
     * @return null
     * @throws EngineException
     * @throws PReturn
     */
    protected function save_edit(&$update, &$error, $data) {
        list($row, $edit_reason, $edit_count, $tfname, $imname, $getpeers, $bt, $torrent) = $data;
        $id = $row['id'];
        if ($this->tstate) {
            $sid = default_filename($row["posted_time"], $row["poster_id"]);
            if ($tfname && $_FILES[$tfname]['tmp_name']) {
                $filelist = "";
                $size = 0;
                $announce_list = "";
                $infohash = $bt->torrent_file($sid, $_FILES[$tfname], $filelist, $size, $announce_list);
                $torrent['info_hash'] = $infohash;
                $torrent['size'] = $size;
                $torrent['filelist'] = $filelist;
                $torrent['announce_list'] = $announce_list;
                if (config::o()->v('getpeers_after_upload'))
                    $torrent['announce_stat'] = serialize($getpeers->get_peers($id, $announce_list, $infohash, false));
                else
                    $torrent['announce_stat'] = "";
            }
            if ($imname) {
                $screenshots = $this->screenshots($sid, $imname, $error, $row["screenshots"]);
                $torrent["screenshots"] = $screenshots;
            }
            plugins::o()->pass_data(array('torrent' => &$torrent));
        }
        $update['last_edit'] = time();
        $update['edit_reason'] = $edit_reason;
        $update['editor_id'] = users::o()->v('id');
        $update['edit_count'] = $edit_count + 1;

        plugins::o()->run_hook('content_save_edit');

        db::o()->no_error()->p($id)->update($update, 'content', 'WHERE id=? LIMIT 1');
        if ($this->tstate && !db::o()->errno())
            db::o()->no_error()->p($id)->update($torrent, 'content_torrents', 'WHERE cid=? LIMIT 1');
        if (db::o()->errno() == UNIQUE_VALUE_ERROR)
            throw new EngineException('content_already_exists');
        elseif (db::o()->errno())
            db::o()->err();
        log_add("edited_content", "user", array($row ['title'], $id));
    }

    /**
     * Сохранение статьи
     * @param array $data массив данных
     * @param int $id ID статьи
     * @param bool $short быстрое редактирование?
     * @return int ID созданной(отредактированной) статьи
     * @throws EngineException 
     */
    public function save($data, $id = null, $short = false) {

        $data_params = array("title",
            "cat" => "cats",
            "content",
            "imname",
            "tfname",
            "tags",
            "on_top",
            "sticky",
            "edit_reason",
            "price");
        extract(rex($data, $data_params));

        check_formkey();
        lang::o()->get('content');
        $id = (int) $id;
        $price = (float) $price;
        if ($id) {
            $lj = $cols = "";
            if ($this->tstate) {
                $cols = ", t.*";
                $lj = " LEFT JOIN content_torrents AS t ON t.cid=c.id";
            }
            $row = db::o()->p($id)->query('SELECT c.* ' . $cols . ' FROM content AS c ' . $lj . ' WHERE c.id=? LIMIT 1');
            $row = db::o()->fetch_assoc($row);
            if ($row) {
                if ($this->tstate && $row["banned"] == 2)
                    throw new EngineException("content_torrent_cant_be_edited");
                if (users::o()->v('id') == $row ['poster_id'])
                    users::o()->check_perms('edit_content');
                else
                    users::o()->check_perms('edit_content', '2');
                $edit_count = $row['edit_count'];
            }
            else
                throw new EngineException('content_not_exists');
        }
        if (!$content)
            throw new EngineException('content_no_content');
        if (!$title)
            throw new EngineException('content_no_title');

        if (!is_null($cat) || !$id) {
            $mcats = $cat;
            $cats = $this->cats;
            $cat = $cats->save_selected($mcats);
            if (!$cat)
                throw new EngineException('content_no_selected_cat');
        }
        try {
            $update = array(
                'title' => $title);
            $torrent = array();
            if (!is_null($content) || !$id)
                $update['content'] = $content;
            if (!is_null($cat) || !$id)
                $update['category_id'] = $cat;
            if (!is_null($tags) || !$id)
                $update['tags'] = preg_replace('/\s*,\s*/su', ',', $tags);
            if (!is_null($sticky) && users::o()->perm('msticky_content'))
                $update['sticky'] = $sticky ? "1" : "0";
            elseif (!$id)
                $update['sticky'] = "0";
            $error = "";
            plugins::o()->pass_data(array('update' => &$update,
                'id' => $id,
                'error' => &$error), true)->run_hook('content_save_begin');
            $bt = $getpeers = null;
            if ($this->tstate) {
                if (!is_null($price) && $price <= config::o()->v('max_torrent_price') && users::o()->perm('ct_price'))
                    $torrent['price'] = $price;
                $torrent['last_active'] = time();
                /* @var $bt bittorrent */
                $bt = n("bittorrent");
                /* @var $getpeers geetpeers */
                $getpeers = n("getpeers");
            } elseif (!is_null($on_top) && users::o()->perm('edit_content', 2))
                $update['on_top'] = $on_top ? '1' : '0';
            elseif (!$id)
                $update['on_top'] = '0';

            if (!$id) {
                $sdata = array($mcats, $tfname, $imname, $getpeers, $bt, $torrent);
                $id = $this->save_add($update, $error, $sdata);
            } else {
                $sdata = array($row, $edit_reason, $edit_count, $tfname, $imname, $getpeers, $bt, $torrent);
                $this->save_edit($update, $error, $sdata);
            }

            plugins::o()->pass_data(array('id' => $id))->run_hook('content_save_end');

            n("attachments")->change_type('content')->define_toid($data, $id);

            try {
                n("polls")->change_type('content')->save($data, $id);
            } catch (EngineException $e) {
                if ($e->getCode())
                    throw $e;
            }
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($error)
            throw new EngineException('content_torrent_uploaded_but', array(
        furl::o()->construct('content', array(
            'id' => $id,
            'title' => $title)),
        $error));
        return $id;
    }

}

class content extends content_add {

    /**
     * Список статусов торрента(имя=>banned)
     * @var array $status
     */
    public static $status = array(
        'checked' => 0,
        'checking' => 2,
        'unsure' => 0,
        'eated' => 1,
        'temporary' => 0,
        'nfabit' => 0,
        'nfalot' => 1,
        'closed' => 2,
        'duplicate' => 1,
        'copyright' => 2);

    /**
     * Инициализация контента
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        lang::o()->get('content');
        users::o()->check_perms('content', 1, 2);
        switch ($act) {
            case "download":
                if (!$this->tstate)
                    die();
                $id = longval($_GET ['id']);
                /* @var $bt bittorrent */
                $bt = n("bittorrent");
                $bt->download_torrent($id);
                break;
            case "rss":
            case "atom":
                $this->rss($_GET ['cat'], $_GET ['act'] == "atom");
                die();
                break;
            case "add" :
            case "edit" :
                $id = longval($_GET ['id']);
                if (!$id) {
                    $this->title = lang::o()->v('content_adding');
                    users::o()->check_perms('content', 2);
                }
                else
                    $this->title = lang::o()->v('content_editing');
                if (!$_POST ['confirm']) {
                    $cat = $_GET ['cat'];
                    $this->add($cat, $id);
                } else {
                    $_POST['tfname'] = 'torrent';
                    $_POST['imname'] = 'screenshots';
                    $id = $this->save($_POST, $id);
                    furl::o()->location(furl::o()->construct('content', array(
                                'id' => $id,
                                'title' => $_POST['title'])));
                }
                break;
            case "new" :
            case "unreaded" :
                $this->title = lang::o()->v('content_unreaded');
            case "unchecked" :
                if (!$this->title)
                    $this->title = lang::o()->v('content_unchecked');
                $this->show_table($act, $_GET['cat']);
                break;
            default :
                $id = longval($_GET ['id']);
                if (!$id) {
                    $this->title = lang::o()->v('content_page');
                    $this->show();
                } else {
                    $this->title = lang::o()->v('content_item');
                    $this->show($id);
                }
                break;
        }
    }

    /**
     * Отметка прочитанным
     * @param int $id ID контента
     * @return null
     */
    public function make_readed($id = "") {
        if (!users::o()->v())
            return;
        $last_clean = stats::o()->read('last_clean_rc');
        $id = longval($id);
        if ($id) {
            db::o()->no_error()->insert(array(
                "content_id" => $id,
                "user_id" => users::o()->v('id')), 'content_readed');
        } else {
            $rows = db::o()->p(users::o()->v('id'))->query('SELECT c.id, c.posted_time FROM content AS c
                LEFT JOIN content_readed AS rt ON rt.content_id=c.id AND rt.user_id=?
                WHERE rt.content_id IS NULL');
            while ($row = db::o()->fetch_assoc($rows)) {
                if ($row["posted_time"] > $last_clean)
                    db::o()->insert(array(
                        "content_id" => $row ['id'],
                        "user_id" => users::o()->v('id')), 'content_readed', true);
            }
            db::o()->save_last_table();
        }
    }

    /**
     * RSS контента
     * @param string $cat категория
     * @param bool $atom Atom?
     * @return null
     */
    protected function rss($cat = null, $atom = false) {
        users::o()->check_perms('content', 1, 2);
        ob_clean();
        @header("Content-Type: application/xml");
        $cat_rows = array();
        $cats = $this->cats;
        if ($cat)
            $where = ($cats->condition($cat, $cat_rows));
        else
            $where = "on_top='1'";
        $lj = $cols = "";
        if ($this->tstate) {
            $lj = ' LEFT JOIN ' . db::table('content_torrents') . ' AS t ON t.cid=c.id';
            $cols = ", t.screenshots";
        }
        $row = db::o()->no_parse()->query('SELECT c.title, c.posted_time, u.username,
            c.content, c.id ' . $cols . ' FROM ' . db::table('content') . ' AS c ' . $lj . '
            LEFT JOIN ' . db::table('users') . ' AS u ON u.id=c.poster_id
            ' . ($where ? " WHERE " . $where : "") .
                " ORDER BY CAST(sticky AS BINARY) DESC, posted_time DESC" .
                (config::o()->v('max_rss_items') ? " LIMIT " . config::o()->v('max_rss_items') : ""));
        tpl::o()->assign('rows', db::o()->fetch2array($row));
        tpl::o()->assign('cat_rows', $cat_rows);

        tpl::o()->register_modifier("show_image", array(
            $this,
            'show_image'));

        if (!$atom)
            tpl::o()->display("content/rss.xtpl");
        else
            tpl::o()->display("content/atom.xtpl");
    }

    /**
     * Непрочтённый контент
     * @param string $act действие
     * @param string $category категория
     * @return null
     * @throws EngineException
     */
    public function show_table($act, $category = null) {
        $where = $lj = $cols = $orderby = '';
        switch ($act) {
            case "unreaded":
            case "new":
                users::o()->check_perms();
                $last_clean = stats::o()->read('last_clean_rc');
                $lj = ' LEFT JOIN ' . db::table('content_readed') . ' AS rt ON rt.content_id=c.id AND rt.user_id=' . users::o()->v('id');
                $where = '(rt.content_id IS NULL)';
                if ($last_clean)
                    $where .= ' AND c.posted_time>' . $last_clean;
                $orderby = 'CAST(sticky AS BINARY) DESC, posted_time DESC';
                break;
            case "unchecked":
                users::o()->check_perms('edit_content', 2);
                $where = ($this->tstate ? "t.status='0'" : "c.on_top='0'");
                $orderby = 'CAST(sticky AS BINARY) DESC, posted_time';
                break;
        }
        if ($this->tstate) {
            $cols .= ", t.*";
            $lj .= ' LEFT JOIN ' . db::table('content_torrents') . ' AS t ON t.cid=c.id';
        }
        if ($category) {
            $cid = $this->cats->get($category);
            if ($cid['id'])
                $where .= ($where ? ' AND ' : "") . $this->cats->cat_where($cid['id']);
        }
        try {
            plugins::o()->pass_data(array('where' => &$where,
                'lj' => &$lj,
                'cols' => &$cols,
                'orderby' => &$orderby), true)->run_hook('content_show_table');
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($where)
            $where = ' WHERE ' . $where;

        $count = db::o()->no_parse()->query('SELECT COUNT(*) FROM ' . db::table('content') . ' AS c ' . $lj . $where);
        $count = db::o()->fetch_row($count);
        $perpage = config::o()->v('table_content_perpage');
        list ( $pages, $limit ) = display::o()->pages($count[0], $perpage, 'change_tpage', 'page', '', true);

        $rows = db::o()->no_parse()->query('SELECT c.*' . $cols . ', u.username, u.group FROM content AS c ' . $lj . '
            LEFT JOIN ' . db::table('users') . ' AS u ON c.poster_id=u.id
            ' . $where . '
            ORDER BY ' . $orderby . '
            LIMIT ' . $limit);

        tpl::o()->assign('rows', db::o()->fetch2array($rows));
        tpl::o()->assign('pages', $pages);
        tpl::o()->assign('act', $act);
        tpl::o()->display('content/table.tpl');
    }

    /**
     * Получение списков сидеров, личеров, скачавших
     * @param array $rows массив данных
     * @return null
     */
    protected function get_peers_list(&$rows) {
        if (!$this->tstate)
            return;
        $id = (int) $rows["id"];
        if (!config::o()->v('cache_details') || !($a = cache::o()->read("details/l-id" . $id))) {
            $r = db::o()->p($id)->query('SELECT u.username, u.group, p.seeder FROM content_peers AS p
                LEFT JOIN users AS u ON u.id=p.uid
                WHERE p.tid = ?');
            $seeders = "";
            $leechers = "";
            while ($row = db::o()->fetch_assoc($r)) {
                $user = smarty_group_color_link($row["username"], $row["group"]);
                if ($row['seeder'])
                    $seeders .= ( $seeders ? ", " : "") . $user;
                else
                    $leechers .= ( $leechers ? ", " : "") . $user;
            }
            $downloaders = "";
            $r = db::o()->p($id)->query('SELECT u.username, u.group FROM content_downloaded AS d
                LEFT JOIN users AS u ON u.id=d.uid
                WHERE d.tid = ? AND d.finished="1"');
            while ($row = db::o()->fetch_assoc($r)) {
                $user = smarty_group_color_link($row["username"], $row["group"]);
                $downloaders .= ( $seeders ? ", " : "") . $user;
            }
            $a["seeders_t"] = $seeders;
            $a["leechers_t"] = $leechers;
            $a["downloaders_t"] = $downloaders;
            if (config::o()->v('cache_details'))
                cache::o()->write($a);
        }
        $rows = array_merge($rows, $a);
    }

    /**
     * Обработка тегов
     * @param array $rows массив данных
     * @return null
     */
    protected function prepare_tags(&$rows) {
        $rows['tags'] = trim($rows['tags']);
        if ($rows['tags']) {
            $tags = explode(',', $rows['tags']);
            $r = "";
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!$tag)
                    continue;
                $r .= ( $r ? ", " : "") . "<a href='" . furl::o()->construct('search', array('auto' => true,
                            'tag' => $tag)) . "'>" . $tag . "</a>";
            }
            $rows ['tags'] = $r;
        }
    }

    /**
     * Префильтр для отображения контента
     * @param bool $full детальный ли просмотр контента?
     * @param array $rows массив данных
     * @return null
     */
    public function prefilter($full, &$rows) {
        if (!$full) {
            $rows ["content"] = display::o()->cut_text($rows ["content"], config::o()->v('max_sc_symb'));
        }
        else
            $this->get_peers_list($rows);
        $cat_arr = $this->cats->cid2arr($rows ['category_id'], 0);
        $this->prepare_tags($rows);
        $rows ['filelist'] = (array) unserialize($rows ['filelist']);
        $rows ['cats_arr'] = $cat_arr [1];
        $rows ['cat_parents'] = $cat_arr [0];
    }

    /**
     * Отображение изображения/изображений для торрента
     * @param string $images сериалиованный массив изображений
     * @param bool $poster постер?
     * @param bool $rss для RSS?
     * @param string $align расположение постера
     * @return string HTML код изображения(ий)
     */
    public function show_image($images, $poster = true, $rss = false, $align = 'right') {
        if (!$this->tstate)
            return;
        $baseurl = globals::g('baseurl');
        $images = unserialize($images);
        if (!is_array($images) || !reset($images))
            return;
        lang::o()->get('content');
        if ($poster)
            $images = array($images[0]);
        else
            unset($images[key($images)]);
        $r = "";
        $mw = $poster ? $this->poster_width : $this->scr_width;
        $mh = $poster ? $this->poster_height : $this->scr_height;
        if (!$align)
            $align = 'right';
        foreach ($images as $k => $image) {
            if (is_array($image)) {
                list($image, $preview) = $image;
                if (!$preview)
                    $preview = $image;
                $pre = $baseurl . config::o()->v('screenshots_folder') . '/';
                $image = $pre . $image;
                $preview = $pre . $preview;
            }
            else
                $preview = $image;
            $title = !$k ? lang::o()->v('content_torrent_poster') : lang::o()->v('content_torrent_screenshot_n') . $k;
            if ($rss)
                $r .= ( $r ? "&nbsp; &nbsp;" : "") . "
                    <a href='" . $image . "'><img alt='poster' src='" . $preview . "' /></a>";
            else
                $r .= ( $r ? "&nbsp; &nbsp;" : "") . "<a href='" . $image . "' title='" . $title . "' 
                    rel='sexylightbox" . (!$poster ? "[scrs]" : "") . "'>
                <img src='" . $preview . "' " . ($poster ? "align='" . $align . "'" : "") . "
                    alt='Image' class='cornerImg' style='max-width:" . $mw . "px;max-height:" . $mh . "px;'>
                </a>";
        }
        tpl::o()->assign('slbox_mbinited', true); // Инициализовать SexyLightbox
        return $r;
    }

    /**
     * Модификатор для заголовка контента, добавляющий к нему иконку
     * @param string $title заголовок контента
     * @param string $image имя иконки(типа .png, хранящяяся в engine_images)
     * @param string $text подсказка к иконке
     * @return string заголовок с картинкой
     */
    public function prepend_title_icon($title, $image, $text = "") {
        $theme_path = globals::g('theme_path');
        $title = '<img src="' . $theme_path . '/engine_images/' . $image . '.png" alt="' . $text . '"
        title="' . $text . '" align="left">' . $title;
        return $title;
    }

    /**
     * Обработка параметров перед отображением
     * @param array $data категория/дата
     * @param int $id ID контента
     * @param bool $full детальный?
     * @param bool $fe от редактирования?
     * @return array массив условия, имени категории, сортировки и строки JOIN
     * @throws PReturn
     */
    protected function show_prepare($data, $id, &$full, $fe) {
        $where = array();
        $orderby = $lj = "";
        plugins::o()->pass_data(array('data' => &$data,
            'id' => $id,
            'fe' => $fe,
            'where' => &$where,
            'orderby' => &$orderby,
            'lj' => &$lj), true)->run_hook('content_show_begin');

        $cats = $this->cats;
        if (!$id) {
            $cat = mb_strtolower(display::o()->strip_subpath($data ['cat']));
            $cat_rows = array();
            if (!$cat)
                $where [] = "c.on_top='1'";
            elseif ($cwhere = $cats->condition($cat, $cat_rows))
                $where [] = $cwhere;
            if ($cat_rows)
                $this->title = $cat_rows [0];
            $year = (int) $data ["year"];
            $month = (int) $data ["month"];
            $day = (int) $data ["day"];
            if ($day)
                $where [] = 'c.posted_time BETWEEN ' . mktime(null, null, null, $month, $day, $year) . ' AND ' . (mktime(null, null, null, $month, $day + 1, $year) - 1);
            elseif ($month)
                $where [] = 'c.posted_time BETWEEN ' . mktime(null, null, null, $month, null, $year) . ' AND ' . (mktime(null, null, null, $month + 1, null, $year) - 1);
            elseif ($year)
                $where [] = 'c.posted_time BETWEEN ' . mktime(null, null, null, null, null, $year) . ' AND ' . (mktime(null, null, null, null, null, $year + 1) - 1);
            $full = false;
            if ($cat_rows)
                $content_cats = $cats->get($cat_rows [3], 'c');
            else
                $content_cats = $cats->get(null, 't');
            tpl::o()->assign("content_catparents", $cats->get($cat_rows [3], 'ps'));
            tpl::o()->assign("content_cats", $content_cats);
            tpl::o()->assign("add_url", http_build_query($data));
        } else {
            $where [] = 'c.id=' . $id;
            tpl::o()->assign("id", "id=" . $id);
            tpl::o()->assign("from_edit", $fe);
            if ($full !== false)
                tpl::o()->assign("full_content", true);
            $full = true;
        }

        plugins::o()->pass_data(array('full' => &$full,
            'cat' => &$cat,
            'cat_rows' => &$cat_rows))->run_hook('content_show_condition');

        tpl::o()->assign('cat_rows', $cat_rows);
        return array($where, $cat, $orderby, $lj);
    }

    /**
     * Создание запроса для отображения статей
     * @param bool $full полная статья?
     * @param string $lj строка JOIN
     * @param string $where условие
     * @param string $orderby сортировка
     * @param string $limit ограничение
     * @return string строка запроса
     */
    protected function show_query($full = false, $lj = "", $where = "", $orderby = "", $limit = "") {
        /* Монстрообразный запрос. Неплохо бы его оптимизировать,
         * но нет.
         * Да и если не смотреть на его размер, он должен(srsly?) выполняться достаточно быстро, 
         * учитывая то,
         * что в каждом случае LEFT JOIN поиск идёт либо по первичному ключу, либо по уникальному ключу.
         */
        $cols = array('c.*',
            "u.username",
            "u.group",
            "u2.username AS eu",
            "u2.group AS eg");
        $leftjoin = array('LEFT JOIN ' . db::table('users') . ' AS u ON c.poster_id=u.id',
            'LEFT JOIN ' . db::table('users') . ' AS u2 ON c.editor_id=u2.id',
            $lj);
        if (!$orderby)
            $orderby = 'CAST(c.sticky AS BINARY) DESC, c.posted_time DESC';
        if ($this->tstate) {
            $leftjoin[] = 'LEFT JOIN ' . db::table('content_torrents') . ' AS t ON t.cid=c.id';
            $cols[] = 't.*';
            if ($full) {
                $cols [] = 'st.username AS su';
                $cols [] = 'st.group AS sg';
                $leftjoin[] = 'LEFT JOIN ' . db::table('users') . ' AS st ON st.id=t.statusby';
            }
        }
        if (users::o()->v()) {
            $cols [] = 'b.id AS bookmark_id';
            $cols [] = 'rt.content_id AS readed';
            $leftjoin[] = 'LEFT JOIN ' . db::table('bookmarks') . ' AS b ON c.id=b.toid AND b.type="content" AND b.user_id=' . users::o()->v('id');
            $leftjoin[] = 'LEFT JOIN ' . db::table('content_readed') . ' AS rt ON rt.content_id=c.id AND rt.user_id=' . users::o()->v('id');
        }


        plugins::o()->pass_data(array('cols' => &$cols,
            'leftjoin' => &$leftjoin,
            'orderby' => &$orderby))->run_hook('content_show_query');

        $query = 'SELECT ';
        $query .= implode(', ', $cols);
        $query .= ' FROM ' . db::table('content') . ' AS c ';
        $query .= implode(' ', $leftjoin);
        if ($where)
            $query .= ' WHERE ' . $where;
        if (!$full)
            $query .= ' ORDER BY ' . $orderby;
        if ($limit)
            $query .= ' LIMIT ' . $limit;
        return $query;
    }

    /**
     * Подготовка мета тегов
     * @param array $row данные контента
     * @return null
     */
    protected function show_meta($row) {
        $this->title .= ' "' . $row ['title'] . '"';
        tpl::o()->assign("overall_keywords", $row ['tags']);
        if ($row ['content']) {
            $what = array("\n", "\r", "  ");
            $with = array(" ", " ", " ");
            $meta = str_replace($what, $with, $row ['title'] . " " . $row ['content']);
            $meta = display::o()->cut_text($meta, config::o()->v('max_meta_descr_symb'));
            tpl::o()->assign("overall_descr", bbcodes::o()->remove_tags($meta));
        }
    }

    /**
     * Отображение контента
     * @param int $id ID контента
     * @param bool $full детальный?
     * @param bool $fe от редактирования?
     * @param array $data категория/дата
     * @return null
     * @throws EngineException 
     */
    public function show($id = null, $full = null, $fe = false, $data = null) {
        lang::o()->get('content');
        if (!$data)
            $data = $_REQUEST;
        $id = (int) $id;
        try {
            list($where, $cat, $orderby, $lj) = $this->show_prepare($data, $id, $full, $fe);

            $where = implode(" AND ", $where);
            $page = 'page';
            if (!$full && !$fe) {
                $slj = $lj;
                //if ($this->tstate)
                //    $lj = " LEFT JOIN content_torrents AS t ON t.cid=c.id";
                $crow = db::o()->no_parse()->query('SELECT COUNT(*) FROM ' . db::table('content') . ' AS c ' .
                        $slj . ($where ? ' WHERE ' . $where : ""));
                $count = db::o()->fetch_row($crow);
                $count = $count[0];
                $perpage = config::o()->v('content_perpage');
                $maxpage = intval($count / $perpage) + ($count % $perpage != 0 ? 1 : 0);
                list ( $pages, $limit ) = display::o()->pages($count, $perpage, 'change_tpage', $page, '', true);
                tpl::o()->assign("pages", $pages);
                tpl::o()->assign('page', $_GET[$page]);
                tpl::o()->assign('maxpage', $maxpage);
            } elseif ($full)
                $limit = 1;

            $query = $this->show_query($full, $lj, $where, $orderby, $limit);
            $rows = db::o()->fetch2array(db::o()->no_parse()->query($query));

            if ($full && !$rows)
                throw new EngineException("content_not_exists");

            $last_clean = stats::o()->read('last_clean_rc');
            if (!$fe && $full && !$rows [0] ['readed'] && $rows [0]['posted_time'] > $last_clean)
                $this->make_readed($id);
            if (!$fe && $full)
                $this->show_meta($rows[0]);

            plugins::o()->pass_data(array('rows' => &$rows), true)->run_hook('content_show_end');


            tpl::o()->register_modifier("show_image", array(
                $this,
                'show_image'));
            tpl::o()->register_modifier("prepend_title_icon", array(
                $this,
                'prepend_title_icon'));
            tpl::o()->register_modifier("content_prefilter", array(
                $this,
                'prefilter'));

            tpl::o()->assign('content_row', $rows);
            tpl::o()->assign('last_clean_rc', $last_clean);
            tpl::o()->assign('statuses', self::$status);
            n('rating'); // для display_rating
            //if ($full) {
            n("comments"); // для display_comments
            n('polls'); // для display_polls
            n('attachments'); // для display_attachments
            //}
            if (tpl::o()->template_exists('content/cats/' . $cat . ".tpl") && $cat)
                tpl::o()->display('content/cats/' . $cat . ".tpl");
            else
                tpl::o()->display('content/index.tpl');
        } catch (PReturn $e) {
            return $e->r();
        }
    }

}

class torrents_simpleview {

    /**
     * Включены ли торренты?
     * @var bool $tstate
     */
    protected $tstate = true;

    /**
     * Конструктор
     * @return null
     */
    public function __construct() {
        $this->tstate = config::o()->v('torrents_on');
    }

    /**
     * Имя блока для отображения
     * @var string $block_name
     */
    protected $block_name = "torrents";

    /**
     * Обработка параметров блока
     * @param string $name имя категории
     * @return array массив условия, ограничения записей и макс. кол-ва символов
     * в заголовке
     * @throws EngineException
     */
    protected function show_prepare($name) {
        $settings = n("blocks")->get_settings($this->block_name);
        $catids = $settings['cats'][$name];
        if (!$settings || !$catids)
            throw new EngineException;
        $limit = (int) $settings['limit'];
        if ($limit > 20 || $limit <= 0)
            $limit = 20;
        if ($limit % 2 == 1)
            $limit--;
        $max_title_symb = (int) $settings['max_title_symb'];
        if (!$max_title_symb)
            $max_title_symb = 100;
        $catids = display::o()->idstring2array($catids);
        $where = n("categories")->cat_where($catids, true);
        $where .= ($where ? ' AND' : '') . ' on_top="1"';
        return array($where, $limit, $max_title_symb);
    }

    /**
     * Обработка записи торрента
     * @param content $content объект контента
     * @param array $row данные торрента
     * @param int $max_title_symb макс. кол-во символов в заголовке
     * @return null
     */
    protected function show_row($content, &$row, $max_title_symb) {
        $row['screenshots'] = $content->show_image($row['screenshots'], true, false, "center");
        $title = $row['title'];
        if (preg_match('/^(.*)(?:\/(.*?))?(?:\(([0-9]+)\))?$/siu', $title, $matches)) {
            $row['name'] = display::o()->cut_text($matches[1], $max_title_symb);
            $row['orig_name'] = display::o()->cut_text($matches[2], $max_title_symb);
            $row['year'] = $matches[3];
        }
        else
            $row['name'] = display::o()->cut_text($row['title'], $max_title_symb);
    }

    /**
     * Простое отображение торрентов для блока
     * @param string $name имя категории
     * @return null
     * @throws EngineException 
     */
    public function show($name) {
        if (!users::o()->perm('content'))
            return;
        if (!$this->tstate)
            throw new EngineException;
        $crc = crc32($name);
        $cfile = 'tsimple/cat-' . $crc;
        lang::o()->get('blocks/torrents');
        if (($a = cache::o()->read($cfile)) === false) {

            list($where, $limit, $max_title_symb) = $this->show_prepare($name);

            $r = db::o()->no_parse()->query('SELECT t.*,c.*, u.username, u.group
            FROM ' . db::table('content') . ' AS c 
            LEFT JOIN ' . db::table('content_torrents') . ' AS t ON t.cid=c.id
            LEFT JOIN ' . db::table('users') . ' AS u ON u.id=c.poster_id
            WHERE ' . $where . '
            ORDER BY posted_time DESC
            LIMIT ' . $limit);
            $a = array();
            /* @var $content content */
            $content = plugins::o()->get_module('content');
            while ($row = db::o()->fetch_assoc($r)) {
                $this->show_row($content, $row, $max_title_symb);
                $a[] = $row;
            }
            cache::o()->write($a);
        }
        tpl::o()->assign('res', $a);
        tpl::o()->display('blocks/contents/torrents.tpl');
    }

}

class content_ajax extends torrents_simpleview {

    /**
     * Инициализация AJAX части контента
     * @return null
     */
    public function init() {
        $act = $_GET ['act'];
        $id = (int) $_POST["id"];
        /* @var $n content */
        $n = plugins::o()->get_module('content');
        switch ($act) {
            case "clear_comm":
                n("comments")->change_type('content')->clear($id);
                ok();
                break;
            case "status":
                $status = $_POST["status"];
                $this->set_status($id, $status);
                break;
            case "getpeers":
                $this->getpeers($id);
                break;
            case "save":
                $c = (bool) $_GET["c"];
                $id = (int) $_GET["id"];
                $n->save($_POST, $id, true);
                ok(true);
                $n->show($id, (bool) $_GET["full"], true);
                break;
            case "quick_edit":
                $this->quick_edit($id);
                break;
            case "delete" :
                $this->delete($id);
                ok();
                break;
            case "check" :
                $this->check($id);
                ok();
                break;
            case "read" :
                $n->make_readed($id);
                ok();
                break;
            case "show":
                $this->show($_GET['cats']);
                break;
            default :
                break;
        }
    }

    /**
     * Отметка проверенным
     * @param int $id ID контента
     * @return null
     */
    public function check($id = "") {
        if (!users::o()->check_perms('edit_content', '2'))
            return;
        $id = longval($id);
        if ($this->tstate) {
            db::o()->p($id)->update(array('status' => 'checked',
                'statusby' => users::o()->v('id')), "content_torrents", 'WHERE status="0"' . ($id ? ' AND cid=?' : ''));
        }
        db::o()->p($id)->update(array('on_top' => '1'), "content", ($id ? 'WHERE id=?' : ''));
    }

    /**
     * Установка статуса торрента
     * @param int $id ID торрента
     * @param int $status статус торрента
     * @return null
     * @throws EngineException
     */
    protected function set_status($id, $status) {
        if (!$this->tstate)
            throw new EngineException;
        if (!isset(content::$status[$status]))
            throw new EngineException;
        check_formkey();
        $id = (int) $id;
        users::o()->check_perms('edit_content', '2');
        $torrent = array('status' => $status,
            'statusby' => users::o()->v('id'),
            'banned' => content::$status[$status]);
        $update = array('on_top' => (int) !content::$status[$status]);
        db::o()->p($id)->update($torrent, 'content_torrents', 'WHERE cid=? LIMIT 1');
        db::o()->p($id)->update($update, 'content', 'WHERE id=? LIMIT 1');
        $torrent = array_merge($torrent, $update);
        $torrent['id'] = $id;
        $torrent['su'] = users::o()->v('username');
        $torrent['sg'] = users::o()->v('group');
        lang::o()->get('content');
        tpl::o()->assign('content', $torrent);
        tpl::o()->assign('statuses', content::$status);
        tpl::o()->display('content/status.tpl');
    }

    /**
     * Обрезка URL аннонсера
     * @param string $url URL аннонсера
     * @return string обрезанный URL
     */
    public function cut_tracker($url) {
        preg_match('/^' . display::url_pattern . '$/siu', $url, $m);
        return $m[2] . "://" . $m[3] . "/";
    }

    /**
     * Получение списка "левых пиров" для торрента
     * @param int $id ID торрента
     * @return null
     */
    protected function getpeers($id) {
        if (!$this->tstate)
            throw new EngineException;
        lang::o()->get('content');
        $id = (int) $id;
        $r = db::o()->p($id)->query('SELECT announce_stat, announce_list, info_hash FROM content_torrents 
            WHERE cid=? LIMIT 1');
        list($announce_stat, $announces, $infohash) = db::o()->fetch_row($r);
        if (!$infohash)
            throw new EngineException('content_are_not_exists');
        $hour = 3600;
        $announce_stat = unserialize($announce_stat);
        if (config::o()->v('get_peers_interval') &&
                $announce_stat['last_update'] <= time() - config::o()->v('get_peers_interval') * $hour)
            $announce_stat = n("getpeers")->get_peers($id, $announces, $infohash);
        unset($announce_stat['last_update']);
        tpl::o()->register_modifier('cut_tracker', array($this, "cut_tracker"));
        tpl::o()->assign('trackers', $announce_stat);
        tpl::o()->display('content/left_peers.tpl');
    }

    /**
     * Удаление скриншотов, постера и торрента
     * @param int $posted_time время создания
     * @param int $poster_id ID залившего
     * @param string $screenshots массив скриншотов
     * @return null
     */
    protected function delete_files($posted_time, $poster_id, $screenshots) {
        if (!$this->tstate)
            return;
        $f = default_filename($posted_time, $poster_id);
        @unlink(ROOT . config::o()->v('torrents_folder') . '/' . bittorrent::torrent_prefix . $f . ".torrent");

        $screenshots = unserialize($screenshots);
        if (!$screenshots)
            return;
        foreach ($screenshots as $screenshot)
            if (is_array($screenshot)) {
                if ($screenshot[0])
                    @unlink(ROOT . config::o()->v('screenshots_folder') . '/' . $screenshot[0]);
                if ($screenshot[1])
                    @unlink(ROOT . config::o()->v('screenshots_folder') . '/' . $screenshot[1]);
            }
    }

    /**
     * Удаление контента
     * @param int $id ID контента
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        check_formkey();
        $id = (int) $id;
        $lj = $cols = "";
        if ($this->tstate) {
            $cols = ", t.screenshots";
            $lj = ' LEFT JOIN content_torrents AS t ON t.cid=c.id';
        }
        $row = db::o()->p($id)->query('SELECT c.poster_id, c.title, c.posted_time, 
            p.id AS poll_id ' . $cols . ' FROM content AS c ' . $lj . '
                LEFT JOIN polls AS p ON p.type="content" AND p.toid=c.id
                WHERE c.id=? LIMIT 1');
        list ($poster_id, $title, $posted_time, $pid, $screenshots) = db::o()->fetch_row($row);
        if ($row) {
            if (users::o()->v('id') == $poster_id)
                users::o()->check_perms('del_content');
            else
                users::o()->check_perms('del_content', '2');
        }
        else
            throw new EngineException('content_not_exists');
        db::o()->p($id)->delete('content', 'WHERE id=? LIMIT 1');
        if ($this->tstate) {
            db::o()->p($id)->delete('content_torrents', 'WHERE cid=? LIMIT 1');
            db::o()->p($id)->delete('content_downloaded', 'WHERE tid=? LIMIT 1');
            db::o()->p($id)->delete('content_peers', 'WHERE tid=? LIMIT 1');
            cache::o()->remove("details/l-id" . $id);
            $this->delete_files($posted_time, $poster_id, $screenshots);
        }

        try {
            plugins::o()->pass_data(array('id' => $id), true)->run_hook('content_delete');
        } catch (PReturn $e) {
            return $e->r();
        }

        db::o()->p($id)->delete('content_readed', 'WHERE content_id=? LIMIT 1');
        /* @var $etc etc */
        $etc = n("etc");
        $etc->add_res('content', -1, '', $poster_id);
        log_add("deleted_content", "user", array($title));
        users::o()->admin_mode();
        n("comments")->change_type('content')->clear($id);
        n("rating")->change_type('content')->clear($id);
        n("mailer")->change_type('content')->remove($id);
        if ($pid)
            n("polls")->delete($pid);
        users::o()->admin_mode(false);
    }

    /**
     * Форма быстрого редактирования контента
     * @param int $id ID контента
     * @return null
     * @throws EngineException
     */
    protected function quick_edit($id) {
        lang::o()->get("content");
        $cols = $lj = $where = "";
        if ($this->tstate) {
            $cols = ', t.*';
            $lj = ' LEFT JOIN content_torrents AS t ON t.cid=c.id';
            $where = " AND (t.banned <> '2' OR t.banned IS NULL)";
        }
        $row = db::o()->p($id)->query('SELECT c.* ' . $cols . ' FROM content AS c ' . $lj . '
            WHERE  c.id=?' . $where . ' LIMIT 1');
        $row = db::o()->fetch_assoc($row);
        if (!$row)
            throw new EngineException;
        if (users::o()->v('id') == $row['poster_id'])
            users::o()->check_perms('edit_content');
        else
            users::o()->check_perms('edit_content', '2');
        tpl::o()->assign('row', $row);
        tpl::o()->display('content/edit.tpl');
    }

}

?>