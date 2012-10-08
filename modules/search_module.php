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
     * @var array $orderby_types
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
     * @var string $title
     */
    public $title = "";

    /**
     * Инициализация модуля поиска
     * @return null
     */
    public function init() {
        lang::o()->get("search");
        $this->title = lang::o()->v('search_page');
        $act = $_GET['act'];
        if ($_GET['user'] || $_GET['email'])
            $act = 'user';
        switch ($act) {
            case "user":
                users::o()->check_perms('usearch', 1, 2);
                $this->users($_GET);
                break;
            default:
                users::o()->check_perms('torrents', 1, 2);
                $searched = $_REQUEST ["auto"];
                if (!$searched)
                    $this->torrents($_REQUEST ["query"], $_REQUEST ["author"]);
                else {
                    $_REQUEST["search_str"] = $_REQUEST ["query"];
                    $_POST["category"] = $_POST["categories"];
                    $_POST["search_in"] = (int) $_POST["search_in"];
                    $_POST["posted_from"] = display::o()->make_time("from", "ymd");
                    $_POST["posted_to"] = display::o()->make_time("to", "ymd");
                    $this->torrents_results(array_merge($_POST, $_REQUEST));
                }
                break;
        }
    }

    /**
     * Отображение поисковых параметров пользователя
     * @param array $data массив данных
     * @return null
     */
    protected function users($data) {
        tpl::o()->assign('uname', $data ['user']);
        tpl::o()->assign('email', $data ['email']);
        tpl::o()->assign('ip', $data ['ip']);
        tpl::o()->assign('parent_form', $data['form']);
        tpl::o()->assign('parent_el', $data['field']);
        tpl::o()->display('profile/search_user.tpl');
    }

    /**
     * Отображение поисковых параметров
     * @param string $search поиск по ключевым словам
     * @param string $author поиск по автору
     * @return null
     */
    protected function torrents($search, $author) {
        /* $torrents = */plugins::o()->get_module('torrents');
        lang::o()->get('torrents');
        tpl::o()->assign('statuses', torrents::$status);
        tpl::o()->assign("search", $search);
        tpl::o()->assign("author", $author);
        tpl::o()->assign("search_str", $search);
        tpl::o()->assign("orderby_types", $this->orderby_types);
        tpl::o()->display("torrents/search.tpl");
    }

    /**
     * Отображение результатов поиска
     * @param array $data массив данных для поиска
     * @return null
     */
    protected function torrents_results($data) {

        /* @var $torrents torrents */
        $torrents = plugins::o()->get_module('torrents');
        tpl::o()->register_modifier('show_image', array($torrents, "show_image"));
        /* @var $search search */
        $search = n("search");
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
            $where[] = 'CONCAT(",",`tags`,",") LIKE "%,' . db::o()->sesc($tag) . ',%"';
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
            $where[] = '`status`=' . db::o()->esc($status);

        try {
            plugins::o()->pass_data(array('where' => &$where,
                'orderby' => &$this->orderby_types), true)->run_hook('search_torrents');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (in_array($orderby, $this->orderby_types))
            $orderby = "`" . $orderby . "` " . $orderby_type;
        else
            $orderby = "";
        if (!$where)
            message('search_nothing_searching');
        if ($category && is_array($category)) {
            /* @var $cats categories */
            $cats = n("categories");
            $where[] = $cats->cat_where(implode('|', $category), true);
        }

        $where = $where ? '(' . implode(') AND (', $where) . ')' : '';

        list ($count) = db::o()->fetch_row(db::o()->query('SELECT COUNT(*) FROM torrents AS t
            LEFT JOIN users AS u ON u.id=t.poster_id
            ' . ($where ? ' WHERE ' . $where : "")));
        $perpage = config::o()->v('torrents_perpage');
        list ( $pages, $limit ) = display::o()->pages($count, $perpage, 'change_spage', 'page', '', true);
        $res = db::o()->query('SELECT t.*, u.username,u.group,
            IF(t.rnum_count<>0,t.rate_count/t.rnum_count,0) AS avg_rate
            FROM torrents AS t LEFT JOIN users AS u ON u.id=t.poster_id
            ' . ($where ? 'WHERE ' . $where : "") . '
            ' . ($orderby ? 'ORDER BY ' . $orderby : "") . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        $rows = array();
        while ($row = db::o()->fetch_assoc($res)) {
            $row ["otitle"] = $row ["title"];
            if ($regexp && $regexp !== true) {
                $search->highlight_text($row ["content"], $regexp);
                $search->highlight_text($row ["title"], $regexp, false);
            } else
                $search->cut_search($row ["content"]);
            $rows[] = $row;
        }
        tpl::o()->assign("post", http_build_query($_POST));
        $g = $_GET;
        unset($g["module"]);
        if ($g) {
            unset($g["from_ajax"]);
            unset($g["page"]);
        }
        tpl::o()->assign("get", http_build_query($g));
        tpl::o()->assign("rows", $rows);
        tpl::o()->assign("pages", $pages);
        tpl::o()->display("torrents/search_result.tpl");
    }

}

class search_module_ajax {

    /**
     * Поля сортировки
     * @var array $orderby
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
     * @param array $data массив данных для поиска
     * @return null
     */
    protected function users_results($data) {
        $unco = (bool) $_GET['unco'];
        $parent = (bool) $_GET['parent'];
        $inadmin = users::o()->check_inadmin("users");
        if (!$inadmin) {
            users::o()->check_perms('usearch', 1, 2);
            $unco = false;
        }
        lang::o()->get('search');
        $where = array();
        /* @var $search search */
        $search = n("search");
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
        $registered = display::o()->make_time("reg", "ymd");
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
        $last_visited = display::o()->make_time("lv", "ymd");
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
            plugins::o()->pass_data(array('where' => &$where,
                'orderby' => &$this->orderby), true)->run_hook('search_users');
        } catch (PReturn $e) {
            return $e->r();
        }

        if (!$inadmin && !$where)
            message('nothing_selected');
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
        $count = db::o()->count_rows('users', $where);
        list ( $pages, $limit ) = display::o()->pages($count, config::o()->v('table_perpage'), 'submit_search_form', 'page', '', true);
        $rows = db::o()->query('SELECT id,username,`group`,settings,
            registered,last_visited,torrents_count FROM users
            ' . ($where ? 'WHERE ' . $where : "") . '
            ' . ($orderby ? 'ORDER BY ' . $orderby : "") . '
            ' . ($limit ? 'LIMIT ' . $limit : ""));
        tpl::o()->assign('unco', $unco);
        tpl::o()->assign('rows', db::o()->fetch2array($rows));
        tpl::o()->assign('pages', $pages);
        if ($parent)
            tpl::o()->assign('parented_window', true);
        else
            tpl::o()->assign('parented_window', false);
        tpl::o()->assign('subupdate', (int) $subupdate);
        tpl::o()->display('profile/search_result.tpl');
    }

}

?>