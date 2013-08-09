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

class uploader_checker extends image {

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
     * Разрешить загрузку с URL
     * @var bool $url
     */
    protected $url = false;

    /**
     * Инициализация файловых типов
     * @return null
     */
    public function __construct() {
        if (self::$inited)
            return false;
        self::$file_types = db::o()->cname('filetypes')->ckeys('name', 0)->query("SELECT * FROM allowed_ft");
        lang::o()->get("file");
        self::$inited = true;
    }

    /**
     * Основная проверка файла
     * @param array $file_arr массив $_FILES или URL к файлу
     * @param string $type имя файлового типа
     * @return null
     * @throws EngineException 
     */
    protected function check_main($file_arr, $type) {
        if ($type)
            if (!self::$file_types [$type])
                throw new EngineException('file_no_type');

        if (!is_array($file_arr))
            if ($this->url) {
                if (!preg_match("/^" . display::url_pattern . "$/siu", $file_arr))
                    throw new EngineException('file_no_file_arr');
            }
            else
                throw new EngineException('file_no_file_arr');
    }

    /**
     * Получение типа файла
     * @param string $name имя файла
     * @param string $mime MIME тип файла
     * @return array массив: имя файлового типа, допустимых типов файлов и 
     * допустимых MIME типов файла
     * @throws EngineException
     */
    protected function get_type($name, $mime) {
        $file_type = file::o()->get_filetype($name);
        foreach (self::$file_types as $ftype => $arr) {
            if (!$arr ["allowed"])
                continue;
            if (!$this->url) {
                $mimes = $arr ["MIMES"];
                if ($mimes && !checkpos($mimes, $mime))
                    continue;
            }
            $types = $arr ["types"];
            if (!checkpos($types, $file_type))
                continue;
            $type = $ftype;
            break;
        }
        if (!$type)
            throw new EngineException('file_no_type');
        return array($type, $types, $mimes);
    }

    /**
     * Проверка типа файла
     * @param string $name имя файла
     * @param string $tmp_name путь к временному файлу
     * @param string $type имя файлового типа
     * @param string $file_type тип файла
     * @return null
     * @throws EngineException
     */
    protected function check_type($name, $tmp_name, &$type, &$file_type) {
        $name = mb_strtolower($name);
        $file_type = null;
        if (!$this->url)
            $mime = file::o()->get_content_type($tmp_name);
        if (!$type)
            list($type, $types, $mimes) = $this->get_type($name, $mime);
        else {
            $mimes = self::$file_types [$type] ["MIMES"];
            $types = self::$file_types [$type] ["types"];
        }
        $mimes = explode(";", $mimes);
        $types = explode(";", $types);
        if (!$types)
            throw new EngineException('file_no_type');

        if (!$file_type) {
            if (!preg_match('/^(.+)\.(' . implode("|", array_unique($types)) . ')$/siu', $name, $file_type))
                throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_' . $type));
            $file_type = $file_type [2];
        }
        if (!$this->url && $mime && $mimes && !in_array($mime, $mimes))
            throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_' . $type));
    }

    /**
     * Проверка размера файла
     * @param string $tmp_name путь к временному файлу
     * @param string $type имя файлового типа
     * @return null
     * @throws EngineException
     */
    protected function check_filesize($tmp_name, $type) {
        $maxfilesize = self::$file_types [$type] ["max_filesize"];
        //if (!$this->url) {
        if (@filesize($tmp_name) > $maxfilesize && @filesize($tmp_name))
            throw new EngineException('file_too_big_size');
        //}
    }

    /**
     * Проверка размера изображения
     * @param string $tmp_name путь к временному файлу
     * @param string $type имя файлового типа
     * @param bool|array $minimized массив данных для ресайза
     * @return null
     * @throws EngineException
     */
    protected function check_size($tmp_name, $type, &$minimized) {
        $maxwidth = self::$file_types [$type] ["max_width"];
        $maxheight = self::$file_types [$type] ["max_height"];
        //if ($noturl) {
        //$minimized = false;
        if ($maxwidth || $maxheight || ($minimized && is_array($minimized))) {
            try {
                $wh = $this->is_image($tmp_name);
            } catch (EngineException $e) {
                if ($minimized) {
                    $minimized = false;
                    return;
                }
                else
                    throw $e;
            }
            if ($minimized)
                list($maxwidth, $maxheight) = $minimized;
            $width = $wh [0];
            $height = $wh [1];
            //if (($width > $maxwidth && $maxwidth) || ($height > $maxheight && $maxheight)) {
            //if (!$this->url)
            $minimized = array($maxwidth, $maxheight, $width, $height);
            //}
        }
        //}
    }

    /**
     * Проверка файла
     * @param array|string $file_arr массив $_FILES или URL к файлу
     * @param string $type имя файлового типа
     * @param string $file_type тип файла
     * @param bool|array $minimized массив данных для ресайза(значит не требуется превью), если дан массив, то подгоняется для превью
     * @return string имя файла
     * @throws EngineException 
     */
    public function check($file_arr, &$type = "", &$file_type = null, &$minimized = null) {
        $this->check_main($file_arr, $type);
        if (is_array($file_arr)) {
            $tmp_name = $file_arr ['tmp_name'];
            $name = $file_arr ['name'];
        } else {
            $tmp_name = $file_arr;
            preg_match('/^(.+)\/(.+?)$/siu', $file_arr, $matches);
            $name = $matches [2];
        }
        $this->check_type($name, $tmp_name, $type, $file_type);
        $this->check_filesize($tmp_name, $type);
        $this->check_size($tmp_name, $type, $minimized);
        return $name;
    }

}

class uploader extends uploader_checker {

