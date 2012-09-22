<?php

/**
 * Project:            	CTRev
 * File:                class.bbcodes.php
 *
 * @link 	  	http://ctrev.cyber-tm.ru/
 * @copyright         	(c) 2008-2012, Cyber-Team
 * @author 	  	The Cheat <cybertmdev@gmail.com>
 * @package             display
 * @name		Класс BB-кодов
 * @version           	1.00
 */
if (!defined('INSITE'))
    die('Remote access denied!');

class formatter_callbacks extends pluginable_object {

    /**
     * Мультивложенность тегов
     * @var array
     */
    protected $multi_tagin = array(
        "simple",
        "position",
        "quote",
        "quote=",
        "spoiler",
        "spoiler=");

    /**
     * Паттерны BB-кодов
     * @var array
     */
    protected $bb_patterns = array(
        "simple" => '/\[(b|i|u|s|p|su[bp]|strong|strike|em)\](.+?)\[\/\1\]/siu',
        "position" => '/\[(left|right|center|justify)\](.+?)\[\/\1\]/siu',
        //"base" => '/\[baseurl\]/siu',
        "url" => '/\[url\]%URL_PATTERN;\[\/url\]/siu',
        "url=" => '/\[url=%Q;%URL_PATTERN;%Q;\](.+?)\[\/url\]/siu',
        'auto_url' => '/(^|\s|\>)%URL_PATTERN;(\s|$|\<)/siu',
        "size" => '/\[size=%Q;([0-9\.]+)\s*(px|pt|\%)\;?%Q;\](.+?)\[\/size\]/siu',
        "color" => '/\[color=%Q;\#?([0-9a-f]{3,6})\;?%Q;\](.+?)\[\/color\]/siu',
        "quote" => '/\[quote\](.+?)\[\/quote\]/siu',
        "quote=" => '/\[quote=%Q;([^\n]+?)%Q;\](.+?)\[\/quote\]/siu',
        "spoiler" => '/\[spoiler\](.+?)\[\/spoiler\]/siu',
        "spoiler=" => '/\[spoiler=%Q;([^\n]+)%Q;\](.+?)\[\/spoiler\]/siu');

