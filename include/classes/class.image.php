<?php

/**
 * Project:            	CTRev
 * File:                class.image.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright 	        (c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @package             file
 * @name           	Методы обработки изображений
 * @version   	        1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class image {

    /**
     * Ширина превью
     * @var int
     */
    protected $preview_width = 0;

    /**
     * Высота превью
     * @var int
     */
    protected $preview_height = 0;

    /**
     * Стандартный размер букв
     * @var int
     */
    protected $size_def = 23;

    /**
     * Максимальный размер букв
     * @var int
     */
    protected $size_max = 23;

    /**
     * Минимальный размер букв
     * @var int
     */
    protected $size_min = 15;

    /**
     * Минимальный цвет букв
     * @var color
     */
    protected $color_from = 0x000000;

    /**
     * Максимальный цвет букв
     * @var color
     */
    protected $color_to = 0x333333;

    /**
     * Установка размеров превьюшки
     * @param int $width ширина
     * @param int $height высота
     * @return image $this
     */
    public function set_preview_size($width, $height) {
        $this->preview_height = (int) $height;
        $this->preview_width = (int) $width;
        return $this;
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
        // invert COLOR
        $r = (255 - $r);
        $g = (255 - $g);
        $b = (255 - $b);
        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '') . $r;
        $color .= ( strlen($g) < 2 ? '0' : '') . $g;
        $color .= ( strlen($b) < 2 ? '0' : '') . $b;
        return '0x' . $color;
    }

    /**
     * Метода наложения текста на изображение
     * @global file $file
     * @global lang $lang
     * @param string $image_path путь к изображению
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
        global $file, $lang;
        $lang->get('file');
        $font = ($font ? $font : "upload/fonts/watermark.ttf");
        $image_path = ROOT . $image_path;
        if (!preg_match("/^[lrc][tbc]$/siu", $pos))
            $pos = "rb";
        $pos1 = $pos [0];
        $pos2 = $pos [1];
        if ($text && $image_path) {
            $type = strtolower($file->get_filetype($image_path));
            $shrift_weight = $this->size_def;
            $shrift_rotate = 0;
            $shrift_template = ROOT . $font;
            $template_file = $image_path;
            if (!file_exists($shrift_template))
                throw new EngineException("file_cannt_load_shrift");
            if ($type == 'jpg')
                $type = 'jpeg';
            $str = 'imagecreatefrom' . $type;
            $img = $str($template_file);
            if (!$img)
                throw new EngineException("file_cannt_load_gd");
            //$img = imagecreatetruecolor ( 300, 60 );
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
            if (!$random_color) {
                if ($color == "auto" || !preg_match('/^([0-9A-fa-f]){6}$/siu', $color)) {
                    $totalcolor = imagecolorat($img, $watermark_x, $watermark_y);
                    $rgb = imagecolorsforindex($img, $totalcolor);
                    $color = $this->rgb2hex($rgb ['red'], $rgb ['green'], $rgb ['blue']);
                } else
                    $color = '0x' . $color;
            }
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
    }

    /**
     * Функция для изменения размера изображения
     * @global lang $lang
     * @param string $filepath путь к файлу
     * @param int $maxwidth макс. ширина изображения
     * @param int $maxheight макс. высота изображения
     * @param int $curwidth текущая ширина изображения
     * @param int $curheight текущая высота изображения
     * @param string $tmp_name имя файла, если оно отличается от имени, указанном в {@link $filepath}
     * @param string $new_name путь, куда будет сохраняться уменьшенное изображение
     * @return bool true, в случае успешного выполения функции
     * @throws EngineException 
     */
    public function resize($filepath, $maxwidth = "", $maxheight = "", $curwidth = "", $curheight = "", $tmp_name = "", $new_name = "") {
        global $lang;
        $lang->get('file');
        if (!$curwidth || !$curheight) {
            $wh = $this->is_image(ROOT . $filepath, true);
            $curwidth = $wh [0];
            $curheight = $wh [1];
        }
        if (!$tmp_name)
            $tmp_name = $filepath;
        if ((!$maxheight && !$maxwidth) || !(($curwidth > $maxwidth && $maxwidth) || ($curheight > $maxheight && $maxheight)))
            throw new EngineException('file_too_big_wh', array(
                ($maxwidth ? $maxwidth : unended),
                ($maxheight ? $maxheight : unended)
            ));
        if (!preg_match('/\.([a-zA-Z0-9]+)$/siu', $tmp_name, $matches))
            throw new EngineException("file_false_name");
        $type = $matches [1];
        if ($type == "jpg")
            $type = "jpeg";
        $imagecftype = 'imagecreatefrom' . $type;
        $imagetype = 'image' . $type;
        if (!function_exists($imagecftype) || !function_exists($imagetype))
            throw new EngineException($lang->v('file_unknown_type') . $lang->v('file_ft_images'));
        $source = @$imagecftype($filepath);
        if (!$source)
            throw new EngineException($lang->v('file_unknown_type') . $lang->v('file_ft_images'));
        $new_w = (($maxwidth && $curwidth > $curheight) || !$maxheight ? $maxwidth : longval($maxheight * ($curwidth / $curheight)));
        $new_h = (($maxheight && $curheight > $curwidth) || !$maxwidth ? $maxheight : longval($maxwidth * ($curheight / $curwidth)));
        $target = imagecreatetruecolor($new_w, $new_h);
        @imagecopyresampled($target, $source, 0, 0, 0, 0, $new_w, $new_h, $curwidth, $curheight);
        $ret = $imagetype($target, ROOT . (!$new_name ? $filepath : $new_name));
        @imagedestroy($target);
        @imagedestroy($source);
        if (!$ret)
            throw new EngineException("file_unknown_error");
        return true;
    }

}

?>