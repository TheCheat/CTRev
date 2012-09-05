<?php

/**
 * Project:             CTRev
 * File:                search_module.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright           (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name 		Поиск торрентов
 * @version             1.00
 */
if (!defined('INSITE'))
    die("Remote access denied!");

class search_module {

    /**
     * Поля сортировки
     * @var array
     */
    protected $orderby_types = array(
        "posted_time",
        "username",
        "title",
        "comm_count",
        "avg_rate",
        "rnum_count",
        "seeders",
        "leechers",
        "downloaded",
        "status");

    /**
     * Заголовок модуля
     * @var string
     */
    public $title = "";

    /**
     * Инициализация модуля поиска
     * @global lang $lang
     * @global users $users
     * @global display $display
     * @return null
     */
    public function init() {
        global $lang, $users, $display;
        $lang->get("search");
        $this->title = $lang->v('search_page');
        $act = $_GET['act'];
        if ($_GET['user'] || $_GET['email'])
            $act = 'user';
        switch ($act) {
            case "user":
                $users->check_perms('usearch', 1, 2);
                $this->users($_GET);
                break;
            default:
                $users->check_perms('torrents', 1, 2);
                $searched = $_REQUEST ["auto"];
                if (!$searched)
                    $this->torrents($_REQUEST ["query"], $_REQUEST ["author"]);
                else {
                    $_REQUEST["search_str"] = $_REQUEST ["query"];
                    $_POST["category"] = $_POST["categories"];
                    $_POST["search_in"] = (int) $_POST["search_in"];
                    $_POST["posted_from"] = $display->make_time("from", "ymd");
                    $_POST["posted_to"] = $display->make_time("to", "ymd");
                    $this->torrents_results(array_merge($_POST, $_REQUEST));
                }
                break;
        }
    }

    /**
     * Отображение поисковых параметров пользователя
     * @global tpl $tpl
     * @param array $data массив данных
     * @return null
     */
    protected function users($data) {
        global $tpl;
        $tpl->assign('uname', $data ['user']);
        $tpl->assign('email', $data ['email']);
        $tpl->assign('ip', $data ['ip']);
        $tpl->assign('parent_form', $data['form']);
        $tpl->assign('parent_el', $data['field']);
        $tpl->display('profile/search_user.tpl');
    }

    /**
     * Отображение поисковых параметров
     * @global tpl $tpl
     * @global plugins $plugins
     * @global lang $lang
     * @param string $search поиск по ключевым словам
     * @param string $author поиск по автору
     * @return null
     */
    protected function torrents($search, $author) {
        global $tpl/* , $input */, $plugins, $lang;
        /* $torrents = */$plugins->get_module('torrents');
        $lang->get('torrents');
        $tpl->assign('statuses', torrents::$status);
        $tpl->assign("search", $search);
        $tpl->assign("author", $author);
        $tpl->assign("search_str", $search);
        $tpl->assign("orderby_types", $this->orderby_types);
        $tpl->display("torrents/search.tpl");
    }

