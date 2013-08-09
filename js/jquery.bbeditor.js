/**
 * Написанный ниже бред ни в коем случае не стоит воспринимать всерьёз.
 * Я уже сам не помню, сколько выпил туалетного утёнка, когда это писал.
 * Good luck, comrads!
 */

/**
 * Вставка цитаты в Textarea
 * @param {array} arr массив из объектов textarea и iframe(wysiwyg)
 * @param {string} author автор цитаты
 * @param {string} txt текст цитаты
 * @returns {null}
 */
function add_tota(arr, author, txt) {
    var o = arr[0];
    var wysiwyg = arr[1];
    var v = "";
    if (!wysiwyg)
        v = o.html();
    else
        v = o.document.body.innerText;
    insertAtCaretPos(o, (trim(v) ? '\n' : "")
            + '[quote="' + author + '"]'
            + trim(txt)
            + '[/quote]' + '\n', wysiwyg, 'bb');
}

/**
 * Инициализация ползунка
 * @param {string} name ID формы с бб-кодами
 * @returns {null}
 */
function init_trackbar(name) {
    jQuery(document).ready(function($) {
        $('#ss_' + name).trackbar(
                {
                    onMove: function() {
                        jQuery(".preview_" + name).css("font-size", this.leftValue + "pt");
                    },
                    dual: false, // two intervals
                    width: 200, // px
                    leftLimit: 7, // unit of value
                    leftValue: 7, // unit of value
                    rightLimit: 24, // unit of value
                    rightValue: 0, // unit of value
                    leftBegunImage: theme_path + '/images/imgtrackbar/b_l.gif',
                    rightBegunImage: theme_path + '/images/imgtrackbar/b_r.gif'
                },
        'ss_' + name
                );
    });
}

/**
 * Выделение кода
 * @param {object} obj объект ссылки
 * @returns {null}
 */
