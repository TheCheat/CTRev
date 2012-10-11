<?php

/**
 * Project:            	CTRev
 * File:                class.categories.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Функции для категорий
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class categories {

    /**
     * Массив категорий
     * @var array $c
     */
    protected static $c = null;

    /**
     * Данный тип категорий
     * @var type $curtype 
     */
    protected $curtype = "torrents";

    /**
     * Конструктор категорий
     * @return null
     */
    public function __construct() {
        if (!self::$c && (self::$c = cache::o()->read('categories')) === false) {
            self::cats2array();
            cache::o()->write(self::$c);
        }
        tpl::o()->register_modifier("print_cats", array(
            $this,
            'print_selected'));
    }

    /**
     * Преобразование списка категорий в массив
     * @param int $pid ID родителя
     * @param string $type тип категории
     * @return array массив категорий данного родителя
     */
    protected static function cats2array($pid = 0, $type = 'torrents') {
        $r = db::o()->query('SELECT * FROM categories 
            WHERE ' . ($pid ? 'type=' . db::o()->esc($type) . ' AND' : "") . ' 
                parent_id=' . $pid);
        $ret = array();
        while ($row = db::o()->fetch_assoc($r)) {
            $type = $row['type'];
            $id = $row['id'];
            unset($row['type']);
            //unset($row['id']);
            unset($row['parent_id']);
            self::$c['e'][$type][$id] = $row; // данные категории с ID $id
            if (!$pid)
                self::$c['t'][$type][] = &self::$c['e'][$type][$id]; // массив категорий верхнего уровня
            self::$c['n'][$type][$row['transl_name']] = &self::$c['e'][$type][$id]; // для поиска категории по имени
            if (self::$c['e'][$type][$pid])
                self::$c['p'][$type][$id] = &self::$c['e'][$type][$pid]; // родитель, если есть
            $a = self::cats2array($id, $type);
            if ($a)
                self::$c['c'][$type][$id] = $a; // дети, если есть
            if ($pid)
                $ret[] = &self::$c['e'][$type][$id]; // записываем для детей, если нужно
        }
        return $ret;
    }

    /**
     * Получение категории/родителя/детей
     * @param int|string $id ID категории или транслитерованное имя
     * @param string $type получаем категорию(e), 
     * категории без PID(t), типы(z), родителя(p) или детей(c)
     * @return array массив данных категории(ий) 
     */
    public function get($id = null, $type = 'e') {
        $e = !is_numeric($id) ? 'n' : 'e';
        switch ($type) {
            case 'e':
                $type = $e;
            case 'p':
            case 't':
            case 'c':
                break;
            case 'z':
                return array_keys(self::$c['e']);
            default:
                $type = $e;
                break;
        }
        return $type == 't' ? self::$c[$type][$this->curtype] : self::$c[$type][$this->curtype][$id];
    }

    /**
     * Изменение типа категорий
     * @param string $type имя типа
     * @return categories $this
     */
    public function change_type($type) {
        if (!self::$c['e'][$type])
            return $this;
        $this->curtype = $type;
        return $this;
    }

    /**
     * Создание условия для выборки всего из данной категории и всех подкатегорий
     * @param integer|string $cur имя или ID данной категории
     * @param array $cat_row массив верхней категории
     * @return string условие
     */
    public function condition($cur, &$cat_row = null) {
        if (!$cur)
            return null;
        if (is_numeric($cur))
            $cat = (int) $cur;
        else {
            $cat = mb_strtolower(display::o()->strip_subpath($cur));
            if (preg_match('/^(.*?)\//siu', $cat, $matches))
                $cat = $matches[1];
        }
        $c = $this->get($cat);
        $cat_row = array(
            $c ['name'],
            $c ['descr'],
            $c ['transl_name'],
            $c ['id']);
        $ids = array();
        $this->get_children_ids($c ['id'], $ids);
        $ids[] = $c['id'];
        $where = $this->cat_where(implode('|', $ids), true);
        return $where;
    }

    /**
     * Получение ID всех детей, и детей их детей, и детей их детей, и ...
     * @param int $id ID данной категории
     * @param array $ids уже полученные ID'ы
     * @return null
     */
    public function get_children_ids($id, &$ids = array()) {
        $child = $this->get($id, 'c');
        $c = count($child);
        if (!$c)
            return;
        for ($i = 0; $i < $c; $i++) {
            $ids[] = $child[$i]['id'];
            $this->get_children_ids($child[$i]['id'], $ids);
        }
    }

    /**
     * Создание условия для категории
     * @param int $id ID категории
     * @param bool $no_int не преобразовывать ID в целое число
     * @return string условие
     */
    public function cat_where($id, $no_int = false) {
        return '`category_id`
            ' . ($no_int ? "R" : "") . 'LIKE "' . (!$no_int ? "%" : "") . ',' . ($no_int ? "(" . $id . ")" : (int) $id) . ',' . (!$no_int ? "%" : "") . '"';
    }

    /**
     * Получение массива категорий
     * @param string $category_id ID категорий
     * @param int $level уровень категорий(1 - верхний, 0 - все)
     * @return array массив категорий, 1-ый элемент - массив данных категорий, а 0, если указан,
     * массив родителей категории, по-порядку, в которых ключ - transl_name, значение - название категории,
     */
    public function cid2arr($category_id, $level = 1) {
        $cats = array_map("intval", explode(",", trim($category_id, ",")));
        if (!$cats)
            return;
        $categories = array();
        $c = count($cats);
        for ($i = 0; $i < $c; $i++)
            $categories[] = $this->get($cats[$i]);
        $parents = array();
        if ($level == 0) {
            $id = $cats[0];
            while ($parent = $this->get($id, 'p')) {
                $id = $parent['id'];
                $parents[$parent['transl_name']] = $parent['name'];
            }
            $parents = array_reverse($parents);
        }
        return array($parents, $categories);
    }

    /**
     * Получение полного списка родителей с выделением ID
     * @param int $id данный ID
     * @return array массив, первый элемент - массив категорий по уровням, 
     * второй - выделяемые ID'ы
     */
    public function get_parents($id) {
        $categories = array();
        $ids = array();
        while ($parent = $this->get($id, 'p')) {
            $categories[] = $this->get($parent['id'], 'c');
            $ids[] = $id;
            $id = $parent['id'];
        }
        $categories[] = $this->get(null, 't');
        $ids[] = $id;
        $categories = array_reverse($categories);
        $ids = array_reverse($ids);
        return array($categories, $ids);
    }

    /**
     * AJAX селектор категорий
     * @param string $cat ID'ы категорий или имя категории
     * @return string HTML код селектора
     */
    public function ajax_selector($cat = null) {
        if ($cat && $cat[0] == ',') {
            $row_cats = explode(',', trim($cat, ","));
            $cat = $row_cats [0];
            tpl::o()->assign('row_cats', $row_cats);
        } elseif ($cat) {
            $cat = $this->get($cat);
            $cat = $cat['id'];
        }
        if (!$cat)
            tpl::o()->assign("cats", $this->get(null, 't'));
        else {
            $categories = $this->get_parents($cat);
            tpl::o()->assign("categories", $categories[0]);
            tpl::o()->assign("cids", $categories[1]);
        }
        tpl::o()->assign("cattype", $this->curtype);
        $r = tpl::o()->fetch('categories.tpl');
        return $r;
    }

    /**
     * Сохранение выбранных категорий
     * @param array $cats массив всех категорий
     * @return string ID выбранных категорий через запятую
     */
    public function save_selected(&$cats) {
        if (!$cats || !is_array($cats))
            return;
        $cats = end($cats);
        if (!$cats || !is_array($cats))
            return;
        foreach ($cats as $cat) {
            $cat = $this->get($cat);
            if (!$cat || !$cat['post_allow'])
                return;
        }
        return ',' . implode(',', $cats) . ',';
    }

    /**
     * Вывод категорий с родителями
     * @param array $cat_arr массив данных категорий
     * @param array $parents массив родителей вида transl_name=>name
     * @param string $type тип категорий
     * @return string HTML код
     */
    public function print_selected($cat_arr, $parents = null, $type = "torrents") {
        if (!$cat_arr)
            return;
        $this->change_type($type);
        $type = $this->curtype;
        $html = '';
        if ($parents) {
            $html = '<b>&nbsp;&raquo;&nbsp;</b>';
            foreach ($parents as $trcat => $cat)
                $html .= '<a href="' . furl::o()->construct($type, array(
                            'cat' => $trcat)) . '">' . $cat . '</a><b>&nbsp;&raquo;&nbsp;</b>';
        }
        $b = false;
        foreach ($cat_arr as $cat) {
            $html .= ($b ? ",&nbsp;" : "") . '<a href="' . furl::o()->construct($type, array(
                        'cat' => $cat['transl_name'])) . '">' . $cat['name'] . '</a>';
            $b = true;
        }
        return $html;
    }

}

?>