    /**
     * Отображение результатов поиска
     * @global search $search
     * @global db $db
     * @global tpl $tpl
     * @global display $display
     * @global config $config
     * @global categories $cats
     * @global plugins $plugins
     * @param array $data массив данных для поиска
     * @return null
     */
    protected function torrents_results($data) {
        global $search, $db, $tpl, $display, $config, $cats, $plugins;

        $torrents = $plugins->get_module('torrents');
        $tpl->register_modifier('show_image', array($torrents, "show_image"));

        $data_params = array("search_str",
            "author",
            "category",
            "search_in",
            "posted_from",
            "posted_to",
            "orderby",
            "orderby_type",
            "tag",
            "status");
        extract(rex($data, $data_params));

        if ($category && is_array($category))
            $category = array_map("intval", $category);
        $search_in = (int) $search_in;
        $posted_from = (int) $posted_from;
        $posted_to = (int) $posted_to;
        $where = array();
        switch ($search_in) {
            case 1 :
                $columns = "title";
                break;
            default :
                $columns = array(
                    "title",
                    "content");
                break;
        }
        ref($search);
        $regexp = true;
        if ($search_str)
            $where[] = $search->make_where($search_str, $columns, $regexp);
        if ($author)
            $where[] = $search->like_where(mb_strtolower($author), "username_lower");
        if ($tag)
            $where[] = 'CONCAT(",",`tags`,",") LIKE "%,' . $db->sesc($tag) . ',%"';
        if ($posted_from || $posted_to) {
            if ($posted_to) {
                $day = 24 * 60 * 60;
                $posted_to += $day - 1;
            }
            if (!$posted_from || !$posted_to)
                $where[] = ( $posted_from ? 'posted_time>=' . $posted_from : "") . ($posted_to ? 'posted_time<=' . $posted_to : "");
            else
                $where[] = 'posted_time BETWEEN ' . $posted_from . ' AND ' . $posted_to;
        }
        if (isset(torrents::$status[$status]) || $status == 'unchecked')
            $where[] = '`status`=' . $db->esc($status);

        try {
            $plugins->pass_data(array('where' => &$where,
                'orderby' => &$this->orderby_types), true)->run_hook('search_torrents');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (in_array($orderby, $this->orderby_types))
            $orderby = "`" . $orderby . "` " . $orderby_type;
        else
            $orderby = "";
        if (!$where)
            mess('search_nothing_searching');
        if ($category && is_array($category))
            $where[] = $cats->cat_where(implode('|', $category), true);

        $where = $where ? '(' . implode(') AND (', $where) . ')' : '';

        list ($count) = $db->fetch_row($db->query('SELECT COUNT(*) FROM torrents AS t
            LEFT JOIN users AS u ON u.id=t.poster_id
            ' . ($where ? ' WHERE ' . $where : "")));
        $perpage = $config->v('torrents_perpage');
        list ( $pages, $limit ) = $display->pages($count, $perpage, 'change_spage', 'page', '', true);
        $res = $db->query('SELECT t.*, u.username,u.group,
            IF(t.rnum_count<>0,t.rate_count/t.rnum_count,0) AS avg_rate
            FROM torrents AS t LEFT JOIN users AS u ON u.id=t.poster_id
            ' . ($where ? 'WHERE ' . $where : "") . '
            ' . ($orderby ? 'ORDER BY ' . $orderby : "") . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        $rows = array();
        while ($row = $db->fetch_assoc($res)) {
            $row ["otitle"] = $row ["title"];
            if ($regexp && $regexp !== true) {
                $search->highlight_text($row ["content"], $regexp);
                $search->highlight_text($row ["title"], $regexp, false);
            } else
                $search->cut_search($row ["content"]);
            $rows[] = $row;
        }
        $tpl->assign("post", http_build_query($_POST));
        $g = $_GET;
        unset($g["module"]);
        if ($g) {
            unset($g["from_ajax"]);
            unset($g["page"]);
        }
        $tpl->assign("get", http_build_query($g));
        $tpl->assign("rows", $rows);
        $tpl->assign("pages", $pages);
        $tpl->display("torrents/search_result.tpl");
    }

}

class search_module_ajax {

    /**
     * Поля сортировки
     * @var array
     */
    protected $orderby = array(
        "username",
        "torrents_count",
        '', // из settings
        '', // из settings
        "registered",
        "last_visited");

    /**
     * Инициализация поиска AJAX
     * @return null
     */
    public function init() {
        $act = $_GET['act'];
        switch ($act) {
            case "user":
            default:
                $this->users_results($_POST);
                break;
        }
    }

    /**
     * Результат поиска
     * @global tpl $tpl
     * @global lang $lang
     * @global db $db
     * @global search $search
     * @global users $users
     * @global display $display
     * @global config $config
     * @global plugins $plugins
     * @param array $data массив данных для поиска
     * @return null
     */
    protected function users_results($data) {
        global $tpl, $lang, $db, $search, $users, $display, $config, $plugins;
        $unco = (bool) $_GET['unco'];
        $parent = (bool) $_GET['parent'];
        $inadmin = $users->check_inadmin("users");
        if (!$inadmin) {
            $users->check_perms('usearch', 1, 2);
            $unco = false;
        }
        $lang->get('search');
        $where = array();

        $data_params = array("uname" => "user",
            "email",
            "ip",
            "icq",
            "skype",
            "name",
            "country",
            "group",
            "reg_type",
            "lv_type",
            "subupdate",
            'orderby');
        extract(rex($data, $data_params));

        ref($search);
        if ($unco)
            $where [] = "confirmed <> '3'";
        if ($uname && ($cwhere = $search->like_where($uname, 'username')))
            $where [] = $cwhere;
        if ($email && ($cwhere = $search->like_where($email, 'email')))
            $where [] = $cwhere;
        if ($ip && ($ip = $search->search_ip($ip)))
            $where [] = $ip;
        if ($icq)
            $where [] = $search->search_settings('icq', $icq);
        if ($skype)
            $where [] = $search->search_settings('skype', $skype);
        if ($name)
            $where [] = $search->search_settings('name_surname', $name);
        $country = (int) $country;
        if ($country)
            $where [] = $search->search_settings('country', $country);
        $group = (int) $group;
        if ($group)
            $where [] = '`group`=' . $group;
        $day = 60 * 60 * 24;
        $sign1 = (!$reg_type ? "==" : ($reg_type == 1 ? ">=" : "<="));
        $registered = $display->make_time("reg", "ymd");
        if ($registered)
            switch ($sign1) {
                case "==":
                    $registered2 = $registered + $day;
                    break;
                case ">=":
                    $registered2 = time();
                    break;
                case "<=":
                    $registered2 = $last_visited;
                    $last_visited = 0;
                    break;
            }
        if ($registered || $registered2)
            $where [] = 'registered BETWEEN ' . longval($registered) . ' AND ' . longval($registered2 - ($sign1 != ">=" ? 1 : 0));
        $sign2 = (!$lv_type ? "==" : ($lv_type == 1 ? ">=" : "<="));
        $last_visited = $display->make_time("lv", "ymd");
        if ($last_visited)
            switch ($sign2) {
                case "==":
                    $last_visited2 = $last_visited + $day;
                    break;
                case ">=":
                    $last_visited2 = time();
                    break;
                case "<=":
                    $last_visited2 = $last_visited;
                    $last_visited = 0;
                    break;
            }
        if ($last_visited || $last_visited2)
            $where [] = 'last_visited BETWEEN ' . longval($last_visited) . ' AND ' . longval($last_visited2 - ($sign2 != ">=" ? 1 : 0));

        try {
            $plugins->pass_data(array('where' => &$where,
                'orderby' => &$this->orderby), true)->run_hook('search_users');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$inadmin && !$where)
            mess('nothing_selected');
        if ($orderby) {
            $sort = explode(",", $orderby);
            $c = count($sort);
            $orderby = '';
            for ($i = 0; $i < $c; $i += 2) {
                if (!$this->orderby [$sort[$i]])
                    continue;
                $orderby .= ($orderby ? ', ' : '') .
                        "`" . $this->orderby [$sort[$i]] . "` " . ($sort[$i + 1] ? "asc" : "desc");
            }
        }
        $where [] = 'id>0';
        $where = ($where ? ("(" . implode(") AND (", $where) . ")") : null);
        $count = $db->count_rows('users', $where);
        list ( $pages, $limit ) = $display->pages($count, $config->v('table_perpage'), 'submit_search_form', 'page', '', true);
        $rows = $db->query('SELECT id,username,`group`,settings,
            registered,last_visited,torrents_count FROM users
            ' . ($where ? 'WHERE ' . $where : "") . '
            ' . ($orderby ? 'ORDER BY ' . $orderby : "") . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        $tpl->assign('unco', $unco);
        $tpl->assign('rows', $db->fetch2array($rows));
        $tpl->assign('pages', $pages);
        if ($parent)
            $tpl->assign('parented_window', true);
        else
            $tpl->assign('parented_window', false);
        $tpl->assign('subupdate', (int) $subupdate);
        $tpl->display('profile/search_result.tpl');
    }

}

?>