    /**
     * Заменяемые значения в HTML
     * @var array
     */
    protected $bb_replacement = array(
        "simple" => '<\1>\2</\1>',
        "position" => '<div align="\1">\2</div>',
        //"base" => '%baseurl;',
        "url" => '<a href="\1">\1</a>',
        "url=" => '<a href="\1">\6</a>',
        'auto_url' => '\1<a href="\2">\2</a>\7',
        "size" => '<font style="font-size: \1\2;">\3</font>',
        "color" => '<font color="#\1">\2</font>',
        "quote" => '<div class="quote cornerText">
			<div class="quote_title">%LANG[quote]</div>
			<div class="quote_content">\1</div></div>',
        "quote=" => '<div class="quote cornerText">
			<div class="quote_title">%LANG[quote_from] <span>\1</span></div>
			<div class="quote_content">\2</div></div>',
        "spoiler" => '<div class="spoiler">
			<div class="spoiler_title"><div class="spoiler_icon"></div>&nbsp;%LANG[spoilered_text]</div>
			<div class="spoiler_content">\1</div></div>',
        "spoiler=" => '<div class="spoiler">
			<div class="spoiler_title"><div class="spoiler_icon"></div>&nbsp;\1</div>
			<div class="spoiler_content">\2</div></div>');

    /**
     * Простые теги
     * @var array
     */
    protected $simple_tags = array(
        "simple",
        "position",
        "size",
        "color",
        "url",
        "url=",
        "auto_url");

    /**
     * Переменная для очистки текста от тегов в цитируемом сообщении.
     * @var array
     */
    protected $remove_quote_tags = array(
        "hide" => '%LANG[hidden_text]');

    /**
     * Переменная для очистки текста от тегов. Сначала {@link $bb_patterns}, потом {@link $spec_patterns}
     * @var array
     */
    protected $removing_tags = array(
        "simple" => '\2',
        "position" => '\1',
        "url" => '\1',
        "url=" => '\6',
        'auto_url' => '\2',
        "size" => '\3',
        "color" => '\2',
        "quote" => '---
			\1
			---',
        "quote=" => '---\1---
			\2
			---',
        'code' => '-------
			\2
			-------',
        "img" => '\5',
        "list" => '\4',
        'hide' => '',
        'spoiler' => '',
        'spoiler=' => '');

    /**
     * Специальные паттерны, которые заменяются с помощью отдельных функций
     * @var array
     */
    protected $spec_patterns = array(
        "code" => '/\[code(?:=%Q;(js|html|php|css|java|delphi|cs|cpp|ls)%Q;)?\](.+?)\[\/code\]/siu',
        "img" => '/\[img(?:=%Q;([^\n]+)%Q;)?(?:\s+w=%Q;([0-9]+)%Q;)?(?:\s+h=%Q;([0-9]+)%Q;)?(?:\s+(bottom|left|middle|right|top))?\]%URL_PATTERN;\[\/img\]/siu',
        "list" => '/\[list(?:=%Q;(disc|circle|square)%Q;)?(?:=%Q;([aAiI1])%Q;(?:\s+s=%Q;([0-9]+)%Q;)?)?\](.+?)\[\/list\]/siu',
        "hide" => '/\[hide(?:=%Q;([0-9]+)%Q;)?(?:\s+g=%Q;([0-9\,]+)%Q;)?\](.+?)\[\/hide\]/siu');

    /**
     * Преобразование квадратных скобок в HTML-ASCII код
     * @param string $text входной текст
     * @return string преобразованный текст
     */
    protected function sc_sscrapes($text) {
        $text = str_replace('[', '&#91;', $text);
        $text = str_replace(']', '&#93;', $text);
        return $text;
    }

    /**
     * Конструктор класса
     * @return null 
     */
    protected function plugin_construct() {
        $this->access_var('bb_patterns', PVAR_ADD | PVAR_MOD);
        $this->access_var('bb_replacement', PVAR_ADD | PVAR_MOD);
        $this->access_var('spec_patterns', PVAR_ADD | PVAR_MOD);
        $this->access_var('multi_tagin', PVAR_ADD);
        $this->access_var('remove_quote_tags', PVAR_ADD);
        $this->access_var('removing_tags', PVAR_ADD);
        $this->access_var('simple_tags', PVAR_ADD);
    }

    /**
     * Аналог pcre_callback_hide для RSS
     * @param array $matches входящий массив парсенной строки
     * @return string HTML код
     */
    protected function pcre_callback_hide_rss($matches) {
        return $this->pcre_callback_hide($matches, true);
    }

    /**
     * Обработка значений для preg_replace_callback тега hide
     * @global users $users
     * @global lang $lang
     * @global furl $furl
     * @global display $display
     * @param array $matches входящий массив парсенной строки
     * @param bool $rss RSS?
     * @return string HTML код
     */
    protected function pcre_callback_hide($matches, $rss = false) {
        global $users, $lang, $furl, $display;
        $vars = array();
        if ($users->v()) {
            if ($matches[1]) {
                $matches[1] = longval(trim($matches[1]));
                if ($matches[1] <= $users->v('torrents_count'))
                    return $matches[3];
                if (!$rss) {
                    $vars = array($matches[1], $users->v('torrents_count'));
                    $text = "hidden_need_torrents_you_have";
                }
            } elseif ($matches[2]) {
                $grps = array_map('longval', explode(",", $matches[2]));
                $c = count($grps);
                for ($i = 0; $i < $c; $i++) {
                    if (!$users->get_group($grps[$i]))
                        continue;
                    if (!$rss) {
                        $pretext .= ( $pretext ? ", " : "") . $display->user_group_color($grps[$i]);
                    }
                    if ($users->v('group') == $grps[$i])
                        return $matches[3];
                }
                if (!$rss) {
                    $vars = array($pretext, $display->user_group_color($users->v('group')));
                    $text = "hidden_group_to_see";
                }
            } else
                return $matches[3];
        }
        if (!$rss)
            $text = "hidden_register_to_see";
        //else
        //    return $matches[3];
        if (!$rss) {
            if (!$vars)
                $vars = $furl->construct("registration");
            ob_start();
            mess($text, $vars, "info", false, "hidden_text", "left", false, true);
            $cont = ob_get_contents();
            ob_end_clean();
            return $cont;
        }
        return $lang->v('hidden_text');
    }

    /**
     * Обработка тега hide
     * @param string $text текст
     * @param bool $rss RSS?
     * @return string обработанный текст
     */
    protected function decode_hide($text, $rss = false) {
        $pattern = $this->spec_patterns ["hide"];
        $callback = array($this, 'pcre_callback_hide' . ($rss ? "_rss" : ""));
        return preg_replace_callback($pattern, $callback, $text);
    }

    /**
     * Обработка тега img
     * @param string $text текст
     * @return string обработанный текст
     */
    protected function decode_img($text) {
        $pattern = $this->spec_patterns ["img"];
        return preg_replace_callback($pattern, array($this, 'pcre_callback_img'), $text);
    }

    /**
     * Обработка значений для preg_replace_callback тега img
     * @param array $matches массив спарсенных частей
     * @return string HTML код
     */
    protected function pcre_callback_img($matches) {
        return "<img" . ($matches[1] ? " alt=\"" . $matches[1] . "\" title=\"" . $matches[1] . "\"" :
                        " alt=\"\"") .
                ($matches[2] ? " width=\"" . $matches[2] . "\"" : "") .
                ($matches[3] ? " height=\"" . $matches[3] . "\"" : "") .
                ($matches[4] ? " align=\"" . $matches[4] . "\"" : "") . " src=\"" . $matches[5] . "\">";
    }

    /**
     * Обработка тега list
     * @param string $text текст
     * @return string обработанный текст
     */
    protected function decode_list($text) {
        $pattern = $this->spec_patterns ["list"];
        return preg_replace_callback($pattern, array($this, 'pcre_callback_list'), $text);
    }

    /**
     * Обработка значений для preg_replace_callback тега list
     * @param array $matches массив спарсенных частей
     * @return string HTML код
     */
    protected function pcre_callback_list($matches) {
        $matches[4] = str_replace('[*]', "<li>", $matches[4]);
        if ($matches[2])
            return "<ol type=\"" . $matches[2] . "\"" . ($matches[3] ? " start=\"" . $matches[3] . "\"" : "") . ">" . $matches[4] . "</ol>";
        else
            return "<ul" . ($matches[1] ? " type=\"" . $matches[1] . "\"" : "") . ">" . $matches[4] . "</ul>";
    }

    /**
     * Обработка значений для preg_replace_callback тега hide
     * @global lang $lang
     * @param array $matches входящий массив парсенной строки
     * @param bool $rss RSS?
     * @return string HTML код
     */
    protected function pcre_callback_code($matches) {
        global $lang;
        return '<div class="shl_div_top"><div class="shl_title">' . $lang->v('code') . ':
                    <a href="javascript:void(0);" onclick="code_select_all(this);">' . $lang->v('select_all') . '</a>,
			<a href="javascript:void(0);" onclick="code_unoverflow(this);">' . $lang->v('unoverflow') . '</a></div>
			<div class="syntaxhighlighter">
			<pre class="' . mb_strtolower($matches[1]) . '">' . $this->sc_sscrapes($matches[2]) . '</pre>
			</div></div>';
    }

    /**
     * Функция обработки тега code
     * @param string $text текст
     * @return string обработанный текст
     */
    protected function decode_code($text) {
        $pattern = $this->spec_patterns ["code"];
        $callback = array($this, 'pcre_callback_code');
        return preg_replace_callback($pattern, $callback, $text);
    }

}

class bbcode_formatter extends formatter_callbacks {

