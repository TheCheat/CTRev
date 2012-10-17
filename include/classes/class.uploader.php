<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.uploader.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Загрузка/скачивание файлов
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class uploader extends image {

    /**
     * Инициализировано?
     * @var bool $inited
     */
    protected static $inited = false;

    /**
     * Массив файловых типов
     * @var array $file_types
     */
    protected static $file_types = array();

    /**
     * Инициализация файловых типов
     * @return null
     */
    public function __construct() {
        if (self::$inited)
            return false;
        self::$file_types = db::o()->query("SELECT * FROM allowed_ft", array('n' => 'filetypes',
            'k' => array('name' => 0)));
        lang::o()->get("file");
        self::$inited = true;
    }

    /**
     * Конвертирование типов файлов(напр. png;jpg => *.png; *.jpg)
     * @param string $file_type тип файла
     * @return string результат
     */
    public function convert_filetypes($file_type) {
        return "*." . str_replace(";", "; *.", self::$file_types [$file_type] ["types"]);
    }

    /**
     * Получение элемента из массива {@link uploader::$file_types}
     * @param string $name ключ
     * @return array элемент 
     */
    public function filetypes($name) {
        return self::$file_types[$name];
    }

    /**
     * Проверка файла
     * @param array $file_arr массив $_FILES или URL к файлу
     * @param string $type имя файлового типа
     * @param string $noturl запрет загрузки из URL
     * @param string $file_type тип файла
     * @param bool|array $minimized массив данных для ресайза(значит не требуется превью), если дан массив, то подгоняется для превью
     * @return null
     * @throws EngineException 
     */
    public function check($file_arr, &$type = "", $noturl = true, &$file_type = null, &$minimized = null) {
        if ($type)
            if (!self::$file_types [$type])
                throw new EngineException('file_no_type');
        if (!is_array($file_arr))
            if (!$noturl) {
                if (!preg_match("/^" . display::url_pattern . "$/", $file_arr))
                    throw new EngineException('file_no_file_arr');
            } else
                throw new EngineException('file_no_file_arr');
        if (is_array($file_arr)) {
            $tmp_name = $file_arr ['tmp_name'];
            $name = $file_arr ['name'];
        } else {
            $tmp_name = $file_arr;
            preg_match('/^(.*)\/(.*)$/siu', $file_arr, $matches);
            $name = $matches [2];
        }
        $name = mb_strtolower($name);
        $file_type = null;
        $auto_type = false;
        if ($noturl)
            $mime = file::o()->get_content_type($tmp_name);
        if (!$type) {
            $file_type = file::o()->get_filetype($name);
            foreach (self::$file_types as $ftype => $arr) {
                if (!$arr ["allowed"])
                    continue;
                if ($noturl) {
                    $mimes = $arr ["MIMES"];
                    if ($mimes) {
                        $mimes = ';' . $mimes . ';';
                        if (mb_strpos($mimes, $mime) === false)
                            continue;
                    }
                }
                $types = ';' . $arr ["types"] . ';';
                if (mb_strpos($types, $file_type) === false)
                    continue;
                $type = $ftype;
                break;
            }
            if (!$type)
                throw new EngineException('file_no_type');
            $auto_type = true;
        }
        if (!$auto_type) {
            $mimes = explode(";", self::$file_types [$type] ["MIMES"]);
            $types = explode(";", self::$file_types [$type] ["types"]);
        }
        if (!$types)
            throw new EngineException('file_no_type');
        $maxfilesize = self::$file_types [$type] ["max_filesize"];
        if ($noturl) {
            if (@fileinclude/classes/size($tmp_name) > $maxfilesize && @fileinclude/classes/size($tmp_name))
                throw new EngineException('file_too_big_size');
        }
        if (is_array($minimized))
            list($maxwidth, $maxheight) = $minimized;
        else {
            $maxwidth = self::$file_types [$type] ["max_width"];
            $maxheight = self::$file_types [$type] ["max_height"];
        }
        //if ($noturl) {
        $minimized = false;
        if ($maxwidth || $maxheight) {
            $wh = $this->is_image($tmp_name);
            $width = $wh [0];
            $height = $wh [1];
            if (($width > $maxwidth && $maxwidth) || ($height > $maxheight && $maxheight)) {
                if ($noturl)
                    $minimized = array($maxwidth, $maxheight, $width, $height);
            }
        }
        //}
        if (!$file_type) {
            if (!preg_match('/^(.+)\.(' . implode("|", array_unique($types)) . ')$/siu', $name, $file_type))
                throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_' . $type));
            $file_type = $file_type [2];
        }
        if ($noturl) {
            if (is_array($mimes) && $mimes)
                if ($mime && !in_array($mime, $mimes))
                    throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_' . $type));
        }
    }

    /**
     * Загрузка файла
     * @param array $file_arr массив $_FILES
     * @param string $to_folder путь к дирректории изображений
     * @param string $type имя файлового типа
     * @param string $new_name новое имя файла
     * @param bool $na_type не добавлять тип файла в новое имя
     * @param bool|string $preview - создание превью к изображению, после - имя файла для превью,
     * в случае успешного создания
     * @return null
     * @throws EngineException 
     */
    public function upload($file_arr, $to_folder, &$type = "", &$new_name = "", $na_type = false, &$preview = false) {
        if ($preview && config::o()->v('makes_preview') && self::$file_types [$type] ["makes_preview"]) {
            if (!$this->preview_height && !$this->preview_width)
                $this->set_preview_size(config::o()->v('preview_width'), config::o()->v('preview_height'));
            $maxwidth = $this->preview_width;
            $maxheight = $this->preview_height;
            $minimized = array($maxwidth, $maxheight);
        } else
            $minimized = false;
        $this->check($file_arr, $type, true, $file_type, $minimized);
        $tmp_name = $file_arr ['tmp_name'];
        $name = mb_strtolower($file_arr ['name']);
        $nt = mb_strtolower(!$na_type ? "." . $file_type : "");
        $new_name = ($new_name ? $new_name . $nt : $name);
        if (!$na_type)
            $name = mb_substr($new_name, 0, mb_strlen($new_name) - mb_strlen($nt));
        $status = move_uploaded_file($tmp_name, ROOT . $to_folder . "/" . $new_name);
        if (!$status)
            throw new EngineException('file_false_copy');
        if ($minimized && is_array($minimized)) {
            list($maxwidth, $maxheight, $width, $height) = $minimized;
            if ($preview && config::o()->v('makes_preview') && self::$file_types [$type] ["makes_preview"]) {
                $preview_postfix = config::o()->v('preview_postfix');
                $preview_name = $name . $preview_postfix . $nt;
                try {
                    $preview = $this->resize($to_folder . "/" . $new_name, $maxwidth, $maxheight, $width, $height, null, $to_folder . "/" . $preview_name);
                    $preview = $preview_name;
                } catch (EngineException $e) {
                    $preview = '';
                }
                if (!$preview)
                    $preview = $new_name;
            } else
                $this->resize($to_folder . "/" . $new_name, $maxwidth, $maxheight, $width, $height);
        } else
            $preview = '';
    }

    /**
     * Функция скачивания файла, защищенного .htaccess
     * @param string $filepath путь к файлу
     * @param string $filename имя файла
     * @return bool true, если всё прошло успешно, false - в случае неудачи
     * @throws EngineException 
     */
    public function download($filepath, $filename = "") {
        if (!$filename) {
            preg_match('/\/(.*)$/siu', $filepath, $matches);
            $filename = urlencode($matches [2]);
        } else
            $filename = urlencode($filename);
        if (!file_exists($filepath))
            throw new EngineException('file_not_exists');
        $content = @fileinclude/classes/_get_contents($filepath);
        $t = file::o()->get_content_type($filepath);
        $this->download_headers($content, $filename, $t);
    }

    /**
     * Заголовки для скачивания файла
     * @param string $content контент файла
     * @param string $filename имя файла
     * @param string $t тип файла
     * @return null 
     */
    public function download_headers($content, $filename, $t = null) {
        @ob_clean();
        @header("Expires: Tue, 1 Jan 1980 00:00:00 GMT");
        @header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        @header("Cache-Control: no-store, no-cache, must-revalidate");
        @header("Cache-Control: post-check=0, pre-check=0", false);
        @header("X-Powered-by: CTRev: A bit of (R)evolution(http://ctrev.cyber-tm.ru)");
        @header("Pragma: no-cache");
        @header("Accept-Ranges: bytes");
        @header("Connection: close");
        @header("Content-Transfer-Encoding: binary");
        @header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        @header("Content-Type: " . ($t ? $t : "application/octet-stream"));
        @ob_implicit_flush(true);
        echo $content;
        die();
    }

}

?>