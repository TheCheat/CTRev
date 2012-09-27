<?php

/**
 * Project:             CTRev
 * File:                torrents.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Модуль торрентов
 * @version           	1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class torrents {

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Список статусов торрента(имя=>banned)
     * @var array
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
     * Макс. ширина для превью скриншота
     * @var int
     */
    protected $scr_width = 150;

    /**
     * Макс. высота для превью скриншота
     * @var int
     */
    protected $scr_height = 150;

    /**
     * Префикс в имени скриншота, хранимого на сервере
     * @const string image_prefix
     */

    const image_prefix = "i";

    /**
     * Инициализация торрентов
     * @global users $users
     * @global lang $lang
     * @global furl $furl
     * @global bittorrent $bt
     * @return null
     */
    public function init() {
        global $users, $lang, $furl, $bt;
        $act = $_GET ['act'];
        $lang->get('torrents');
        $users->check_perms('torrents', 1, 2);
        switch ($act) {
            case "download":
                $id = longval($_GET ['id']);
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
                    $this->title = $lang->v('torrents_adding');
                    $users->check_perms('torrents', 2);
                } else
                    $this->title = $lang->v('torrents_editing');
                if (!$_POST ['confirm']) {
                    $cat = $_GET ['cat'];
                    $this->add($cat, $id);
                } else {
                    $_POST['tfname'] = 'torrent';
                    $_POST['imname'] = 'screenshots';
                    $id = $this->save($_POST, $id);
                    $furl->location($furl->construct('torrents', array(
                                'id' => $id,
                                'title' => $_POST['title'])));
                }
                break;
            case "new" :
            case "unreaded" :
                $this->title = $lang->v('torrents_unreaded');
                $this->unreaded($_GET['cat']);
                break;
            default :
                $id = longval($_GET ['id']);
                if (!$id) {
                    $this->title = $lang->v('torrents_page');
                    $this->show();
                } else {
                    $this->title = $lang->v('torrents_torrent');
                    $this->show($id);
                }
                break;
        }
    }

    /**
     * Отметка прочитанным
     * @global db $db
     * @global users $users
     * @global stats $stats
     * @param int $id ID торрента
     * @return null
     */
    public function make_readed($id = "") {
        global $db, $users, $stats;
        if (!$users->v())
            return;
        $last_clean = $stats->read('last_clean_rt');
        $id = longval($id);
        if ($id) {
            $db->no_error()->insert(array(
                "torrent_id" => $id,
                "user_id" => $users->v('id')), 'read_torrents');
        } else {
            $rows = $db->query('SELECT t.id, t.posted_time FROM torrents AS t
                LEFT JOIN read_torrents AS rt ON rt.torrent_id=t.id AND rt.user_id=' . $users->v('id') . '
                WHERE rt.torrent_id=false OR rt.torrent_id IS NULL');
            while ($row = $db->fetch_assoc($rows)) {
                if ($row["posted_time"] > $last_clean)
                    $db->insert(array(
                        "torrent_id" => $row ['id'],
                        "user_id" => $users->v('id')), 'read_torrents', true);
            }
            $db->save_last_table();
        }
    }

    /**
     * RSS торрентов
     * @global tpl $tpl
     * @global db $db
     * @global config $config
     * @global users $users
     * @global categories $cats
     * @param string $cat категория
     * @param bool $atom Atom?
     * @return null
     */
    protected function rss($cat = null, $atom = false) {
        global $tpl, $db, $config, $users, $cats;
        $users->check_perms('torrents', 1, 2);
        ob_clean();
        @header("Content-Type: application/xml");
        $cat_rows = array();
        if ($cat)
            $where = (ref($cats)->condition($cat, $cat_rows));
        else
            $where = "on_top='1'";
        $row = $db->query('SELECT t.title, t.posted_time, u.username,
            t.content, t.id, t.screenshots FROM torrents AS t
            LEFT JOIN users AS u ON u.id=t.poster_id
            ' . ($where ? " WHERE " . $where : "") .
                " ORDER BY CAST(sticky AS BINARY) DESC, posted_time DESC" .
                ($config->v('max_rss_items') ? " LIMIT " . $config->v('max_rss_items') : ""));
        $tpl->assign('rows', $db->fetch2array($row));
        $tpl->assign('cat_rows', $cat_rows);

        $tpl->register_modifier("show_image", array(
            $this,
            'show_image'));

        if (!$atom)
            $tpl->display("torrents/rss.xtpl");
        else
            $tpl->display("torrents/atom.xtpl");
    }

    /**
     * Непрочтённые торренты
     * @global users $users
     * @global db $db
     * @global tpl $tpl
     * @global display $display
     * @global config $config
     * @global stats $stats
     * @global categories $cats
     * @param string $category категория
     * @return null
     * @throws EngineException
     */
    protected function unreaded($category = null) {
        global $users, $db, $tpl, $display, $config, $stats, $cats;
        $users->check_perms();
        $last_clean = $stats->read('last_clean_rt');
        $add = '';
        if ($last_clean)
            $add .= ' AND t.posted_time>' . $last_clean;
        if ($category) {
            $cid = $cats->get($category);
            if ($cid)
                $add .= ' AND ' . $cats->cat_where($cid['id']);
        }
        $count = $db->query('SELECT COUNT(*) FROM torrents AS t
            LEFT JOIN read_torrents AS rt ON rt.torrent_id=t.id AND rt.user_id=' . $users->v('id') . '
            WHERE (rt.torrent_id=false OR rt.torrent_id IS NULL)' . $add);
        $count = $db->fetch_row($count);
        $perpage = $config->v('table_torrents_perpage');
        list ( $pages, $limit ) = $display->pages($count[0], $perpage, 'change_tpage', 'page', '', true);
        $rows = $db->query('SELECT t.*, u.username, u.group FROM torrents AS t
            LEFT JOIN read_torrents AS rt ON rt.torrent_id=t.id AND rt.user_id=' . $users->v('id') . '
            LEFT JOIN users AS u ON t.poster_id=u.id
            WHERE (rt.torrent_id=false OR rt.torrent_id IS NULL)' . $add . '
            ORDER BY CAST(sticky AS BINARY) DESC, posted_time DESC
            LIMIT ' . $limit);
        $tpl->assign('rows', $db->fetch2array($rows));
        $tpl->assign('pages', $pages);
        $tpl->display('torrents/unreaded.tpl');
    }

    /**
     * Префильтр для показа торрентов
     * @global config $config
     * @global db $db
     * @global categories $cats
     * @global furl $furl
     * @global cache $cache
     * @global display $display
     * @param bool $full детальный ли просмотр торрента?
     * @param array $rows массив параметров
     * @return null
     */
    public function prefilter($full, &$rows) {
        global $config, $db, $cats, $furl, $cache, $display;
        if (!$full) {
            $rows ["content"] = $display->cut_text($rows ["content"], $config->v('max_sc_symb'));
        } else {
            $id = (int) $rows["id"];
            if (!$config->v('cache_details') || !($a = $cache->read("details/l-id" . $id))) {
                $r = $db->query('SELECT u.username, u.group, p.seeder FROM peers AS p
                LEFT JOIN users AS u ON u.id=p.uid
                WHERE p.tid = ' . $id);
                $seeders = "";
                $leechers = "";
                while ($row = $db->fetch_assoc($r)) {
                    $user = smarty_group_color_link($row["username"], $row["group"]);
                    if ($row['seeder'])
                        $seeders .= ( $seeders ? ", " : "") . $user;
                    else
                        $leechers .= ( $leechers ? ", " : "") . $user;
                }
                $downloaders = "";
                $r = $db->query('SELECT u.username, u.group FROM downloaded AS d
                LEFT JOIN users AS u ON u.id=d.uid
                WHERE d.tid = ' . $id . ' AND d.finished="1"');
                while ($row = $db->fetch_assoc($r)) {
                    $user = smarty_group_color_link($row["username"], $row["group"]);
                    $downloaders .= ( $seeders ? ", " : "") . $user;
                }
                $a["seeders_t"] = $seeders;
                $a["leechers_t"] = $leechers;
                $a["downloaders_t"] = $downloaders;
                if ($config->v('cache_details'))
                    $cache->write($a);
            }
            $rows = array_merge($rows, $a);
        }
        $cat_arr = $cats->cid2arr($rows ['category_id'], 0);
        $rows['tags'] = trim($rows['tags']);
        if ($rows['tags']) {
            $tags = explode(',', $rows['tags']);
            $r = "";
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!$tag)
                    continue;
                $r .= ( $r ? ", " : "") . "<a href='" . $furl->construct('search', array('auto' => true,
                            'tag' => $tag)) . "'>" . $tag . "</a>";
            }
            $rows ['tags'] = $r;
        }
        $rows ['filelist'] = (array) unserialize($rows ['filelist']);
        $rows ['cats_arr'] = $cat_arr [1];
        $rows ['cat_parents'] = $cat_arr [0];
    }

    /**
     * Отображение изображения/изображений для торрента
     * @global string $BASEURL
     * @global config $config
     * @global tpl $tpl
     * @param string $images сериалиованный массив изображений
     * @param bool $poster постер?
     * @param bool $rss для RSS?
     * @param string $align расположение постера
     * @return string HTML код изображения(ий)
     */
    public function show_image($images, $poster = true, $rss = false, $align = 'right') {
        global $BASEURL, $config, $tpl;
        $images = unserialize($images);
        if (!is_array($images) || !reset($images))
            return;
        if ($poster)
            $images = array($images[0]);
        else
            unset($images[key($images)]);
        $r = "";
        $mw = $poster ? $config->v('preview_width') : $this->scr_width;
        $mh = $poster ? $config->v('preview_height') : $this->scr_height;
        if (!$align)
            $align = 'right';
        foreach ($images as $image) {
            if (is_array($image)) {
                list($image, $preview) = $image;
                if (!$preview)
                    $preview = $image;
                $pre = $BASEURL . $config->v('screenshots_folder') . '/';
                $image = $pre . $image;
                $preview = $pre . $preview;
            } else
                $preview = $image;
            if ($rss)
                $r .= ( $r ? "&nbsp; &nbsp;" : "") . "
                    <a href='" . $image . "'><img alt='poster' src='" . $preview . "' /></a>";
            else
                $r .= ( $r ? "&nbsp; &nbsp;" : "") . "<a href='" . $image . "'
        rel='sexylightbox'>
                <img src='" . $preview . "' " . ($poster ? "align='" . $align . "'" : "") . "
                    alt='Image' class='cornerImg' style='max-width:" . $mw . "px;max-height:" . $mh . "px;'>
                </a>";
        }
        $tpl->assign('slbox_mbinited', true); // Инициализовать SexyLightbox
        return $r;
    }

    /**
     * Модификатор для заголовка торрент, добавляющий к нему иконку
     * @global string $theme_path
     * @param string $title заголовок торрента
     * @param string $image имя иконки(типа .png, хранящяяся в engine_images)
     * @param string $text подсказка к иконке
     * @return string заголовок с картинкой
     */
    public function prepend_title_icon($title, $image, $text = "") {
        global $theme_path;
        $title = '<img src="' . $theme_path . '/engine_images/' . $image . '.png" alt="' . $text . '"
        title="' . $text . '" align="left">' . $title;
        return $title;
    }

    /**
     * Отображение торрентов
     * @global db $db
     * @global tpl $tpl
     * @global display $display
     * @global config $config
     * @global bbcodes $bbcodes
     * @global plugins $plugins
     * @global users $users
     * @global lang $lang
     * @global categories $cats
     * @global stats $stats
     * @param int $id ID торрента
     * @param bool $full детальный?
     * @param bool $fe от редактирования?
     * @param array $data категория/дата
     * @return null
     * @throws EngineException 
     */
    public function show($id = null, $full = null, $fe = false, $data = null) {
        global $db, $tpl, $display, $config, $bbcodes, $plugins, $users, $lang, $cats, $stats;
        $lang->get('torrents');
        if (!$data)
            $data = $_GET;
        $id = (int) $id;
        $where = array();
        if (!$id) {
            $cat = mb_strtolower($display->strip_subpath($data ['cat']));
            $cat_rows = array();
            if (!$cat)
                $where [] = "t.on_top='1'";
            else
                $where [] = ref($cats)->condition($cat, $cat_rows);
            if ($cat_rows)
                $this->title = $cat_rows [0];
            $year = (int) $data ["year"];
            $month = (int) $data ["month"];
            $day = (int) $data ["day"];
            if ($day)
                $where [] = 't.posted_time BETWEEN ' . mktime(null, null, null, $month, $day, $year) . ' AND ' . (mktime(null, null, null, $month, $day + 1, $year) - 1);
            elseif ($month)
                $where [] = 't.posted_time BETWEEN ' . mktime(null, null, null, $month, null, $year) . ' AND ' . (mktime(null, null, null, $month + 1, null, $year) - 1);
            elseif ($year)
                $where [] = 't.posted_time BETWEEN ' . mktime(null, null, null, null, null, $year) . ' AND ' . (mktime(null, null, null, null, null, $year + 1) - 1);
            $full = false;
            $tpl->assign("add_url", "cat=" . $cat . "&year=" . $year . "&month=" . $month . "&day=" . $day);
        } else {
            $where [] = 't.id=' . $id;
            $tpl->assign("id", "id=" . $id);
            $tpl->assign("from_edit", $fe);
            if ($full !== false)
                $tpl->assign("full_torrents", true);
            $full = true;
        }
        try {
            $plugins->pass_data(array('where' => &$where), true)->run_hook('torrents_show_begin');

            $where = implode(" AND ", $where);
            $page = 'page';
            if (!$full && !$fe) {
                $count = $db->as_table('t')->count_rows("torrents", $where);
                $perpage = $config->v('torrents_perpage');
                $maxpage = intval($count / $perpage) + ($count % $perpage != 0 ? 1 : 0);
                list ( $pages, $limit ) = $display->pages($count, $perpage, 'change_tpage', $page, '', true);
                $tpl->assign("pages", $pages);
                $tpl->assign('page', $_GET[$page]);
                $tpl->assign('maxpage', $maxpage);
            } elseif ($full)
                $limit = 1;

            /* Монстрообразный запрос. Неплохо бы его оптимизировать,
             * но нет.
             * Да и если не смотреть на его размер, он должен(srsly?) выполняться достаточно быстро, 
             * учитывая то,
             * что в каждом случае LEFT JOIN поиск идёт либо по первичному ключу, либо по уникальному ключу.
             */
            $rows = $db->query('SELECT t.*, u.username, u.group, '
                    . ($full ? ' st.username AS su, st.group AS sg,' : '') .
                    'u2.username AS eu, u2.group AS eg' . ($users->v() ? ', b.id AS bookmark_id' .
                            (/* !$id */true ? ', rt.torrent_id AS readed' : "") : "") . ' FROM torrents AS t
            LEFT JOIN users AS u ON t.poster_id=u.id
            LEFT JOIN users AS u2 ON t.editor_id=u2.id
            ' . ($users->v() ? '
            LEFT JOIN bookmarks AS b ON t.id=b.toid AND b.type="torrents" AND b.user_id=' . $users->v('id') . '
            ' . (/* !$id */true ? ' LEFT JOIN read_torrents AS rt ON rt.torrent_id=t.id AND rt.user_id=' . $users->v('id') : "") : "")
                    . ($full ? ' LEFT JOIN users AS st ON st.id=t.status_by' : "")
                    . ($where ? ' WHERE ' . $where : "") .
                    (!$full ? ' ORDER BY CAST(t.sticky AS BINARY) DESC, t.posted_time DESC' : "") .
                    ($limit ? ' LIMIT ' . $limit : ""));
            $rows = $db->fetch2array($rows);
            if ($full && !$rows)
                throw new EngineException("torrents_no_this_torrents");
            $last_clean = $stats->read('last_clean_rt');
            if (!$fe && $full && !$rows [0] ['readed'] && $rows [0]['posted_time'] > $last_clean)
                $this->make_readed($id);
            if (!$fe && $full) {
                $this->title .= ' "' . $rows [0] ['title'] . '"';
                $tpl->assign("overall_keywords", $rows [0] ['tags']);
                if ($rows [0] ['content']) {
                    $what = array("\n", "\r", "  ");
                    $with = array(" ", " ", " ");
                    $meta = str_replace($what, $with, $rows [0] ['title'] . " " . $rows [0] ['content']);
                    $meta = $display->cut_text($meta, $config->v('max_meta_descr_symb'));
                    $tpl->assign("overall_descr", $bbcodes->remove_tags($meta));
                }
            }

            $plugins->pass_data(array('rows' => &$rows), true)->run_hook('torrents_show_end');
        } catch (PReturn $e) {
            return $e->r();
        }
        $tpl->register_modifier("show_image", array(
            $this,
            'show_image'));
        $tpl->register_modifier("prepend_title_icon", array(
            $this,
            'prepend_title_icon'));
        $tpl->register_modifier("torrents_prefilter", array(
            $this,
            'prefilter'));

        $tpl->assign('cat_rows', $cat_rows);
        $tpl->assign('torrents_row', $rows);
        $tpl->assign('last_clean_rt', $last_clean);
        $tpl->assign('statuses', self::$status);
        $lang->get('comments');
        if ($tpl->template_exists('torrents/cats_' . $cat . ".tpl") && $cat)
            $tpl->display('torrents/cats/' . $cat . ".tpl");
        else
            $tpl->display('torrents/index.tpl');
    }

    /**
     * Добавление торрентов
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @global lang $lang
     * @global categories $cats
     * @global plugins $plugins
     * @param string $cat имя категории
     * @param int $id ID торрента
     * @return null
     * @throws EngineException 
     */
    protected function add($cat, $id = null) {
        global $db, $users, $tpl, $lang, $cats, $plugins;
        $lang->get('torrents');
        $id = (int) $id;
        if ($id) {
            $row = $db->query('SELECT * FROM torrents WHERE id=' . $id . " LIMIT 1");
            $row = $db->fetch_assoc($row);
            if ($row) {
                if ($row["banned"] == 2)
                    throw new EngineException("torrents_cant_be_edited");
                $this->title .= ' "' . $row["title"] . '"';
                $adder = $row ['poster_id'];
                $cat = $row ['category_id'];
                if ($users->v('id') == $adder)
                    $users->check_perms('edit_torrents');
                else
                    $users->check_perms('edit_torrents', '2');
                $row["screenshots"] = unserialize($row["screenshots"]);
                $tpl->assign('nrow', $row);
                $tpl->assign('id', $id);
            } else
                throw new EngineException('torrents_this_torrents_are_not_exists');
        }
        if (!$row['screenshots']) {
            $row['screenshots'] = array(array(), array());
            $tpl->assign('nrow', $row);
        }

        try {
            $plugins->pass_data(array('row' => &$row), true)->run_hook('torrents_add');
        } catch (PReturn $e) {
            return $e->r();
        }

        $tpl->assign('categories_selector', $cats->ajax_selector($cat));
        $tpl->assign("num", 0);
        $tpl->display('torrents/add.tpl');
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
     * @global config $config
     * @param array $c массив, если файл
     * @param string $ni URL изображения для сравнения, чтобы не удалять новый файл
     * @param string $np URL превью для сравнения, чтобы не удалять новый файл
     * @return null
     */
    protected function check_isfile($c, $ni = null, $np = null) {
        global $config;
        if (!is_array($c))
            return;
        $path = ROOT . $config->v('screenshots_folder') . '/';
        $i = $p = "";
        list($i, $p) = $c;
        if ($ni != $i && $i)
            @unlink($path . $i);
        if ($np != $p && $p)
            @unlink($path . $p);
    }

    /**
     * Загрузка скриншотов
     * @global uploader $uploader
     * @global config $config
     * @global lang $lang
     * @global display $display
     * @param int $id ID торрента
     * @param string $filevars имя массива($_REQUEST или $_FILES) для загрузки
     * @param string $err ошибки, возникшие при добавлении скриншотов
     * @param string $old старый сериализованный массив скриншотов
     * @return string сериализованный массив скриншотов
     */
    protected function screenshots($id, $filevars, &$err = "", $old = null) {
        global $uploader, $config, $lang, $display;
        $surl = (array) $_REQUEST[$filevars];
        if (!is($config->v('allowed_screenshots'), ALLOWED_IMG_URL))
            $surl = array();
        $stfile = (array) $_FILES[$filevars];
        $sfile = $stfile['tmp_name'];
        if (!is($config->v('allowed_screenshots'), ALLOWED_IMG_PC))
            $sfile = $stfile = array();
        $r = array();
        $l = null;
        $maxscreenshots = $config->v('max_screenshots');
        if ($maxscreenshots < 2)
            $maxscreenshots = 2;
        if ($old) {
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
        } else
            $old = array();
        $u = $s = 0;
        $inum = $lang->v('torrents_image_n');
        foreach ($surl as $n => $url) {
            $n = (int) $n;
            $u++;
            $s++;
            if ($s > $maxscreenshots)
                break;
            if (!preg_match('/^' . display::url_pattern . '$/siu', $url) || ($config->v('check_rimage') && !$uploader->is_image($url))) {
                $err .= ( $err ? "\n" : "") . ($inum . $u . '. ' . $lang->v('torrents_image_invalid_url'));
                continue;
            }
            $this->check_isfile($old[$n]);
            $r[$n] = $url;
        }
        $f = $i = $fi = 0;
        $purl = self::image_prefix . $id;
        if ($l) {
            preg_match('/^' . mpc($purl) . '([0-9]+)\./', $l, $matches);
            $fi = $matches[1] + 1;
        }
        ref($uploader);
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
            $preview = true;
            try {
                if ($n > 0) // тобишь не постер
                    $uploader->set_preview_size($this->scr_width, $this->scr_height);
                $uploader->upload($fvar, $config->v('screenshots_folder'), /* ссылка */ $tmp = 'images', $url, false, $preview);
                if ($config->v('watermark_text'))
                    $uploader->watermark($config->v('screenshots_folder') . '/' . $url, $config->v('watermark_text'), 'auto', true, null, $config->v('watermark_pos'));
                $this->check_isfile($old[$n], $url, $preview);
                $r[$n] = array($url, $preview);
                $fi++;
            } catch (EngineException $e) {
                $err .= ( $err ? "\n" : "") . ($inum . ($u + $f) . '. ' . $e->getEMessage());
            }
        }
        ksort($r);
        return serialize(array_values($r));
    }

    /**
     * Сохранение торрента
     * @global tpl $tpl
     * @global db $db
     * @global lang $lang
     * @global users $users
     * @global polls $polls
     * @global etc $etc
     * @global mailer $mailer
     * @global bittorrent $bt
     * @global config $config
     * @global getpeers $getpeers
     * @global categories $cats
     * @global plugins $plugins
     * @param array $data массив данных
     * @param int $id ID торрента
     * @param bool $short быстрое редактирование?
     * @return int ID созданного(отредактированного) торрента
     * @throws EngineException 
     */
    public function save($data, $id = null, $short = false) {
        global $furl, $db, $lang, $users, $polls, $etc, $mailer, $bt, $config, $getpeers, $cats, $plugins;

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
        $lang->get('torrents');
        $id = (int) $id;
        $price = (float) $price;
        if ($id) {
            $row = $db->query('SELECT * FROM torrents WHERE id=' . $id . ' LIMIT 1');
            $row = $db->fetch_assoc($row);
            if ($row) {
                if ($row["banned"] == 2)
                    throw new EngineException("torrents_cant_be_edited");
                if ($users->v('id') == $row ['poster_id'])
                    $users->check_perms('edit_torrents');
                else
                    $users->check_perms('edit_torrents', '2');
                $edit_count = $row['edit_count'];
            } else
                throw new EngineException('torrents_this_torrents_are_not_exists');
        }
        if (!$content)
            throw new EngineException('torrents_no_content');
        if (!$title)
            throw new EngineException('torrents_no_title');
        if (!is_null($cat) || !$id) {
            $mcats = $cat;
            $cat = ref($cats)->save_selected($mcats);
            if (!$cat)
                throw new EngineException('torrents_no_selected_cat');
        }
        $update = array(
            'title' => $title);
        if (!is_null($content) || !$id)
            $update['content'] = $content;
        if (!is_null($cat) || !$id)
            $update['category_id'] = $cat;
        if (!is_null($tags) || !$id)
            $update['tags'] = preg_replace('/\s*,\s*/su', ',', $tags);
        if (!is_null($price) && $price <= $config->v('max_torrent_price') && $users->perm('ct_price'))
            $update['price'] = $price;
        if (!is_null($sticky) || !$id) {
            $sticky = ($users->perm('msticky_torrents') ? $sticky : 0);
            if ($users->perm('msticky_torrents') || !$id)
                $update['sticky'] = $sticky;
        }
        $update['last_active'] = time();
        $error = "";
        try {
            if (!$id) {
                $filelist = "";
                $size = 0;
                $announce_list = "";
                $time = time(); // Важно для именования файлов! Пишется в posted_time
                $poster_id = $users->v('id'); // Важно для именования файлов! Пишется в poster_id
                $sid = $bt->get_filename($time, $poster_id);
                $infohash = $bt->torrent_file($sid, $_FILES[$tfname], $filelist, $size, $announce_list);
                $screenshots = $this->screenshots($sid, $imname, $error);
                $update['info_hash'] = $infohash;
                $update['size'] = $size;
                $update['filelist'] = $filelist;
                $update['screenshots'] = $screenshots;
                $update['announce_list'] = $announce_list;
                $update['posted_time'] = $time;
                $update['poster_id'] = $poster_id;

                $plugins->pass_data(array('update' => &$update,
                    'error' => &$error), true)->run_hook('torrents_save_add');

                $id = $db->no_error()->insert($update, 'torrents');
                if ($db->errno() == UNIQUE_VALUE_ERROR)
                    throw new EngineException('torrents_torrent_already_exists');
                elseif ($db->errno())
                    $db->err();
                if ($config->v('getpeers_after_upload'))
                    $getpeers->get_peers($id, $announce_list, $infohash);
                $etc->add_res();
                $mailer->change_type('categories')->update($mcats);
            } else {
                $sid = $bt->get_filename($row["posted_time"], $row["poster_id"]);
                if ($tfname && $_FILES[$tfname]['tmp_name']) {
                    $filelist = "";
                    $size = 0;
                    $announce_list = "";
                    $infohash = $bt->torrent_file($sid, $_FILES[$tfname], $filelist, $size, $announce_list);
                    $update['info_hash'] = $infohash;
                    $update['size'] = $size;
                    $update['filelist'] = $filelist;
                    $update['announce_list'] = $announce_list;
                    if ($config->v('getpeers_after_upload'))
                        $update['announce_stat'] = serialize($getpeers->get_peers($id, $announce_list, $infohash, false));
                    else
                        $update['announce_stat'] = "";
                }
                if ($imname) {
                    $screenshots = $this->screenshots($sid, $imname, $error, $row["screenshots"]);
                    $update["screenshots"] = $screenshots;
                }
                $update['last_edit'] = time();
                $update['edit_reason'] = $edit_reason;
                $update['editor_id'] = $users->v('id');
                $update['edit_count'] = $edit_count + 1;

                $plugins->pass_data(array('update' => &$update,
                    'id' => $id,
                    'error' => &$error), true)->run_hook('torrents_save_edit');

                $db->no_error()->update($update, 'torrents', 'WHERE id=' . $id . ' LIMIT 1');
                if ($db->errno() == UNIQUE_VALUE_ERROR)
                    throw new EngineException('torrents_torrent_already_exists');
                elseif ($db->errno())
                    $db->err();
                log_add("edited_torrents", "user", array($row ['title'], $id));
            }

            $plugins->run_hook('torrents_save_end');

            try {
                $users->perm_exception();
                $polls->change_type('torrents')->save($data, $id);
            } catch (EngineException $e) {
                if ($e->getCode())
                    throw $e;
            }
        } catch (PReturn $e) {
            return $e->r();
        }
        if ($error)
            throw new EngineException('torrent_uploaded_but', array(
                $furl->construct('torrents', array(
                    'id' => $id,
                    'title' => $title)),
                $error));
        return $id;
    }

}

class torrents_ajax {

    /**
     * Имя блока для отображения
     * @var string
     */
    protected $block_name = "torrents_simple";

    /**
     * Инициализация AJAX части торрентов
     * @global comments $comments
     * @global plugins $plugins
     * @return null
     */
    public function init() {
        global $comments, $plugins;
        $act = $_GET ['act'];
        $id = (int) $_POST["id"];
        $n = $plugins->get_module('torrents');
        switch ($act) {
            case "clear_comm":
                $comments->change_type('torrents')->clear($id);
                die("OK!");
                break;
            case "status":
                $status = $_POST["status"];
                $this->set_status($id, $status);
                die();
                break;
            case "getpeers":
                $this->getpeers($id);
                die();
                break;
            case "save":
                $c = (bool) $_GET["c"];
                $id = (int) $_GET["id"];
                $n->save($_POST, $id, true);
                print('OK!');
                $n->show($id, (bool) $_GET["full"], true);
                die();
                break;
            case "quick_edit":
                $this->quick_edit($id);
                break;
            case "delete" :
                $this->delete($id);
                die("OK!");
                break;
            case "read" :
                $n->make_readed($id);
                die("OK!");
                break;
            case "show":
                $this->show($_GET['cats']);
                break;
            default :
                break;
        }
    }

    /**
     * Простое отображение торрентов для блока
     * @global db $db
     * @global categories $cats
     * @global tpl $tpl
     * @global lang $lang
     * @global blocks $blocks
     * @global cache $cache
     * @global display $display
     * @global users $users
     * @global plugins $plugins
     * @param string $catids ID категорий через "|"
     * @return null
     * @throws EngineException 
     */
    protected function show($catids) {
        global $db, $cats, $tpl, $lang, $blocks, $cache, $display, $users, $plugins;
        if (!$users->perm('torrents'))
            return;
        if (!preg_match('/^([0-9]+\|)+$/', $catids . '|'))
            throw new EngineException;
        $crc = crc32($catids);
        $cfile = 'tsimple/cat-' . $crc;
        $lang->get('blocks/torrents');
        if (($a = $cache->read($cfile)) === false) {
            $settings = $blocks->get_settings($this->block_name);
            if (!$settings || !in_array($catids, $settings['cats']))
                throw new EngineException;
            $limit = (int) $settings['limit'];
            if ($limit > 20 || $limit <= 0)
                $limit = 20;
            if ($limit % 2 == 1)
                $limit--;
            $max_title_symb = (int) $settings['max_title_symb'];
            if (!$max_title_symb)
                $max_title_symb = 100;
            $r = $db->query('SELECT t.id, t.title, t.seeders, t.leechers, 
            t.size, t.screenshots, u.username, u.group, t.posted_time 
            FROM torrents AS t LEFT JOIN users AS u ON u.id=t.poster_id
            WHERE ' . $cats->cat_where($catids, true) . ' AND on_top="1"
            ORDER BY posted_time DESC
            LIMIT ' . $limit);
            $a = array();
            $torrents = $plugins->get_module('torrents');
            while ($row = $db->fetch_assoc($r)) {
                $row['screenshots'] = $torrents->show_image($row['screenshots'], true, false, "center");
                $title = $row['title'];
                if (preg_match('/^(.*)\/(.*?)(?:\(([0-9]+)\))?$/siu', $title, $matches)) {
                    $row['name'] = $display->cut_text($matches[1], $max_title_symb);
                    $row['orig_name'] = $display->cut_text($matches[2], $max_title_symb);
                    $row['year'] = $matches[3];
                } else
                    $row['name'] = $display->cut_text($row['title'], $max_title_symb);
                $a[] = $row;
            }
            $cache->write($a);
        }
        $tpl->assign('res', $a);
        $tpl->display('blocks/contents/torrents.tpl');
    }

    /**
     * Установка статуса торрента
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @global lang $lang
     * @param int $id ID торрента
     * @param int $status статус торрента
     * @return null
     * @throws EngineException
     */
    protected function set_status($id, $status) {
        global $db, $users, $tpl, $lang;
        if (!isset(torrents::$status[$status]))
            throw new EngineException;
        check_formkey();
        $id = (int) $id;
        $users->check_perms('edit_torrents', '2');
        $torrents = array('status' => $status,
            'status_by' => $users->v('id'),
            'banned' => torrents::$status[$status],
            'on_top' => (int) !torrents::$status[$status]);
        $db->update($torrents, 'torrents', 'WHERE id=' . $id . ' LIMIT 1');
        $torrents['id'] = $id;
        $torrents['su'] = $users->v('username');
        $torrents['sg'] = $users->v('group');
        $lang->get('torrents');
        $tpl->assign('torrents', $torrents);
        $tpl->assign('statuses', torrents::$status);
        $tpl->display('torrents/status.tpl');
    }

    /**
     * Обрезка URL аннонсера
     * @global display $display
     * @param string $url URL аннонсера
     * @return string обрезанный URL
     */
    public function cut_tracker($url) {
        global $display;
        preg_match('/^' . display::url_pattern . '$/siu', $url, $m);
        return $m[2] . "://" . $m[3] . "/";
    }

    /**
     * Получение списка "левых пиров" для торрента
     * @global db $db
     * @global getpeers $getpeers
     * @global config $config
     * @global tpl $tpl
     * @global lang $lang
     * @param int $id ID торрента
     * @return null
     */
    protected function getpeers($id) {
        global $db, $getpeers, $config, $tpl, $lang;
        $lang->get('torrents');
        $id = (int) $id;
        $r = $db->query('SELECT announce_stat, announce_list, info_hash FROM torrents WHERE id=' . $id . ' LIMIT 1');
        list($announce_stat, $announces, $infohash) = $db->fetch_row($r);
        if (!$infohash)
            throw new EngineException('torrents_this_torrents_are_not_exists');
        $hour = 3600;
        $announce_stat = unserialize($announce_stat);
        if ($config->v('get_peers_interval') &&
                $announce_stat['last_update'] <= time() - $config->v('get_peers_interval') * $hour)
            $announce_stat = $getpeers->get_peers($id, $announces, $infohash);
        unset($announce_stat['last_update']);
        $tpl->register_modifier('cut_tracker', array($this, "cut_tracker"));
        $tpl->assign('trackers', $announce_stat);
        $tpl->display('torrents/left_peers.tpl');
    }

    /**
     * Удаление торрента
     * @global db $db
     * @global users $users
     * @global etc $etc
     * @global polls $polls
     * @global comments $comments
     * @global bittorrent $bt
     * @global config $config
     * @global cache $cache
     * @global rating $rating
     * @global mailer $mailer
     * @global plugins $plugins
     * @param int $id ID торрента
     * @return null
     * @throws EngineException 
     */
    public function delete($id) {
        global $db, $users, $etc, $polls, $comments, $bt, $config, $cache, $rating, $mailer, $plugins;
        check_formkey();
        $id = (int) $id;
        $row = $db->query('SELECT t.poster_id, t.title, t.posted_time, t.screenshots, p.id AS poll_id FROM torrents AS t
                LEFT JOIN polls AS p ON p.type="torrents" AND p.toid=t.id
                WHERE t.id=' . $id . ' LIMIT 1');
        list ($poster_id, $title, $posted_time, $screenshots, $pid) = $db->fetch_row($row);
        if ($row) {
            if ($users->v('id') == $poster_id)
                $users->check_perms('del_torrents');
            else
                $users->check_perms('del_torrents', '2');
        } else
            throw new EngineException('torrents_this_torrents_are_not_exists');
        $db->delete('torrents', 'WHERE id=' . $id . ' LIMIT 1');

        try {
            $plugins->pass_data(array('id' => $id), true)->run_hook('torrents_delete');
        } catch (PReturn $e) {
            return $e->r();
        }

        $cache->remove("details/l-id" . $id);
        $f = $bt->get_filename($posted_time, $poster_id);
        @unlink(ROOT . $config->v('torrents_folder') . '/' . bittorrent::torrent_prefix .
                        $f . ".torrent");
        $screenshots = unserialize($screenshots);
        foreach ($screenshots as $screenshot)
            if (is_array($screenshot)) {
                if ($screenshot[0])
                    @unlink(ROOT . $config->v('screenshots_folder') . '/' . $screenshot[0]);
                if ($screenshot[1])
                    @unlink(ROOT . $config->v('screenshots_folder') . '/' . $screenshot[1]);
            }
        $db->delete('read_torrents', 'WHERE torrent_id=' . $id . ' LIMIT 1');
        $etc->add_res('torrents', -1, '', $poster_id);
        log_add("deleted_torrents", "user", array($title));
        $b = $users->admin_mode(true);
        $comments->change_type('torrents')->clear($id);
        $rating->change_type('torrents')->clear($id);
        $mailer->change_type('torrents')->remove($id);
        if ($pid)
            $polls->delete($pid);
        if (!$b)
            $users->admin_mode();
    }

    /**
     * Форма быстрого редактирования торрента
     * @global db $db
     * @global users $users
     * @global tpl $tpl
     * @global lang $lang
     * @param int $id ID торрента
     * @return null
     * @throws EngineException
     */
    protected function quick_edit($id) {
        global $db, $users, $tpl, $lang;
        $lang->get("torrents");
        $row = $db->query('SELECT * FROM torrents WHERE banned <> "2" AND id=' . longval($id) . ' LIMIT 1');
        $row = $db->fetch_assoc($row);
        if (!$row)
            throw new EngineException;
        if ($users->v('id') == $row['poster_id'])
            $users->check_perms('edit_torrents');
        else
            $users->check_perms('edit_torrents', '2');
        $tpl->assign('row', $row);
        $tpl->display('torrents/edit.tpl');
    }

}

?>