    /**
     * Массив смайлов
     * @var array
     */
    public $smilies = array();

    /**
     * Паттерны ББ-кодов в "одном флаконе"
     * @var array
     */
    protected $merged_bb = array();

    /**
     * Выполнялось ли форматирование текста
     * @var bool
     */
    protected $executed_format = false;

    /**
     * Выполнялось ли форматирование текста для цитат
     * @var bool
     */
    protected $subexe_format = false;

    /**
     * Инициализовано ли JS форматирование?
     * @var bool
     */
    protected $inited_js_format = false;

    /**
     * Соединение паттернов ББ-кодов
     * @return array паттерны
     */
    protected function merge_bb() {
        if (!$this->merged_bb)
            $this->merged_bb = array_merge($this->bb_patterns, $this->spec_patterns);
        return $this->merged_bb;
    }

    /**
     * Инициализация паттернов и заменяемого текста для format_text
     * @global display $display
     * @global string $BASEURL
     * @param array $patterns паттерны
     * @param array $replacement заменяемый текст
     * @return null
     */
    protected function format_text_init(&$patterns = "", &$replacement = "") {
        global $display, $BASEURL;
        $global = false;
        if (!$patterns && !$replacement) {
            if ($this->executed_format)
                return;
            $this->format_text_init($this->bb_patterns, $this->bb_replacement);
            $this->format_text_init($this->spec_patterns);
            return;
        }
        if ($patterns) {
            $q = mpc($display->html_encode('"'));
            $sq = mpc($display->html_encode("'"));
            $patterns = str_replace("%URL_PATTERN;", display::url_pattern, $patterns);
            $patterns = str_replace('"', $q, $patterns);
            $patterns = str_replace("'", $sq, $patterns);
            $patterns = str_replace("%Q;", '(?:' . $q . '|' . $sq . ')?', $patterns);
        }
        if ($replacement) {
            $replacement = preg_replace_callback('/%LANG\[(?:"|\'|)([a-zA-Z0-9\-\_]+)(?:"|\'|)\]/siu', array($this, 'lang_macro'), $replacement);
            $replacement = str_replace('%baseurl;', $BASEURL, $replacement);
        }
    }

