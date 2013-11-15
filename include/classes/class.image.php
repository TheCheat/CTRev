<?php

/**
 * Project:            	CTRev
 * @file                include/classes/class.image.php
 *
 * @page 	  	http://ctrev.cyber-tm.ru/
 * @copyright 	        (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @name           	Методы обработки изображений
 * @version   	        1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class image {

    /**
     * Стандартный размер букв
     * @var int $size_def
     */
    protected $size_def = 23;

    /**
     * Максимальный размер букв
     * @var int $size_max
     */
    protected $size_max = 23;

    /**
     * Минимальный размер букв
     * @var int $size_min
     */
    protected $size_min = 15;

    /**
     * Минимальный цвет букв
     * @var color $color_from
     */
    protected $color_from = 0x000000;

    /**
     * Максимальный цвет букв
     * @var color $color_to
     */
    protected $color_to = 0x333333;

    /**
     * Данные шрифта
     * @var array $shrift_data
     */
    protected $shrift_data = null;

    /**
     * Позиции цветов, ближе к белому(вида rgb)
     * @var array $white_colors
     */
    protected $white_colors = array(020, 021, 022, 111, 112, 120, 121, 122, 201, 202, 210, 211, 212, 220, 221, 222);

    /**
     * Функция для тестирования цветов
     * @return null
     */
    public function test_colors() {
        for ($r = 0; $r < 3; $r++) {
            for ($g = 0; $g < 3; $g++) {
                for ($b = 0; $b < 3; $b++) {
                    $color = $this->rgb2hex($r * 128, $g * 128, $b * 128);
                    print($r . $g . $b . ": <span style='background-color:" . $color . ";padding:0 5px;'><b>BLACK</b></span>
                <span style='background-color:" . $color . ";padding:0 5px;color:white;'><b>WHITE</b></span>
                <br>");
                }
            }
        }
    }

    /**
     * Установка пределов цвета
     * @param int $color_form начальный цвет
     * @param int $color_to конечный цвет
     * @return image $this
     */
    public function set_random_color($color_form, $color_to) {
        $this->color_from = (int) $color_form;
        $this->color_to = (int) $color_to;
        return $this;
    }

    /**
     * Установка пределов размера букв
     * @param int $size_def стандартный размер
     * @param int $size_min мин. размер
     * @param int $size_max макс. размер
     * @return image $this
     */
    public function set_random_size($size_def, $size_min = null, $size_max = null) {
        $this->size_def = (int) $size_def;
        if ($size_max)
            $this->size_max = (int) $size_max;
        if ($size_min)
            $this->size_min = (int) $size_min;
        return $this;
    }

    /**
     * Проверка, является ли изображением файл по данному адресу
     * @param string $url URL файла
     * @param bool $ierr ошибка для класса image
     * @return array размеры изображения
     * @throws EngineException 
     */
    public function is_image($url, $ierr = false) {
        $wh = @getimagesize($url);
        if (!$wh)
            throw new EngineException($ierr ? "file_cannt_get_sizes_of_file" : 'file_not_image');
        return $wh;
    }

    /**
     * Преобразование RGB в HEX цвет и инвертирование цвета
     * @param int $r красный цвет
     * @param int $g зелёный цвет
     * @param int $b синий цвет
     * @return int HEX цвет
     */
    public function rgb2hex($r, $g, $b) {
        if (is_array($r) && sizeof($r) == 3)
            list ( $r, $g, $b ) = $r;

        $r = longval($r);
        $g = longval($g);
        $b = longval($b);

        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '') . $r;
        $color .= ( strlen($g) < 2 ? '0' : '') . $g;
        $color .= ( strlen($b) < 2 ? '0' : '') . $b;
        return '0x' . $color;
    }

    /**
     * Вычисление положения текста
     * @param resource $img ресурс изображения
     * @param string $text накладываемый текст
     * @param string $pos позиция, 2 символа(1-й l|r|c - лево,право,центр, 2-й t|b|c - верх,низ,центр)
     * @param bool $random_size случайный размер букв
     * @return array массив координат (x,y) положения текста и массива размеров ватермарка
     */
    protected function watermark_pos(&$img, $text, $pos, $random_size = false) {

        list($shrift_weight, $shrift_rotate, $shrift_template) = $this->shrift_data;
        if (!preg_match("/^[lrc][tbc]$/siu", $pos))
            $pos = "rb";

        $pos1 = $pos [0];
        $pos2 = $pos [1];
        $WIDTH = imagesx($img);
        $HEIGHT = imagesy($img);
        $bbox = imagettfbbox(($random_size ? $this->size_max : $shrift_weight), $shrift_rotate, $shrift_template, $text);
        if ($pos1 == "r")
            $watermark_x = $WIDTH - ($bbox [2] - $bbox [0]) - 5;
        elseif ($pos1 == "c")
            $watermark_x = ($WIDTH - ($bbox [2] - $bbox [0])) / 2;
        else
            $watermark_x = 5;
        if ($pos2 == "t")
            $watermark_y = ($bbox [1] - $bbox [7]);
        elseif ($pos2 == "c")
            $watermark_y = ($HEIGHT + ($bbox [1] - $bbox [7]) / 2) / 2;
        else
            $watermark_y = $HEIGHT - ($bbox [1] - $bbox [7]) / 4;
        return array($watermark_x, $watermark_y, $bbox);
    }

    /**
     * Вычисление положения цвета
     * @param int $color цвет в RGB
     * @return int его положение
     */
    protected function position_color($color) {
        // если цвет ближе к 0, то 0
        if ($color <= 64)
            return 0;
        // если ближе к 256, то 2
        elseif ($color >= 192)
            return 2;
        // если ближе к 128, то 1
        else
            return 1;
    }

    /**
     * Подборка наилучшего цвета для отображения на данном фоне
     * @param string $color цвет
     * @param resource $img ресурс изображения
     * @param int $watermark_x положение ватермарка по x
     * @param int $watermark_y положение ватермарка по y
     * @param array $bbox массив размеров ватермарка
     * @return null
     */
    protected function parse_color(&$color, &$img, $watermark_x, $watermark_y, $bbox) {
        if ($color == "auto" || !preg_match('/^([0-9A-fa-f]){6}$/siu', $color)) {
            // берём из середины текста
            $watermark_x += ($bbox[2] - $bbox[0]) / 2;
            $watermark_y += ($bbox[3] - $bbox[1]) / 2;
            $totalcolor = imagecolorat($img, $watermark_x, $watermark_y);
            $rgb = imagecolorsforindex($img, $totalcolor);
            $r = $this->position_color($rgb ['red']);
            $g = $this->position_color($rgb ['green']);
            $b = $this->position_color($rgb ['blue']);
            if (in_array($r . $g . $b, $this->white_colors))
                $color = '0x000000'; // чёрный
            else
                $color = '0xFFFFFF'; // белый
        }
        else
            $color = '0x' . $color;
    }

    /**
     * Метода наложения текста на изображение
     * @param string|array $image_path путь к изображению
     * если массив, то первый элемент - путь, второй - с типом изображения
     * @param string $text накладываемый текст
     * @param string $color цвет изображения
     * @param bool $rewrite перезаписывать ли изображение, иначе - выводится на экран
     * @param string $font путь к шрифту
     * @param string $pos позиция, 2 символа(1-й l|r|c - лево,право,центр, 2-й t|b|c - верх,низ,центр)
     * @param bool $random_color случайный цвет букв
     * @param bool $random_size случайный размер букв
     * @return image|null
     * @throws EngineException 
     */
    public function watermark($image_path, $text, $color = 'auto', $rewrite = false, $font = "upload/fonts/watermark.ttf", $pos = "rb", $random_color = false, $random_size = false) {
        lang::o()->get('file');
        $font = ($font ? $font : "upload/fonts/watermark.ttf");
        if (is_array($image_path)) {
            list($image_path, $type) = $image_path;
            $type = file::o()->get_filetype($type);
        }
        else
            $type = file::o()->get_filetype($image_path);
        if (!$text || !$image_path)
            return;
        if ($type == 'jpg')
            $type = 'jpeg';
        if (!$type)
            throw new EngineException("file_false_name");
        $image_path = ROOT . $image_path;

        $shrift_weight = $this->size_def;
        $shrift_rotate = 0;
        $shrift_template = ROOT . $font;
        $this->shrift_data = array($shrift_weight, $shrift_rotate, $shrift_template);

        $template_file = $image_path;
        if (!file_exists($shrift_template))
            throw new EngineException("file_cannt_load_shrift");
        $str = 'imagecreatefrom' . $type;
        $img = $str($template_file);
        if (!$img)
            throw new EngineException("file_cannt_load_gd");
        //$img = imagecreatetruecolor ( 300, 60 );
        try {
            plugins::o()->pass_data(array('img' => &$img,
                'text' => &$text,
                'args' => func_get_args()), true)->run_hook('watermark_begin');

            list($watermark_x, $watermark_y, $bbox) = $this->watermark_pos($img, $text, $pos, $random_size);

            if (!$random_color)
                $this->parse_color($color, $img, $watermark_x, $watermark_y, $bbox);

            if (!$random_color && !$random_size)
                imagettftext($img, $shrift_weight, $shrift_rotate, $watermark_x, $watermark_y, $color, $shrift_template, $text);
            else {
                for ($i = 0; $i < strlen($text); $i++) {
                    if ($random_size)
                        $shrift_weight_2 = rand($this->size_min, $this->size_max);
                    $coords = imagettftext($img, (!$random_size ? $shrift_weight : $shrift_weight_2), $shrift_rotate, $watermark_x, $watermark_y, (!$random_color ? $color : rand($this->color_from, $this->color_to)), $shrift_template, $text [$i]);
                    $watermark_x = $coords [4];
                    if ($random_size)
                        $shrift_weight = $shrift_weight_2;
                }
            }

            plugins::o()->run_hook('watermark_end');
        } catch (PReturn $e) {
            return $e->r();
        }
        if (!$rewrite)
            header("Content-type: image/png");
        if ($rewrite)
            ob_start();
        $str = 'image' . $type;
        $str($img);
        if ($rewrite) {
            $image = ob_get_contents();
            ob_end_clean();
            $rfile = fopen($image_path, 'w');
            fwrite($rfile, $image);
            fclose($rfile);
        }
        imagedestroy($img);
        if (!$rewrite)
            die();
    }

    /**
     * Функция для изменения размера изображения
     * @param string|array $filepath путь к файлу
     * если массив, то первый элемент - путь, второй - с типом изображения
     * @param int $maxwidth макс. ширина изображения
     * @param int $maxheight макс. высота изображения
     * @param int $curwidth текущая ширина изображения
     * @param int $curheight текущая высота изображения
     * @param string $new_name путь, куда будет сохраняться уменьшенное изображение
     * @param bool $cut обрезать до размеров превью? 
     * @return bool true, в случае успешного выполения функции
     * @throws EngineException 
     */
    public function resize($filepath, $maxwidth = "", $maxheight = "", $curwidth = "", $curheight = "", $new_name = "", $cut = false) {
        lang::o()->get('file');
        if (is_array($filepath)) {
            list($filepath, $type) = $filepath;
            $type = file::o()->get_filetype($type);
        }
        else
            $type = file::o()->get_filetype($filepath);
        if ($type == "jpg")
            $type = "jpeg";
        if (!$type)
            throw new EngineException("file_false_name");
        if (!$curwidth || !$curheight) {
            $wh = $this->is_image(ROOT . $filepath, true);
            $curwidth = $wh [0];
            $curheight = $wh [1];
        }
        if ((!$maxheight && !$maxwidth) || !(($curwidth >= $maxwidth && $maxwidth) || ($curheight >= $maxheight && $maxheight)))
            throw new EngineException('file_unknown_resize', array(
        ($maxwidth ? $maxwidth : unended),
        ($maxheight ? $maxheight : unended)
            ));
        $imagecftype = 'imagecreatefrom' . $type;
        $imagetype = 'image' . $type;

        if (!function_exists($imagecftype) || !function_exists($imagetype))
            throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_images'));

        $source = @$imagecftype($filepath);
        if (!$source)
            throw new EngineException(lang::o()->v('file_unknown_type') . lang::o()->v('file_ft_images'));

        $source_x = $source_y = 0;

        $ratio = $curwidth / $curheight;
        $new_w = $maxwidth;
        $new_h = $maxheight;
        $ratio_n = $new_h ? $new_w / $new_h : 0;
        if (!$maxheight || $ratio > $ratio_n)
            $new_h = longval($new_w / $ratio);
        else
            $new_w = longval($new_h * $ratio);

        if ($cut && $ratio_n && $ratio_n != $ratio) {
            if ($new_w == $maxwidth) {
                $delta = ($curheight * $ratio_n);
                $source_x = longval(($curwidth - $delta) / 2);
                $curwidth = longval($delta);
            } else {
                $delta = ($curwidth / $ratio_n);
                $source_y = longval(($curheight - $delta) / 2);
                $curheight = longval($delta);
            }
            $new_w = $maxwidth;
            $new_h = $maxheight;
        }

        $target = imagecreatetruecolor($new_w, $new_h);
        @imagecopyresampled($target, $source, 0, 0, $source_x, $source_y, $new_w, $new_h, $curwidth, $curheight);
        $ret = $imagetype($target, ROOT . (!$new_name ? $filepath : $new_name));

        @imagedestroy($target);
        @imagedestroy($source);
        if (!$ret)
            throw new EngineException("file_unknown_error");
        return true;
    }

}

?>