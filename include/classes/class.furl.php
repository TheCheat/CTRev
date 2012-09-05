<?php

/**
 * Project:            	CTRev
 * File:                class.furl.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		ЧПУ
 * @version           	1.00
 * @tutorial            Конструировать надо так, чтобы при отключении ЧПУ работало :)
 * когда ЧПУ отключается, то выдаётся строка типа $param1=$value1&$param2=$value2, etc.
 * Безусловно, можно создать функцию вида {$module}_nfurl_rules, но зачем?
 */
if (!defined('INSITE'))
    die('Remote access denied!');

final class furl extends pluginable_object {

    /**
     * Запретить переадресации?
     * @var bool
     */
    private $denied_locations = false;

    /**
     * Правила правильного расположения параметров
     * @var array
     */
    protected $resort = array(
        'news' => array(
            'id',
            'act'),
        'polls' => array(
            'id',
            'act'),
        'search' => array(
            'auto',
            'tag',
            'author',
            'query',
            'act',
            'user',
            'email'),
        'login' => array(
            'act',
            'key',
            'email',
            'ref'),
        'torrents' => array(
            'year',
            'month',
            'day',
            'cat',
            'title',
            'act',
            'id',
            'cid',
            //'attr',
            'page',
            'comments_page'),
        'users' => array(
            "title",
            "user",
            "act",
            'cid'),
        'pm' => array(
            "to",
            "act",
            "id"));

    /**
     * Постфиксы в ЧПУ
     * @var array
     */
    protected $postfixes = array(
        'torrents' => array(
            'cid' => '#comment_'),
        'users' => array(
            'cid' => '#comment_'));

    /**
     * Замена для модулей при отключенном ЧПУ
     * @var array
     */
    protected $rmodules = array(
        'polls' => 'polls_manage',
        'search' => 'search_module',
        'users' => 'user',
        'pm' => 'messages',
        'static' => 'statics',
        'download' => 'torrents');