    /**
     * Обработка конструкции %LANG
     * @global lang $lang
     * @param array $matches входящий массив парсенной строки
     * @return string языковая часть
     */
    protected function lang_macro($matches) {
        global $lang;
        return $lang->v($matches[1]);
    }

    /**
     * Удаление ББ-тегов из текста(для метатега "description")
     * @param string $text текст с тегами
     * @param array $not_remove не удаляемые ББ-теги
     * @return string текст без тегов
     */
    public function remove_tags($text, $not_remove = array()) {
        $this->format_text_init();
        $curpatt = $this->merge_bb();
        $currem = $this->removing_tags;
        if ($not_remove)
            foreach ($not_remove as $cnrem) {
                unset($curpatt [$cnrem]);
                unset($currem [$cnrem]);
            }
//$text = preg_replace ( $curpatt, $currem, $text );
        foreach ($curpatt as $key => $pattern) {
            if (!$this->multi_tagin[$key])
                $text = preg_replace($pattern, $currem [$key], $text);
            else {
                $i = 0;
                do {
                    $o = $text;
                    $text = preg_replace($pattern, $currem [$key], $text);
                    $i++;
                } while ($o != $text && $i < 100);
            }
        }
        return $text;
    }

    /**
     * Удаление ББ-тегов из текста(для цитирования)
     * @global display $display
     * @param string $text текст с тегами
     * @param array $not_remove не удаляемые ББ-теги
     * @return string текст без тегов
     */
    public function remove_quote_tags($text, $not_remove = array()) {
        global $display;
        $this->format_text_init();
        if (!$this->subexe_format) {
            $this->format_text_init($null, $this->remove_quote_tags);
            $this->subexe_format = true;
        }
        $curpatt = $this->merge_bb();
        $currem = $this->remove_quote_tags;
        if ($not_remove)
            foreach ($not_remove as $cnrem) {
                unset($currem [$cnrem]);
            }
        foreach ($currem as $key => $repl) {
            if (!$curpatt[$key])
                continue;
            if (!$this->multi_tagin[$key])
                $text = preg_replace($curpatt[$key], $repl, $text);
            else {
                $i = 0;
                do {
                    $o = $text;
                    $text = preg_replace($curpatt[$key], $repl, $text);
                    $i++;
                } while ($o != $text && $i < 100);
            }
        }
        return $display->html_decode($text);
    }

