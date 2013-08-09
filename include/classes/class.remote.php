<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.remote.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name		Класс для получения данных с удалённого сервера
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class remote {

    /**
     * Ограничение на запрос по времени в секундах
     * По-умолчанию DEFAULT_SOCKET_TIMEOUT
     * @var int $time_limit
     */
    protected $time_limit = 0;

    /**
     * HTTP User-agent
     * @var string $ua
     */
    protected $ua = "Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14";

    /**
     * Функция обработки запроса
     * @var callback $func
     */
    protected $func = "";

    /**
     * Принудительно получать этим методом
     * @var string $force
     */
    protected $force = "";

    /**
     * Конструктор. Настройка параметров
     * @param callback $func функция обработки запроса
     * (первый параметр - запрос, второй - метод отправки file, socket или curl)
     * @param string $force принудительно получать этим методов(file, socket или curl)
     * @param string $ua HTTP User-agent
     * @param int $time_limit ограничение времени запроса
     * @return null
     */
    public function __construct($func = "", $force = "", $ua = "", $time_limit = 0) {
        $this->func = $func;
        $this->force = $force;
        if ($ua)
            $this->ua = $ua;
        if ($time_limit)
            $this->time_limit = $time_limit;
        if (!$this->time_limit)
            $this->time_limit = DEFAULT_SOCKET_TIMEOUT;
    }

    /**
     * Вызов функции
     * @param array $data данные
     * @param string $type тип
     * @return mixed ответ
     */
    protected function call_func(&$data, $type) {
        $f = $this->func;
        if (!is_callable($f))
            return;
        if (is_array($f)) {
            list($obj, $func) = $f;
            return $obj->$func($data, $type);
        }
        return $f($data, $type);
    }

    /**
     * Получение контента страницы посредством сокетов
     * @param string $url URL страницы
     * @param string|array $post массив данных POST
     * @return string контент страницы
     */
    protected function content_via_sockets($url, $post = array()) {
        $p = parse_url($url);
        $host = $p['host'];
        $path = $p['path'];
        if (!$path)
            $path = "/";
        $port = $p['port'];
        if (!$port) {
            switch ($p['scheme']) {
                case "https":
                    $port = 443;
                    $host = "ssl://" . $host;
                    break;
                case "udp":
                    $port = 13;
                    $host = "udp://" . $host;
                    break;
                default:
                    $port = 80;
                    break;
            }
        }
        $query = $p['query'];
        if ($post && is_array($post))
            $post = http_build_query($post);
        $r = @fsockopen($host, $port, $errno, $errstr, $this->time_limit);
        if (!$r)
            return;
        $query = ($post ? "POST" : "GET" ) . " " . $path . "?" . $query;
        $out = array();
        $out["Protocol"] = "HTTP/1.1";
        $out["Host"] = $host;
        $out["User-Agent"] = $this->ua;
        if ($post) {
            $out["Content-Type"] = 'application/x-www-form-urlencoded';
            $out["Content-Length"] = strlen($post);
        }
        $out["Connection"] = "Close\r\n";
        $this->call_func($out, "socket");

        $query .= $out["Protocol"];
        unset($out["Protocol"]);
        foreach ($out as $param => $value)
            $query .= $param . ": " . $value . "\r\n";

        if ($post)
            $query .= $post;
        if (!@fwrite($r, $out))
            return; // для UDP
        $c = '';
        while (!feof($r))
            $c .= fgets($r, 1024);
        fclose($r);
        return $this->parse_headers($c);
    }

    /**
     * Обрезание заголовков и парсинг чанков
     * @param string $c контент с заголовками
     * @return string спарсенный контент
     */
    protected function parse_headers($c) {
        $hend = "\r\n\r\n";
        $p = strpos($c, $hend);
        $h = substr($c, 0, $p);
        $c = trim(substr($c, $p + strlen($hend)));
        if (preg_match('/Transfer-Encoding\:\s*chunked\r\n/siu', $h)) {
            $r = '';
            $nl = "\r\n";
            $nll = strlen($nl);
            do {
                $p = strpos($c, $nl);
                $clen = hexdec(substr($c, 0, $p));
                $p += $nll;
                $r .= substr($c, $p, $clen);
                $c = substr($c, $p + $clen + 2); // + 2 ибо перевод на новую строку
                if (!$clen)
                    break;
            } while ($c);
            $c = $r;
        }
        return $c;
    }

    /**
     * Получение контента через CURL
     * @param string $url URL страницы
     * @param string|array $post массив данных POST
     * @return string контент
     */
    protected function content_via_curl($url, $post = array()) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->time_limit);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->time_limit);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $this->call_func($ch, "curl");

        @$r = curl_exec($ch);

        curl_close($ch);

        return $r;
    }

    /**
     * Получение контента через file_get_contents
     * @param string $url URL страницы
     * @param string|array $post массив данных POST
     * @return string контент
     */
    protected function content_via_file($url, $post = array()) {
        $params = array(
            "http" => array("user_agent" => $this->ua,
                "timeout" => $this->time_limit));
        if ($post)
            $params["http"] = array_merge($params["http"], array("method" => "POST",
                'header' => 'Content-type: application/x-www-form-urlencoded',
                "content" => http_build_query($post)));
        $this->call_func($params, "file");

        $context = stream_context_create($params);
        return @file_get_contents($url, null, $context);
    }

    /**
     * Посылка запроса по URL и получение результата
     * @param string $url URL страницы
     * @param string|array $post массив данных POST
     * @return string контент
     */
    public function send_request($url, $post = array()) {
        if ($this->force == 'file')
            $r = $this->content_via_file($url, $post);
        elseif (function_exists("curl_init") && $this->force != 'socket')
            $r = $this->content_via_curl($url, $post);
        else
            $r = $this->content_via_sockets($url, $post);

        return $r;
    }

}

?>