    /**
     * Проверка для метода location
     * @var array
     */
    private $forlocation = array();

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->access_var('postfixes', PVAR_ADD | PVAR_MOD);
        $this->access_var('resort', PVAR_ADD | PVAR_MOD);
        $this->access_var('rmodules', PVAR_ADD);
    }

    /**
     * Включить/Выключить запрет переадресации
     * @return furl $this
     */
    public function deny_locations() {
        $this->denied_locations = !$this->denied_locations;
        return $this;
    }

    /**
     * Метод обработки параметров для аннонсера
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function announce_furl_rules($param, $value) {
        switch ($param) {
            case "passkey":
                return "p" . $value;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ новостей
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function news_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
            case "id" :
                return "id" . longval($value) . "-";
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ опросов
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function polls_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
            case "id" :
                return "id" . ($value == '$1' ? $value : longval($value)) . "-";
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ вложений
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function download_furl_rules($param, $value) {
        switch ($param) {
            case "id" :
                return "id" . longval($value);
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для неЧПУ вложений
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @param bool $amp экранировать "&"?
     * @return string часть не-ЧПУ
     */
    protected function download_nfurl_rules($param, $value, $amp = false) {
        switch ($param) {
            case "id" :
                return "act=download" . ($amp ? "&amp;" : "&") . "id=" . longval($value);
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ поиска
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function search_furl_rules($param, $value) {
        switch ($param) {
            case "auto" :
                return "auto-";
                break;
            case "act" :
                return $value;
                break;
            case "author" :
            case "query" :
            case "user" :
            case "tag":
            case "email" :
                return $param . "-" . $value;
                break;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ подтверждения
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function confirm_furl_rules($param, $value) {
        switch ($param) {
            case "key" :
                return "key-" . $value;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ ЛС
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function pm_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
                break;
            case "to" :
                return $value . '/';
                break;
            case "id" :
                return "-id" . longval($value);
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ регистрации
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function registration_furl_rules($param, $value) {
        switch ($param) {
            case "ckey":
                return "ckey-" . $value;
            case "act" :
                return $value;
                break;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ панели управления
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function usercp_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
                break;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для не-ЧПУ пользователя
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть не-URL
     */
    protected function users_nfurl_rules($param, $value) {
        switch ($param) {
            case "id":
                break;
            case "title" :
                $param = 'user';
            default :
                return $param . '=' . $value;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ пользователя
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function users_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
                break;
            case "title" :
            case "user" :
                return urlencode($value) . "/";
                break;
            case "cid" :
                return "cid" . longval($value);
                break;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ стат. страниц
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function static_furl_rules($param, $value) {
        switch ($param) {
            case "page" :
                return $value;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ логина
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function login_furl_rules($param, $value) {
        switch ($param) {
            case "act" :
                return $value;
            case "key" :
                return "-" . $value;
            case "email" :
                return "/email-" . $value;
            case "ref" :
                return 'ref-' . urlencode(urlencode($value));
                break;
            default :
                return;
                break;
        }
    }

    /**
     * Метод обработки параметров для не-ЧПУ логина
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть не-ЧПУ
     */
    protected function login_nfurl_rules($param, $value) {
        switch ($param) {
            case "ref" :
                $value = urlencode(urlencode($value));
            default:
                return $param . '=' . $value;
                break;
        }
    }

    /**
     * Метод обработки параметров для не-ЧПУ торрентов
     * @global display $display
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть не-URL
     */
    protected function torrents_nfurl_rules($param, $value) {
        global $display;
        switch ($param) {
            case "title":
                $value = $display->translite($value, 100);
            default:
                return $param . '=' . $value;
                break;
        }
    }

    /**
     * Метод обработки параметров для ЧПУ торрентов
     * @global display $display
     * @param string $param имя параметра
     * @param mixed $value значение параметра
     * @return string часть ЧПУ
     */
    protected function torrents_furl_rules($param, $value) {
        global $display;
        switch ($param) {
            //case "attr" :
            //    return $value;
            case "year" :
            case "month" :
            case "day" :
                return (preg_match('/^\$([1-3])$/siu', $value) ? $value : longval($value)) . '/';
            case "act" :
                return $value;
            case "id" :
                return "-id" . longval($value);
                break;
            case "cid" :
                return "-cid" . longval($value);
                break;
            case "title" :
                return $display->translite($value, 100);
                break;
            case "cat" :
                return $value . "/";
            case "comments_page":
            case "page":
                return "page" . $value;
            default :
                return;
                break;
        }
    }

    /**
     * Функция сортировки параметров
     * @param string $module имя модуля
     * @param array $params парамтеры
     * @return array отсортированные параметры
     */
    protected function resort($module, $params) {
        if ($this->resort [$module]) {
            foreach ($this->resort [$module] as $key) {
                if (isset($params [$key]))
                    $newparams [$key] = $params [$key];
            }
            return $newparams;
        } else {
            return $params;
        }
    }

    /**
     * Функция создания Человекопонятного URL, исходя из заданных параметров, 
     * по предустановленным правилам
     * @global string $BASEURL
     * @global config $config
     * @param string $module имя модуля
     * @param array $params массив параметров, например:
     * array('id' => 1, 'name' => 'CTRev', 'cat' => 'demo')
     * ключ slashes экранирует результат для JavaScript, иначе & заменяется на &amp;
     * @param bool $page является ли указанный модуль ссылкой на документ?
     * @param bool $no_end нужно ли в конец добавлять .html/index.html?
     * @param bool $nobaseurl не добавлять в начала $BASEURL
     * @return string ЧПУ
     */
    public function construct($module, $params = array(), $page = false, $no_end = false, $nobaseurl = false) {
        global $BASEURL, $config;
        $burl = true;
        if (is_array($module)) {
            $module_t = $module ['module'];
            if ($module ['no_end']) {
                $no_end = $module ['no_end'];
                //unset ( $module ['no_end'] );
            }
            if (!$module_t && $module ['page']) {
                $module = $module ['page'];
                $page = true;
                //unset ( $module ['page'] );
            } elseif (!$module_t)
                return;
            else {
                unset($module ['module']);
                $params = $module;
                $module = $module_t;
            }
        } elseif ($nobaseurl) {
            $burl = false;
        }/*
          if (!is_array($params) && $params)
          $params = (array) $display->parse_smarty_array($params);
          else */
        if (!is_array($params))
            $params = array();
        if ($params ["_filetype"])
            $filetype = (strpos($params ["_filetype"], ".") === 0 ? $params ["_filetype"] : "." . $params ["_filetype"]);
        else
            $filetype = ".html";
        $url = ($burl ? $BASEURL : "");
        if ($config->v('furl')) {
            $url .= $module . ($page ? '' : "/");
            $function = $module . '_furl_rules';
        } else {
            $url = 'index.php?module=' . ($this->rmodules[$module] ? $this->rmodules[$module] : $module);
            $function = $module . '_nfurl_rules';
        }
        $b = $this->is_callable($function);
        if ($b)
            $params = $this->resort($module, $params);
        $postfix = "";
        $slashes = $params ['slashes'];
        $noencode = $params ['noencode'];
        $surl = '';
        $ourl = $url;
        if ($params && !$page) {
            foreach ($params as $param => $value) {
                if ($this->postfixes[$module] [$param])
                    $postfix .= $this->postfixes[$module] [$param] . $value;
                if ($b)
                    $r = $this->call_method($function, array($param, $value, !$slashes && !$noencode));
                elseif (!$config->v('furl'))
                    $r = $param . "=" . $value;
                else
                    $r = $param . "-" . $value . "/";
                if (!$config->v('furl') && $r) {
                    $surl .= '&' . $r;
                    $r = ($slashes || $noencode ? '&' : '&amp;') . $r;
                }
                $url .= $r;
            }
        }
        if (!$surl)
            $surl = $url;
        else
            $surl = $ourl . $surl;
        $add = '';
        if ($config->v('furl')) {
            if (!$no_end && !$page && (!$params || !$b || $url [mb_strlen($url) - 1] == '/'))
                $add .= "index";
            if (!$no_end)
                $add .= $filetype;
        }
        $add .= $postfix;
        $url = $url . $add;
        $surl = $surl . $add;
        $this->forlocation = array($surl, $url);
        if ($slashes)
            $url = slashes_smarty($url);
        return $url;
    }

    /**
     * Функция переадресации
     * @global string $BASEURL
     * @param string $url URL переадресации
     * @param int $time время переадресации
     * @param bool $no_clean не очищать экран и не выполнять функцию die()?
     * @return null
     */
    public function location($url, $time = 0, $no_clean = false) {
        global $BASEURL;
        if ($this->denied_locations)
            return;
        if ($this->forlocation && $this->forlocation[1] == $url)
            $url = $this->forlocation[0];
        if ($time)
            $no_clean = true;
        if ($no_clean)
            $contents = ob_get_contents();
        ob_end_clean();
        if (!preg_match("/^http\\:\\/\\/(.*?)$/siu", $url))
            $url = $BASEURL . $url;
        if (!$time)
            @header("Location: " . $url);
        else
            @header("Refresh: " . $time . ", url=" . $url);
        if (!$no_clean)
            die();
        else
            print ($contents);
    }

}

?>