    /**
     * Форматирование текста, согласно паттернам(простые теги типа b, i, u и пр.)
     * @global string $BASEURL
     * @global config $config
     * @param string $input входной текст
     * @return string форматированный текст
     */
    protected function format_text_simple($input) {
        global $BASEURL, $config;
        $this->init_smilies();
// $out = nl2br ( $input ); // лишь в 5.3 можно сделать для HTML Trans.
        $out = $input;
        $this->smilies_replace($out);
        $this->format_text_init();
        foreach ($this->simple_tags as $key)
            $out = preg_replace($this->bb_patterns [$key], $this->bb_replacement [$key], $out);
        return $out;
    }

    /**
     * Замена смайликов
     * @global string $BASEURL
     * @global config $config
     * @param string $input входящий текст
     * @return null
     */
    protected function smilies_replace(&$input) {
        global $BASEURL, $config;
        if (!$this->smilies)
            return;
        foreach ($this->smilies as $smilies_pack) {
            if ($smilies_pack)
                foreach ($smilies_pack as $smilie) {
                    $code = $smilie ['code'];
                    $image = $smilie ['image'];
                    $name = $smilie ['name'];
                    // preg_replace с модификатором i намного быстрее str_ireplace
                    $input = preg_replace('/' . mpc($code) . '/i', "<img src=\"" . $BASEURL . $config->v('smilies_folder') . "/" . $image . "\" 
                                alt=\"" . $name . "\" 
                                title=\"" . $name . "\">", $input);
                }
        }
    }

    /**
     * Перенос на новую строку в формате HTML
     * @param string $input входной текст
     * @return string форматированный текст
     */
    public function make_newline($input) {
        $input = preg_replace('/\r?\n\r?/siu', "<br>\n", $input);
        $input = preg_replace('/(\t|\s\s)/siu', '&nbsp;&nbsp;', $input);
        return $input;
    }

    /**
     * Форматирование текста для его последующего вывода в RSS
     * @param string $input входной текст
     * @return string отформатированный текст
     */
    protected function format_text_rss($input) {
        $out = str_replace(array(
            "\r\n",
            "\r",
            "\n"), "<br />", $input);
        $out = $this->remove_tags($out, array(
            "url",
            "url=",
            "auto_url",
            "img",
            "hide"));
        $out = $this->call_method("decode_img", $out);
        $out = $this->call_method("decode_hide", array($out, true));
        $out = preg_replace($this->bb_patterns ["url"], '<a href="\1">\1</a>', $out);
        $out = preg_replace($this->bb_patterns ["url="], '<a href="\1">\6</a>', $out);
        $out = preg_replace($this->bb_patterns ["auto_url"], '<a href="\2">\2</a>', $out);
        return $out;
    }