    /**
     * Ширина превью
     * @var int $preview_width
     */
    protected $preview_width = 0;

    /**
     * Высота превью
     * @var int $preview_height
     */
    protected $preview_height = 0;

    /**
     * Обрезать превью?
     * @var bool $preview_cut
     */
    protected $preview_cut = true;

    /**
     * Делать превью?
     * @var bool $make_preview
     */
    protected $make_preview = false;

    /**
     * Имя превью файла
     * @var string $preview
     */
    protected $preview = "";

    /**
     * Дирректория превью
     * @var string $preview_folder
     */
    protected $preview_folder = "";

    /**
     * Свой постфикс для превью
     * @var string $preview_postfix
     */
    protected $preview_postfix = "";

    /**
     * Всегда создавать превью?
     * @var bool $preview_always
     */
    protected $preview_always = "";

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
     * Разрешение загрузки с URL для одного вызова upload
     * @param bool $state статус
     * @return uploader $this
     */
    public function upload_via_url($state = true) {
        $this->url = (bool) $state;
        return $this;
    }

    /**
     * Создание превью для изображения при загрузке
     * @param string $folder дирректория для превью(полностью, а не отн. загружаемой)
     * @param string $postfix постфикс превью, отличный от коонфига
     * @param bool $always всегда создавать превью?
     * @return uploader $this
     */
    public function upload_preview($folder = "", $postfix = null, $always = false) {
        $this->make_preview = true;
        $this->preview_folder = $folder;
        $this->preview_postfix = $postfix;
        $this->preview_always = $always;
        return $this;
    }

    /**
     * Установка размеров превьюшки
     * @param int $width ширина
     * @param int $height высота
     * @param bool $cut обрезать до размеров превью?
     * @return uploader $this
     */
    public function set_preview_size($width, $height, $cut = true) {
        $this->preview_height = (int) $height;
        $this->preview_width = (int) $width;
        $this->preview_cut = (bool) $cut;
        return $this;
    }

    /**
     * Получение имени превью файла
     * @return string имя
     */
    public function get_preview() {
        return $this->preview;
    }

    /**
     * Загрузка файла
     * @param array|string $file_arr массив $_FILES или URL для загрузки
     * @param string $to_folder путь к дирректории изображений
     * @param string $type имя файлового типа
     * @param string $new_name новое имя файла
     * @param bool $na_type не добавлять тип файла в новое имя
     * @return null
     * @throws EngineException 
     */
    public function upload($file_arr, $to_folder, &$type = "", &$new_name = "", $na_type = false) {
        if (!config::o()->v('makes_preview') || ($type && !self::$file_types [$type] ["makes_preview"])) {
            $this->make_preview = false;
            $minimized = false;
        }
        if ($this->make_preview) {
            if (!$this->preview_height && !$this->preview_width)
                $this->set_preview_size(config::o()->v('preview_width'), config::o()->v('preview_height'));
            $minimized = array($this->preview_width, $this->preview_height);
        }
        $file_type = "";
        $name = $this->check($file_arr, $type, $file_type, $minimized);
        if (!self::$file_types [$type] ["makes_preview"])
            $this->make_preview = false;
        $nt = mb_strtolower("." . $file_type);
        $name = mb_strtolower($name);
        $name = ($new_name ? $new_name : $name);
        $new_name = $name . (!$na_type ? $nt : "");
        $to = $to_folder . "/" . $new_name;
        if ($this->url && !is_array($file_arr)) {
            $r = n("remote");
            $data = $r->send_request($file_arr);
            if (!$data)
                throw new EngineException('file_no_file_arr');
            $status = file::o()->write_file($data, $to);
        } else {
            $tmp_name = $file_arr ['tmp_name'];
            $status = move_uploaded_file($tmp_name, ROOT . $to);
        }
        $this->upload_via_url(false);
        if (!$status)
            throw new EngineException('file_false_copy');
        $filedata = array($to_folder, $name, $nt, $to);
        $this->upload_minimize($minimized, $filedata);
        $this->make_preview = false;
    }

    /**
     * Создание превью для загружаемого изображения
     * @param bool|array $minimized массив данных для ресайза
     * @param array $file массив данных файла(дирректория,имя,тип,загруженное изображение)
     * @return null
     */
    protected function upload_minimize($minimized, $file) {
        list($to_folder, $name, $nt, $to) = $file;
        $preview = "";
        $this->preview = &$preview;
        if (!$minimized || !is_array($minimized))
            return;
        list($maxwidth, $maxheight, $width, $height) = $minimized;
        if ($this->make_preview) {
            $preview_postfix = !is_null($this->preview_postfix) ? $this->preview_postfix : config::o()->v('preview_postfix');
            $preview = $name . $preview_postfix . $nt; // Всегда добавляем тип для превью
            $topr = ($this->preview_folder ? $this->preview_folder : $to_folder) . "/" . $preview;
            try {
                if ($this->preview_always && $width <= $maxwidth && $height <= $maxheight)
                    @copy($to, $topr); // просто копируем
                else
                    $this->resize(array($to, $nt), $maxwidth, $maxheight, $width, $height, $topr, $this->preview_cut);
            } catch (EngineException $e) {
                $preview = '';
            }
        }
        else
            $this->resize(array($to, $nt), $maxwidth, $maxheight, $width, $height, null, $this->preview_cut);
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
            $filename = $matches [2];
        }
        if (!file_exists($filepath))
            throw new EngineException('file_not_exists');
        $content = @file_get_contents($filepath);
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
        @header("Content-Disposition: attachment; filename=\"" . addslashes($filename) . "\"");
        @header("Content-Type: " . ($t ? $t : "application/octet-stream"));
        @ob_implicit_flush(true);
        echo $content;
        die();
    }

}

?>