function code_select_all(obj) {
    var text = jQuery(obj).parent('div').next('div.syntaxhighlighter').children('pre').get(0);
    var range = null, selection = null;
    if (jQuery.browser.msie) {
        range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (jQuery.browser.mozilla || jQuery.browser.opera) {
        selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    } else if (jQuery.browser.safari) {
        selection = window.getSelection();
        selection.setBaseAndExtent(text, 0, text, 1);
    }
}

/**
 * Расширение кода
 * @param {object} obj объект ссылки
 * @returns {null}
 */
function code_unoverflow(obj) {
    var $el = jQuery(obj).parent('div').next('div.syntaxhighlighter');
    if ($el.css('max-height') == "none") {
        $el.css({
            'max-height': $el.css('min-height'),
            'min-height': 'none',
            'overflow': 'auto'
        });
    } else {
        $el.css({
            'min-height': $el.css('max-height'),
            'max-height': 'none',
            'overflow': 'none'
        });
    }
}

/**
 * Эмуляция прозрачности для кнопок BBCode
 * @returns {null}
 */
function opacity_bbcodes() {
    jQuery(document).ready(function($) {
        opacity_emulator(".bbcodes", 0.8);
        jQuery(".bbcodes").hover(function() {
            opacity_emulator(this, 1);
        }, function() {
            opacity_emulator(this, 0.8);
        });
    });
}

/**
 * Обработка URL BBCode
 * @param {object} $st объект textarea
 * @param {string} code BBCode(img, url, f.e.)
 * @param {bool} html HTML?
 * @returns {null}
 */
function url_bbcode($st, code, html) {
    var $text = getSelected($st, html);
    var istext = $text ? true : false;
    if ($text.match(URL_PATTERN)) {
        replaceSelected($st, "[" + code + "]" + $text + "[/" + code + "]", code, [null, $text], html);
        return;
    }
    var $url = prompt(please_enter_link, 'http://');
    if (!$url || !$url.match(URL_PATTERN)) {
        alert(bbcode_error);
        return;
    }
    var $pos = "";
    if (code == "img") {
        $pos = prompt(please_enter_pos, 'left');
        if ($pos && $pos != 'left' && $pos != 'right' && $pos != 'center') {
            alert(bbcode_error);
            return;
        }
    }
    if (code == "img") {
        var tmp = $url;
        $url = $text;
        $text = tmp;
    }
    if ($pos)
        $pos = " " + $pos;
    var $code = "[" + code + ($text ? "=\"" + $url + "\"" : "") + ($pos ? $pos : "") + "]" + ($text ? $text : $url) + "[/" + code + "]";
    if (istext)
        replaceSelected($st, $code, code, [$url, $text, $pos], html);
    else
        insertAtCaretPos($st, $code, html, code);
}

/**
 * Обработка LIST BBCode
 * @param {object} $st объект textarea
 * @param {string} code BBCode(list, nlist, f.e.)
 * @param {bool} html HTML?
 * @returns {null}
 */
function list_bbcode($st, code, html) {
    var subparam = "";
    if (code == "nlist") {
        code = "list";
        subparam = "1";
    }
    var text = getSelected($st, html);
    var istext = (text ? true : false);
    if (text)
        text = "[*]" + text.replace(/(\n+)/g, '\n[*]');
    text = (text ? text : "[*]");
    var $code = "[" + code + (subparam ? "=\"" + subparam + "\"" : "") + "]" + text + "[/" + code + "]";
    if (istext)
        replaceSelected($st, $code, code, subparam, html);
    else
        insertAtCaretPos($st, $code, html, code);
}

/**
 * Удаление BB-тегов при замене на другой
 * @param {string} $sel_text выбранный текст
 * @param {string} code тип BBCode(регэкспом)
 * @param {string} subcode имя BBCode(center, left, right, f.e.)
 * @returns {string} обработанный текст
 */
function remove_bbtags($sel_text, code, subcode) {
    code = code ? '(' + code + ')' : '([a-zA-Z0-9]+)';
    var tsubcode = code ? '(' + code + ')' : '\1';
    var regexp = new RegExp('^\\[' + code + '(.*?)\\](.*?)\\[\\/' + tsubcode + '\\]$', 'ig');
    var curcode = $sel_text.match(regexp);
    var first_scr = '';
    var second_scr = '';
    if (curcode) {
        curcode = curcode[1];
        if (typeof subcode != 'undefined') {
            if (subcode && curcode != subcode)
                first_scr = '[' + subcode + '$2]';
            if (subcode && curcode != subcode)
                second_scr = '[/' + subcode + ']';
        }
    }
    return $sel_text.replace(regexp, first_scr + '$3' + second_scr);
}

/**
 * Подстановка BBCode
 * @param {object} $st объект textarea
 * @param {string} code BBCode(list, nlist, f.e.)
 * @param {bool} html HTML?
 * @returns {null}
 */
function replace_selected($st, code, html) {
    if (html)
        return;
    var subcode = '';
    switch (code) {
        case "img":
        case "quote":
        case "spoiler":
            return;
            break;
        case "left":
        case "right":
        case "center":
        case "justify":
            subcode = code;
            code = 'left|right|center|justify';
            break;
        case "sub":
        case "sup":
            subcode = code;
            code = 'sub|sup';
            break;
    }
    var oldtext = getSelected($st, html);
    if (oldtext) {
        var newtext = remove_bbtags(oldtext, code, subcode);
        if (oldtext != newtext) {
            replaceSelected($st, newtext, code, null, html);
            return true;
        }
    }
    return false;
}
/**
 * Получение выбранной области текста
 * @param {object} o объект
 * @returns {object} область
 */
function getRange(o) {
    var r;
    if (o.getSelection) {
        o = o.getSelection();
        if (o.getRangeAt)
            r = o.rangeCount ? o.getRangeAt(/*o.rangeCount - 1*/0) : document.createRange();
        else {
            r = document.createRange();
            r.setStart(o.anchorNode, o.anchorOffset);
            r.setEnd(o.focusNode, o.focusOffset);
        }
    } else if (o.selection)
        r = o.selection.createRange();
    return r;

}
/**
 * Получение выбранного текста
 * @param {object} o объект
 * @param {bool} html HTML?
 * @param {bool} text получение текста?
 * @returns {string} текст
 */
function getSelected(o, html, text) {
    if (!html)
        return o.getSelection().text;
    return !text ? getSelectionHTML(o) : getSelectionText(o);
}

/**
 * Получение выбранного HTML кода
 * @param {object} o объект
 * @returns {string} HTML кода
 */
function getSelectionHTML(o) {
    var r, h;
    r = getRange(o);
    if (o.selection)
        h = r.htmlText;
    else {
        var d = /*o.*/document.createElement('div');
        var c = r.cloneRange().cloneContents();
        d.appendChild(c);
        h = d.innerHTML;
    }
    return h;
}
/**
 * Получение выбранного текста
 * @param {object} o объект
 * @returns {string} текст
 */
function getSelectionText(o) {
    var r, h;
    r = getRange(o);
    if (o.selection)
        h = r.text;
    else
        h = r.toString();
    return h;
}
/**
 * Подготовка к выполнению комманды
 * @param {object} o объект
 * @return {null}
 */
function prepare_exec(o) {
    o = o.document;
    if (o.queryCommandEnabled("useCSS"))
        o.execCommand("useCSS", false, false);
    if (o.queryCommandEnabled("styleWithCSS"))
        o.execCommand("styleWithCSS", false, false);
    return null;
}

/**
 * Замена выбранной области
 * @param {object} o объект
 * @param {string} what чем заменяем
 * @param {string} code BBCode
 * @param {string} subparam параметр BBCode
 * @param {bool} html HTML?
 * @return {null}
 */
function replaceSelected(o, what, code, subparam, html) {
    o.focus();
    if (!html)
        return o.replaceSelection(what);
    var c = null;
    if (subparam && typeof subparam != "object")
        subparam = [subparam];
    prepare_exec(o);
    switch (code) {
        case "b":
            c = "bold";
        case "i":
            if (!c)
                c = "italic";
        case "s":
            if (!c)
                c = "strikeThrough";
        case "u":
            if (!c)
                c = "underline";
        case "sub":
            if (!c)
                c = "subscript";
        case "sup":
            if (!c)
                c = "superscript";
            o.document.execCommand(c, false, null);
            return null;
            break;
        case "center":
        case "justify":
        case "left":
        case "right":
            if (code == "justify")
                c = "Full";
            else {
                c = code;
                c[0] = c[0].toUpperCase();
            }
            o.document.execCommand("justify" + c, false, null);
            return null;
            break;
        case "color":
            if (subparam[0] != "#")
                subparam = "#" + subparam;
        case "size":
            o.document.execCommand('fontName', false, 'curwysiwygfont');
            var obj = null;
            if (code == "size")
                obj = {
                    "font-size": subparam
                }
            if (code == "color")
                obj = {
                    "color": subparam
                }
            jQuery("font[face='curwysiwygfont']", o.document).css(obj).attr("face", " ")/*.removeAttr("face")*/;
            return null;
            break;
        case "url":
            if (!subparam)
                return null;
            var url = subparam[0];
            o.document.execCommand('createLink', false, url);
            return null;
            break;
        case "img":
            if (!subparam)
                return null;
            var src, title, align = "";
            src = subparam[1];
            if (subparam[0])
                title = subparam[0];
            if (subparam[2])
                align = trim(subparam[2]);
            o.document.execCommand('insertImage', false, 'curwysiwygimage');
            var d = jQuery('img#curwysiwygimage', o.document);
            if (!d.length)
                d = jQuery('img[src=curwysiwygimage]', o.document);
            d.removeAttr("id");
            d.removeAttr("alt");
            d.removeAttr("src");
            if (title)
                d.attr("alt", title).attr("title", title);
            else
                d.attr("alt", "");
            if (align)
                d.attr("align", align);
            d.attr("src", src);
            return null;
            break;
    }
    what = convert_bh(what, false, true);
    var s = getRange(o);
    if (s.createContextualFragment) {
        s.deleteContents();
        var node = s.createContextualFragment(what);
        s.insertNode(node);
    } else
        s.pasteHTML(what);
    return null;
}
/**
 * Вставка на позицию курсора
 * @param {object} o объект
 * @param {string} what что вставляем
 * @param {bool} html HTML?
 * @param {string} code BBCode
 * @returns {null}
 */
function insertAtCaretPos(o, what, html, code) {
    o.focus();
    if (!html)
        return o.insertAtCaretPos(what);
    switch (code) {
        case "bb":
        case "br":
        case "smilie":
        case "img":
        case "url":
            break;
        default:
            return null;
            break;
    }
    what = convert_bh(what, false);
    if (code == "br")
        what += " "; // Чтобы перенесло на новую линию, без этого никак(по-крайней мере в опере)
    if (o.document.queryCommandEnabled("insertHTML")) {
        prepare_exec(o);
        o.document.execCommand("insertHTML", false, what);
        return null;
    }
    var s = getRange(o);
    s.collapse(false);
    if (s.createContextualFragment) {
        var node = s.createContextualFragment(what);
        s.insertNode(node);
    } else
        s.pasteHTML(what);
    return null;
}
/**
 * Получение обрезанного ID textarea
 * @param {object} obj объект textarea
 * @returns {string} ID textarea
 */
function ta_cut_name(obj) {
    var pre = 'textarea_';
    return jQuery(obj).attr('id').substr(pre.length);
}
/**
 * Получение объекта textarea и iframe
 * @param {string} name ID или name textarea
 * @returns {array} массив объектов
 */
function getTa(name) {
    var $st = jQuery("textarea#textarea_" + name);
    if (!$st.length) {
        $st = jQuery('textarea[name="' + name + '"]');
        name = ta_cut_name($st);
    }
    var wysiwyg = false;
    if ($st.parent().css("display") == "none") {
        $st = select_wysiwyg(name, false, !document.selection);
        wysiwyg = true;
    }
    return [$st, wysiwyg];
}
/**
 * Вставка BBCode
 * @param {string} name ID textarea
 * @param {string} code BBCode
 * @param {string} subparam параметр BBCode
 * @returns {null}
 */
function bbcode(name, code, subparam) {
    var $st, wysiwyg;
    if (typeof name == "string") {
        var arr = getTa(name);
        $st = arr[0];
        wysiwyg = arr[1];
    } else {
        $st = name;
        wysiwyg = true;
    }
    switch (code) {
        case "list":
        case "nlist":
            list_bbcode($st, code, wysiwyg);
            break;
        case "img":
        case "url":
            url_bbcode($st, code, wysiwyg);
            replace_selected($st, code, wysiwyg);
            break;
        default:
            if (replace_selected($st, code, wysiwyg))
                return;
            var $cursel = getSelected($st, wysiwyg);
            var $insert = "[" + code + (subparam ? "=\"" + subparam + "\"" : "") + "]" + ($cursel ? $cursel : "") + "[/" + code + "]";
            if ($cursel)
                replaceSelected($st, $insert, code, subparam, wysiwyg);
            else
                insertAtCaretPos($st, $insert, wysiwyg, code);
            break;
    }
    $st.focus();
}

/**
 * Вставка смайла
 * @param {string} name ID textarea
 * @param {string} code смайл
 * @returns {null}
 */
function insert_smilie(name, code) {
    var arr = getTa(name);
    var $st = arr[0];
    var wysiwyg = arr[1];
    insertAtCaretPos($st, " " + code + " ", wysiwyg, "smilie");
    //if ($st.isPrototypeOf('jQuery'))
    $st.focus();
}

/**
 * Инициализация выборки цвета
 * @param {string} $name ID textarea
 * @returns {null}
 */
function init_colorpicker($name) {
    jQuery(document).ready(function($) {
        $('#colorpicker_' + $name).ColorPicker({
            flat: true,
            onSubmit: function(cobj, color) {
                bbcode($name, 'color', color);
            }
        });
    });
}


/**
 * Выборка iframe
 * @param {string} name ID textarea
 * @param {bool} jq без jQuery
 * @param {bool} wind окно?
 * @returns {object} iframe
 */
function select_wysiwyg(name, jq, wind) {
    var id = 'wysiwyg_' + name;
    if (!jq) {
        var wysiwyg = window.frames[id] ? window.frames[id] : document.getElementById(id);
        return !wind ? (wysiwyg.document || wysiwyg.contentDocument || wysiwyg.contentWindow.document) : wysiwyg.contentWindow || wysiwyg;
    }
    else {
        return jQuery('iframe#' + id);
    }
}
/**
 * Обработчик событий клавиатуры
 * @param {object} o объект textarea
 * @param {object} event объект события
 * @returns {null}
 */
function keyhandler(o, event) {
    if (event.keyCode == '13') {
        if (!event.shiftKey)
            insertAtCaretPos(o, "\n", true, "br");
        else
            bbcode(o, "p");
        event.preventDefault();
    }
}
/**
 * Смена типа редактора
 * @param {string} name|object ID или объект textarea
 * @param {bool} wysiwyg WYSIWYG?
 * @param {bool} nols без загрузки?
 * @returns {null}
 */
function editor_type(name, wysiwyg, nols) {
    var o1;
    if (typeof name == 'object' && jQuery(name).is('textarea')) {
        o1 = name;
        name = ta_cut_name(o1);
    } else
        o1 = jQuery('textarea#textarea_' + name);
    var o2 = select_wysiwyg(name, true);
    var wo;
    if (!nols)
        show_ls();
    if (wysiwyg && o1.parent().css("display") != "none") {
        var html = convert_bh(o1.val());
        wo = select_wysiwyg(name);
        o1.parent().hide();
        var intd = false;
        if (o2.is('.inited')) {
            wo.body.innerHTML = html;
            intd = true;
        }
        o2.parent().show();
        if (!intd) {
            o2.addClass('inited');
            o2.TextAreaResizer(true);
            wo.open();
            wo.write("<html>\n\
<head><link rel=\"stylesheet\" href=\"" + theme_path + "css/allstyle.css\"\n\
        type=\"text/css\"></head>\n\
<body></body>\n\
</html>");
            wo.close();
            wo.body.contentEditable = 'true';
            wo.designMode = 'on';
            wo.body.innerHTML = html !== null ? html : "";
            wo.body.focus();
            jQuery(wo.parentWindow).keypress(function(event) {
                keyhandler(this, event);
            });
            var props = new Array('font-size', 'font-family', 'margin', 'padding');
            for (var i = 0; i < props.length; i++)
                jQuery(wo.body).css(props[i], o2.css(props[i]));
        }
    } else if (!wysiwyg && o1.parent().css("display") == "none") {
        o2.parent().hide();
        o1.parent().show();
        wo = select_wysiwyg(name);
        o1.val(convert_bh(wo.body.innerHTML, true));
        o1.focus();
    }
    if (!nols)
        hide_ls();
}

// Вложенные теги

multi_tagin = {
    "simple": 1,
    "position": 1,
    "remfail": 1,
    "quote": 1,
    "spoiler": 1
};

other_tags = [
    'quote=',
    'spoiler=',
];


/// Вроде всё работает! *happy*

/**
 http://test.ru/test
 [left]test[/left][right]test[/right][center]test[/center][p]test[/p][sub]test[/sub][sup]test[/sup][size="12px"]test[/size]
 [color="634141"]test[/color][url="http://тест.пхп"]test[/url]
 [size="19px"][color="704e4e"]test[/color][/size]
 :-)
 [spoiler="trololo"][img="test" middle]http://localhost/CTRev/themes/CTRev/engine_images/online.png[/img][/spoiler]
 [code="PHP"][spoiler="ok"]sss[/spoiler]<div>test</div>[/code]
 [hide][spoiler]blahblahblah[/spoiler][/hide][spoiler][hide]blahblahblah[/hide][/spoiler]
 [quote="admin"]
 & what de fuck? lold
 [/quote]
 */

// ББ-код паттерны
bb_patterns = {
    'newline': /\r?\n\r?/,
    'nbsp': /(\t|\s\s)/,
    "code": /\[code(?:=%Q;(js|html|php|css|java|delphi|cs|cpp|ls)%Q;)?\]([\s\S]+?)\[\/code\]/,
    "hide": /\[hide(?:=%Q;([0-9]+)%Q;)?(?:\s+g=%Q;([0-9\,]+)%Q;)?\]([\s\S]+?)\[\/hide\]/,
    "img": /\[img(?:=%Q;([\s\S]+?)%Q;)?(?:\s+w=%Q;([0-9]+)%Q;)?(?:\s+h=%Q;([0-9]+)%Q;)?(?:\s+(bottom|left|middle|right|top))?\]%URL_PATTERN;\[\/img\]/,
    "list": /\[list(?:=%Q;(disc|circle|square)%Q;)?(?:=%Q;([aAiI1])%Q;(?:\s+s=%Q;([0-9]+)%Q;)?)?\]([\s\S]+?)\[\/list\]/,
    "simple": /\[(b|i|u|s|p|su[bp]|strong|strike|em)\]([\s\S]*?)\[\/\1\]/,
    "position": /\[(left|right|center|justify)\]([\s\S]*?)\[\/\1\]/,
    "url": /\[url\]%URL_PATTERN;\[\/url\]/,
    "url=": /\[url=%Q;%URL_PATTERN;%Q;\]([\s\S]*?)\[\/url\]/,
    'auto_url': /(^|\s+)%URL_PATTERN;(\s+|$|\<)/,
    "size": /\[size=%Q;([0-9\.]+)\s*(px|pt|\%)\;?%Q;\]([\s\S]*?)\[\/size\]/,
    "color": /\[color=%Q;\#?([0-9a-fA-F]{3,6})\;?%Q;\]([\s\S]*?)\[\/color\]/,
    "other": ""
};

bb_replacement = {
    'newline': "<br>\n",
    'nbsp': "&nbsp;&nbsp;",
    "simple": '<$1>$2</$1>',
    "position": '<div align="$1">$2</div>',
    "url": '<a href="$1">$1</a>',
    "url=": '<a href="$1">$6</a>',
    'auto_url': '$1<a href="$2">$2</a>$7',
    "size": '<font face=" " style="font-size: $1$2;">$3</font>',
    "color": '<font face=" " style="color: #$1">$2</font>'
};

html_patterns = {
    'newline': /<br\/?>\n?/,
    'nbspd': /\&nbsp\;\&nbsp\;/,
    'nbsp': /\&nbsp\;/,
    "code": /<div class=%Q;code_wysiwyg%Q;>\s*<span>(?:[\s\S]+?)(js|html|php|css|java|delphi|cs|cpp|ls)?\:<\/span>\s*<code>([\s\S]+?)<\/code>\s*<\/div>/,
    "hide": /<div class=%Q;hidden_wysiwyg%Q;>[\s\S]*?(?:<span\s+class=%Q;mc_wysiwyg%Q;>[\s\S]*?\:\s*([0-9]+)<\/span>)?\s*(?:<span\s+class=%Q;gi_wysiwyg%Q;>[\s\S]*?\:\s*([0-9\,]+)<\/span>)?\s*<div>([\s\S]+?)<\/div>\s*<span class=%Q;hdwsw%Q;><\/span>\s*<\/div>/,
    "img": /<img([\s\S]+?)\/?>/,
    "list": /<(?:(ul)(?:\s+type=%Q;(disc|circle|square)%Q;)?|(ol)(?:\s+type=%Q;([aAiI1])%Q;)?(?:\s+start=%Q;([0-9]+)%Q;)?)>([\s\S]*?)<\/(\1|\3)>/gi,
    "simple": /<(b|i|u|s|p|su[bp]|strong|strike|em)>([\s\S]*?)<\/\1>/,
    "position": /<div\s+align=%Q;(left|right|center|justify)%Q;>([\s\S]*?)<\/div>/,
    "url": /<a href=%Q;%URL_PATTERN;%Q;>\1<\/a>/,
    "url=": /<a href=%Q;%URL_PATTERN;%Q;>([\s\S]*?)<\/a>/,
    "font": /<font([\s\S]+?)>([\s\S]*?)<\/font>/,
    "other": /<div class=%Q;other_wysiwyg%Q;>[\s\S]*?(?:<span>([\s\S]*?)<\/span>)?\s*<div class=%Q;([\s\S]*?)%Q;>([\s\S]*?)<\/div><span class=%Q;\2wsw%Q;><\/span><\/div>/,
    "remfail": /<([a-z]+?)(?:[\s\S]*?)>([\s\S]*?)<\/\1>/
//"remempty" : /\[([a-z]+?)(?:[\s\=][\s\S]*?)?\]([\s\t\n\r]*)\[\/\1\]/
};

html_replacement = {
    'newline': "\r\n",
    'nbspd': "\t",
    'nbsp': " ",
    "simple": '[$1]$2[/$1]',
    "position": '[$1]$2[/$1]',
    "url": '[url]$1[/url]',
    "url=": '[url="$1"]$6[/url]',
    'remfail': '$2'
//'remempty' : ''
};

/**
 * HTML->BBCode для тега font
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_font(patt, text) {
    var cb = function() {
        var m = arguments;
        var attrs = m[1];
        var txt = m[2];
        var c, s = "";
        s = attrs.match(prepare_pattern(/(?:^|\s+)style=%Q;.*?font-size:\s*([0-9\.]+)(px|pt|\%)\;?.*?%Q;(?:$|\s+)/, false, true));
        if (s)
            s = s[1] + s[2];
        c = attrs.match(prepare_pattern(/(?:^|\s+)style=%Q;.*?color:\s*([^;"']+);?.*?%Q;(?:$|\s+)/, false, true));
        if (c)
            c = rgb2hex(c[1]);
        if (!s && !c)
            return "";
        if (s)
            txt = "[size=\"" + s + "\"]" + txt + "[/size]";
        if (c)
            txt = "[color=\"" + c + "\"]" + txt + "[/color]";
        return txt;
    }
    var ot;
    do {
        ot = text;
        text = text.replace(patt, cb);
    } while (ot != text);
    return text;
}

/**
 * Преобразование RGB в HEX
 * @param {string} rgb_in исходный цвет в RGB
 * @returns {int} цвет в HEX
 */
function rgb2hex(rgb_in) {
    var rgb = rgb_in.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!rgb) {
        if (rgb_in[0] == "#")
            rgb_in = rgb_in.substr(1);
        return rgb_in;
    }
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

/**
 * BBCode->HTML для тега img
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_img(patt, text) {
    var cb = function() {
        var m = arguments;
        var alt = m[1];
        var w = m[2];
        var h = m[3];
        var a = m[4];
        var s = m[5];
        return "<img" + (alt ? " alt=\"" + alt + "\" title=\"" + alt + "\"" : " alt=\"\"") +
                (w ? " width=\"" + w + "\"" : "") +
                (h ? " height=\"" + h + "\"" : "") +
                (a ? " align=\"" + a + "\"" : "") + " src=\"" + s + "\">";
    }
    return text.replace(patt, cb);
}

/**
 * HTML->BBCode для тега img
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_img(patt, html) {
    var cb = function() {
        var m = arguments;
        var attrs = m[1];
        var s, t, a, w, h = "";
        s = attrs.match(prepare_pattern(/(?:^|\s+)src=%Q;%URL_PATTERN;%Q;(?:$|\s+)/, false, true));
        if (!s)
            return "";
        s = s[1];
        t = attrs.match(prepare_pattern(/(?:^|\s+)title=%Q;([\s\S]*?)%Q;(?:$|\s+)/, false, true));
        if (t)
            t = t[1];
        a = attrs.match(prepare_pattern(/(?:^|\s+)align=%Q;(bottom|left|middle|right|top)%Q;(?:$|\s+)/, false, true));
        if (a)
            a = a[1];
        w = attrs.match(prepare_pattern(/(?:^|\s+)width=%Q;([0-9]+)%Q;(?:$|\s+)/, false, true));
        if (w)
            w = w[1];
        h = attrs.match(prepare_pattern(/(?:^|\s+)height=%Q;([0-9]+)%Q;(?:$|\s+)/, false, true));
        if (h)
            h = h[1];
        return "[img" + (t ? "=\"" + t + "\"" : "") +
                (w ? " w=\"" + w + "\"" : "") +
                (h ? " h=\"" + h + "\"" : "") +
                (a ? " " + a : "") + "]" + s + "[/img]";
    }
    return html.replace(patt, cb);
}

/**
 * BBCode->HTML для тега list
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_list(patt, text) {
    var cb = function() {
        var m = arguments;
        var l = (m[1] || !m[2] ? "u" : "o");
        var t = m[1] || m[2];
        var s = m[3];
        var txt = (m[4] + '').replace(/\[\*\]/g, "<li>");
        return "<" + l + "l" + (t ? " type='" + t + "'" : "") + (s ? " start='" + s + "'" : "") + ">" + txt + "</" + l + "l>";
    }
    return text.replace(patt, cb);
}

/**
 * HTML->BBCode для тега list
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_list(patt, html) {
    var cb = function() {
        var m = arguments;
        var t = m[2] || m[4];
        var s = m[5];
        var txt = (m[6] + '').replace(/\<\/li\>/g, "").replace(/\<li\>/g, "[*]");
        return "[list" + (t ? "=\"" + t + "\"" : "") + (s ? " s=\"" + s + "\"" : "") + "]" + txt + "[/list]";
    }
    return html.replace(patt, cb);
}

/**
 * Замена скобочек их аналогами для HTML
 * @param {string} text исходный текст
 * @param {bool} from из HTML?
 * @returns {string} обработанный текст
 */
function replace_scrapes(text, from) {
    var s_d = ["[", "]"];
    var s_e = ["&#91;", "&#93;"];
    for (var i = 0; i < 2; i++)
        text = text.replace(new RegExp(regex_quote((from ? s_e : s_d)[i]), "g"), ((!from ? s_e : s_d)[i]));
    return text;
}

/**
 * BBCode->HTML для тега code
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_code(patt, text) {
    var cb = function() {
        var m = arguments;
        var txt = replace_scrapes(m[2]);
        var type = m[1];
        return "<div class=\"code_wysiwyg\"><span>" +
                lang_bbcodes["code"] + (type ? " " + type.toUpperCase() : "") +
                ":</span><code>" + txt + "</code></div>";
    }
    return text.replace(patt, cb);
}

/**
 * HTML->BBCode для тега code
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_code(patt, html) {
    var cb = function() {
        var m = arguments;
        var txt = replace_scrapes(m[2], true);
        var type = m[1];
        return "[code" + (type ? "=\"" + type.toUpperCase() + "\"" : "") + "]" + txt + "[/code]";
    }
    return html.replace(patt, cb);
}

/**
 * BBCode->HTML для тега hide
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_hide(patt, text) {
    var cb = function() {
        var m = arguments;
        var txt = m[3];
        var mc = m[1];
        var gi = m[2];
        return "<div class=\"hidden_wysiwyg\">" +
                lang_bbcodes["hide"] + (mc ? " <span class=\"mc_wysiwyg\">" + lang_bbcodes["mc"] + mc + "</span>" : "") +
                (gi ? " <span class=\"gi_wysiwyg\">" + lang_bbcodes["gi"] + gi + "</span>" : "") + "<div>" + txt + "</div><span class='hdwsw'></span></div>";
    }
    return text.replace(patt, cb);
}

/**
 * HTML->BBCode для тега hide
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_hide(patt, html) {
    var cb = function() {
        var m = arguments;
        var txt = m[3];
        var mc = m[1];
        var gi = m[2];
        return "[hide" + (mc ? "=\"" + mc + "\"" : "") + (gi ? " g=\"" + gi + "\"" : "") + "]" + txt + "[/hide]";
    }
    return html.replace(patt, cb);
}

/**
 * BBCode->HTML для смайлов
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_smilies(text) {
    var s = smilies_array;
    var nbsp = regex_quote('&nbsp;');
    for (var i in s) {
        var c = s[i];
        text = text.replace(new RegExp('(?:' + nbsp + '|\\s|^)' + regex_quote(replace_quotes(trim(c.code))) + '(?:' + nbsp + '|\\s|$)', "gi"),
                "<img alt='" + c.code + "' src='" + smilies_src + c.image + "' title='" + c.name + "'>");
    }
    return text;
}

/**
 * HTML->BBCode для смайлов
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_smilies(html) {
    var s = smilies_array;
    for (var i in s) {
        var c = s[i];
        html = html.replace(new RegExp(
                "<img\\s+alt=('|\")" +
                regex_quote(c.code) + "('|\")\\s+src=('|\")(?:.*?)" +
                regex_quote(c.image) + "('|\").*?>",
                'gim'), ' ' + c.code + ' ');
    }
    return html;
}

/**
 * BBCode->HTML для остальных тегов
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function decode_other(patt, text) {
    for (var i = 0; i < other_tags.length; i++) {
        var c = other_tags[i];
        var a = "";
        var tp = 1;
        if (c[c.length - 1] == "=") {
            c = c.substr(0, c.length - 1);
            a = '(?:=%Q;(.*?)%Q;)?';
            tp = 2;
        }
        var repl = "<div class='other_wysiwyg'>" + lang_bbcodes[c] + (a ? (lang_bbcodes[c + "="] ? lang_bbcodes[c + "="] : "") + " <span>$1</span>" : "") + "<div class='" + c + "'>$" + tp + "</div><span class='" + c + "wsw'></span></div>";
        patt = prepare_pattern(new RegExp('\\[' + c + a + '\\]([\\s\\S]*?)\\[\\/' + c + '\\]'), true);
        if (multi_tagin[c]) {
            var ot;
            do {
                ot = text;
                text = text.replace(patt, repl);
            } while (ot != text);
        } else
            text = text.replace(patt, repl);
    }
    return text;
}

/**
 * HTML->BBCode для остальных тегов
 * @param {object} patt паттерн
 * @param {string} text исходный текст
 * @returns {string} обработанный текст
 */
function encode_other(patt, html) {
    var cb = function() {
        var m = arguments;
        var txt = m[3];
        var tag = m[2];
        var params = m[1];
        return "[" + tag + (params ? "=\"" + params + "\"" : "") + "]" + txt + "[/" + tag + "]";
    }
    var ot;
    do {
        ot = html;
        html = html.replace(patt, cb);
    } while (ot != html);
    return html;
}

prepared_bbcode = false;
prepared_htmlcode = false;

/**
 * Замена " и ' в паттерне
 * @param {string} patt паттерн
 * @returns {string} обработанный паттерн
 */
function replace_quotes(pattern) {
    var $q = regex_quote(html_encode('"'));
    var $sq = regex_quote(html_encode("'"));
    pattern = pattern.replace(new RegExp(regex_quote('"'), "g"), $q);
    pattern = pattern.replace(new RegExp(regex_quote("'"), "g"), $sq);
    return pattern;
}

/**
 * Предобработка паттернов
 * @param {object} patt паттерн
 * @param {bool} encoded HTML?
 * @param {bool} not_global без модификатора "g"
 * @returns {object} обработанный паттерн
 */
function prepare_pattern(pattern, encoded, not_global) {
    var $q = regex_quote(encoded ? html_encode('"') : '"');
    var $sq = regex_quote(encoded ? html_encode("'") : "'");
    pattern = pattern.source;
    pattern = pattern.replace(new RegExp(regex_quote("%URL_PATTERN;"), "g"), URL_PATTERN.source);
    if (encoded)
        replace_quotes(pattern);
    pattern = pattern.replace(new RegExp(regex_quote("%Q;"), "g"), '(?:' + $q + '|' + $sq + ')?');
    return new RegExp(pattern, 'i' + (!not_global ? 'g' : ""));
}

// Костыли начинаются тут.

/**
 * Предобработка HTML для браузеров
 * @param {string} html HTML код
 * @returns {string} обработанный код
 */
function prepare_html(html) {
    var d = document.createElement("div");
    d.innerHTML = html;
    var obj = jQuery(d);
    jQuery("*:not(font)", obj).removeAttr("style"); // Для гугль-хрома
    // Для ФигеФох. Бегинс.
    jQuery("font > font", obj).each(function() {
        var t = jQuery(this);
        var c = t.css("color");
        var s = t.css("font-size");
        var tp = t.parent();
        if (c)
            tp.css("color", c);
        if (s)
            tp.css("font-size", s);
        tp.html(t.html());
    });
    // Эндс.
    return obj.html();
}

// И заканчиваются тут.

/**
 * Преобразование текста из HTML в BBCode и наоборот
 * @param {string} text исходный текст
 * @param {bool} h2b HTML->BBCode?
 * @param {bool} not_encodes не экранировать?
 * @returns {string} обработанный текст
 */
function convert_bh(text, h2b, not_encodes) {
    //text = trim(text);
    if (!h2b && !not_encodes)
        text = html_encode(text);
    var from = h2b ? html_patterns : bb_patterns;
    var to = h2b ? html_replacement : bb_replacement;
    var prefix = h2b ? 'en' : 'de';
    if (h2b)
        text = prepare_html(text);
    text = trim(window[(prefix + 'code_smilies')](text));
    for (var t in from) {
        var cur = from[t];
        if (cur && typeof cur != "string") {
            if (h2b && !prepared_htmlcode)
                html_patterns[t] = cur = prepare_pattern(cur, false);
            else if (!h2b) {
                cur = prepare_pattern(cur, !not_encodes);
            }
        }
        if (typeof to[t] != "undefined") {
            if (multi_tagin[t]) {
                var i = 0;
                do {
                    var ot = text;
                    text = text.replace(cur, to[t]);
                    i++;
                } while (ot != text && i < 100);
            } else
                text = text.replace(cur, to[t]);
        } else if (jQuery.isFunction(window[prefix + 'code_' + t]))
            text = window[(prefix + 'code_' + t)](cur, text);
    }
    if (h2b && !not_encodes)
        text = html_decode(text);
    if (h2b)
        prepared_htmlcode = true;
    if (!h2b)
        text = prepare_html(text);
    return text;
}