    /**
     * Форматирование текста, согласно паттернам
     * @global string $BASEURL
     * @global config $config
     * @global tpl $tpl
     * @global plugins $plugins
     * @param string $input входной текст
     * @param bool $rss RSS?, если же это поле равно ATOM, то будет обрабатываться, как для ATOM,
     * SIMPLE - простое форматирование
     * QUOTE - удаление лишних тегов(hide f.e.)
     * @param bool $nc без div class='content'
     * @return string форматированный текст
     */
    public function format_text($input, $rss = false, $nc = false) {
        global $tpl, $plugins;
        try {
            $plugins->pass_data(array('input' => &$input,
                'rss' => &$rss), true)->run_hook('bbcodes_format_text');
        } catch (PReturn $e) {
            return $e->r();
        }

        switch (strtoupper($rss)) {
            case "SIMPLE":
                return $this->format_text_simple($input);
                break;
            case "ATOM":
                return $this->format_text_rss($input);
                break;
            case "QUOTE":
                return $this->remove_quote_tags($input);
                break;
            case 1:
                return $this->remove_tags($input);
                break;
        }
        $this->init_smilies();
// $out = nl2br ( $input ); // лишь в 5.3 можно сделать для HTML Trans.
        $out = $input;
        $this->format_text_init();
        $out = $this->make_newline($out);
        $this->smilies_replace($out);
        foreach ($this->spec_patterns as $key => $pattern) {
            $funct = 'decode_' . $key;
            $out = $this->call_method($funct, $out);
        }
        foreach ($this->bb_patterns as $key => $pattern)
            $out = preg_replace($pattern, $this->bb_replacement [$key], $out);
        foreach ($this->multi_tagin as $tag) {
            $tag_pattern = $this->bb_patterns [$tag];
            $tag_replace = $this->bb_replacement [$tag];
            $i = 0;
            do {
                $o = $out;
                $out = preg_replace($tag_pattern, $tag_replace, $out);
                $i++;
            } while ($o != $out && $i < 100);
        }
        if (!$this->inited_js_format) {
            $this->inited_js_format = true;
            $c = $tpl->fetch('initializer_formatter.tpl');
        }
        $c .= (!$nc ? "<div class='content'>" : "") . $out . (!$nc ? "</div>" : "");
        return $c;
    }

}

final class bbcodes extends bbcode_formatter {

    /**
     * ID формы
     * @var int 
     */
    protected $id = 0;

    /**
     * Инициализовано ли JS для формы с BB-кодами
     * @var bool
     */
    protected $inited_js = false;

    /**
     * Инициализация смайлов
     * @global db $db
     * @return null
     */
    public function init_smilies() {
        global $db;
        if ($this->smilies)
            return;
        $r = $db->query('SELECT name,image,code,show_bbeditor FROM smilies', 'smilies');
        $this->smilies = array(array(), array());
        foreach ($r as $row)
            $this->smilies[$row['show_bbeditor']][] = $row;
    }

    /**
     * Форма ввода текста с BB-кодами
     * @global tpl $tpl
     * @global lang $lang
     * @global display $display
     * @global plugins $plugins
     * @param string $name имя формы
     * @param string $text текст
     * @return string HTML код формы
     */
    public function input_form($name, $text = '') {
        global $tpl, $lang, $display, $plugins;
        if (is_array($name)) {
            $text = $name ['text'];
            $name = $name ['name'];
        }
        $this->init_smilies();
        $lang->get('bbcodes');
        $c = '';

        try {
            $plugins->pass_data(array('name' => $name,
                'text' => $text,
                'html' => &$c), true)->run_hook('bbcodes_input_form');
        } catch (PReturn $e) {
            return $e->r();
        }

        $tpl->assign("textarea_rname", $name);
        $tpl->assign("textarea_name", 'formid' . time() . $this->id++);
        $tpl->assign("textarea_text", $text);
        $tpl->assign("smilies", $this->smilies[1]);
        $tpl->assign("inited_bbcodes", $this->inited_js);
        if (!$this->inited_js) {
            $this->inited_js = true;
            $fs = array_merge($this->smilies[0], $this->smilies[1]);
            $tpl->assign('smilies_array', $display->array_export_to_js($fs));
        }
        $c .= $tpl->fetch('init_textinput.tpl');
        return $c;
    }

}

?>