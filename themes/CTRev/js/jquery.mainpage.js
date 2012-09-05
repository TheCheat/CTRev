// some(four, may be?) functions getted from phpjs.org
// Opacity Emulator, to be a valid CSS

$popup_id = "";
lswillbehidden = false;
ehtml_code_array = ["&amp;", "&lt;", "&gt;", "&#39;", "&quot;", "&nbsp;&nbsp;"];
dhtml_code_array = ["&", "<", ">", "'", '"', '  '];
previous_tooltip_obj = null;
from_transl = ["а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я"," ","-"];
to_transl = ["a","b","v","g","d","e","e","zh","z","i","y","k","l","m","n","o","p","r","c","t","u","f","h","c","ch","sh","sch","","y","","e","u","ya","_","-"];

/**
 * Транслитерация строки
 * @param text string входящая строка
 * @return string исходящая строка
 */
function translite(text) {
    var newtext = "";
    var p = 0;
    var up = false;
    for (var i=0; i<text.length; i++) {
        if ((text[i] > '0' && text[i] < '9') || (text[i].toLowerCase() > 'a' && text[i].toLowerCase() < 'z')) {
            newtext += text[i];
            continue;
        }
        up = false;
        if (text[i].toLowerCase() != text[i])
            up = true;
        p = from_transl.indexOf(text[i].toLowerCase());
        if (!p)
            continue;
        newtext += up?to_transl[p].toUpperCase():to_transl[p];
    }
    return newtext;
}
/**
 * Экранирование строки для рег. выражния
 * @param str string входящая строка
 * @param delimiter string делимиттер рег. выражения
 * @return string исходящая строка
 */
function regex_quote (str, delimiter) {
    return (str+'').replace(new RegExp('[\\.\\+\\*\\?\\[\\^\\]\\$\\(\\)\\{\\}\\=\\!\\<\\>\\|\\:\\' +
        (delimiter || '/') + ']', 'g'), '\\$&');
}
/**
 * Деэкранирование HTML символов в строке
 * @param str string входящая строка
 * @return string исходящая строка
 */
function html_decode(text) {
    for (var i = 0; i < ehtml_code_array.length; i++)
        text = text.replace(new RegExp(regex_quote(ehtml_code_array[i]),"g"),dhtml_code_array[i]);
    return text;
}
/**
 * Экранирование HTML символов в строке
 * @param str string входящая строка
 * @return string исходящая строка
 */
function html_encode(html) {
    for (var i = 0; i < ehtml_code_array.length; i++)
        html = html.replace(new RegExp(regex_quote(dhtml_code_array[i]),"g"),ehtml_code_array[i]);
    return html;
}
/**
 * Обрезание пробелов строки с обоих сторон
 * @param str string входящая строка
 * @return string исходящая строка
 */
function trim(str) {
    return ltrim(rtrim(str));
}
/**
 * Обрезание пробелов строки слева
 * @param str string входящая строка
 * @return string исходящая строка
 */
function ltrim(str) {
    return (str+'').replace(/^([\s\n\t\r]+)/g, "");
}
/**
 * Обрезание пробелов строки справа
 * @param str string входящая строка
 * @return string исходящая строка
 */
function rtrim(str) {
    return (str+'').replace(/([\s\n\t\r]+)$/g, "");
}
/**
 * Эмулятор прозрачности
 * @param $element object объект для эмуляции
 * @param $now_opacity float данная прозрачность
 * @param $animate bool анимировать?
 * @return null
 */
function opacity_emulator($element, $now_opacity, $animate) {
    var $css_obj = {
        "opacity" : $now_opacity,
        "filter" : "alpha(opacity=" + ($now_opacity * 100) + ")",
        "-ms-filter" : "progid:DXImageTransform.Microsoft.Alpha(Opacity=" + ($now_opacity * 100) + ")",
        "-moz-opacity" : $now_opacity,
        "-khtml-opacity" : $now_opacity
    };
    if (!$animate)
        jQuery($element).css($css_obj);
    else
        jQuery($element).stop().animate($css_obj);
}
/**
 * Инициализация действия при наведении на dd
 * @return null
 */
function onhovered_dd() {
    jQuery(document).ready(function() {
        jQuery("dl.info_text dd:not(.inited_onhover)")
        .addClass('inited_onhover').hover(function() {
            jQuery(this).prev().removeClass("hovered");
            jQuery(this).prev().addClass("hovered");
        }, function() {
            jQuery(this).prev().removeClass("hovered");
        });
    });
}
/**
 * Инициализация прозрачности
 * @return null
 */
function init_opacity() {
    jQuery(document).ready(function() {
        opacity_emulator(".tabs-nav li.tabs-disabled", 0.4);
    });
}

/**
 * Открытие модального окна
 * @param id int ID окна
 * @param color string цвет окна
 * @return null
 */
function init_popup(id, color) {
    jQuery(document).ready(function() {
        if ($popup_id == 'loading_container')
            close_popup();
        else if ($popup_id && id == 'loading_container')
            return;
        $popup_id = id;
        if (!jQuery('#'+id).is('.popup_container')) {
            if (!color)
                color = 'white_color';
            jQuery('#'+id).addClass('popup_container')
            .wrapInner('<div class="cornerText '+color+'"/>')
            .wrapInner('<div class="popup_content"/>')
            .wrapInner('<div class="popup_screen"/>')
            .prepend('<div class="popup_screen_oc"/>');
            init_corners();
        }
        opacity_emulator(jQuery("#" + id + " .popup_screen_oc"), '0.75');
        jQuery("#" + id).appendTo('body');
        jQuery("#" + id).show();
        var $ps = jQuery("#" + id + " .popup_screen");
        var $pc = jQuery("#" + id + " .popup_content");
        $ps.css({
            "margin-left" : "-" + parseInt($pc.width() / 3) + "px",
            "margin-top" : "-" + parseInt($pc.height() / 3) + "px"
        });
    });
}
/**
 * Закрытие модального окна
 * @return null
 */
function close_popup() {
    if (typeof ($popup_id) == "undefined")
        return;
    jQuery("#" + $popup_id).hide();
    $popup_id = "";
}
/**
 * Инициализация кнопки закрытия модального окна
 * @param $onclose callback функция по закрытию
 * @return null
 */
function init_modalbox_close($onclose) {
    if (typeof ($popup_id) == "undefined")
        return;
    jQuery("#" + $popup_id + " .modalbox_title").append('<div class="modalbox_close"></div>');
    jQuery("#" + $popup_id + " .modalbox_close").bind("click", function() {
        if (!$popup_id)
            return;
        if (jQuery.isFunction($onclose)) {
            if ($onclose($popup_id) === false)
                return;
        }
        close_popup();
    });
}
/**
 * Предварительно не открывать loading_container
 * @return null
 */
function prehide_ls() {
    lswillbehidden = true;
}
/**
 * Открытие loading_container
 * @return null
 */
function show_ls() {
    if (!lswillbehidden) {
        init_corners();
        init_popup('loading_container');
    }
    lswillbehidden = false;
}
/**
 * Закрытие loading_container
 * @return null
 */
function hide_ls() {
    if ($popup_id && $popup_id != 'loading_container')
        return;
    $popup_id = 'loading_container';
    close_popup();
}
/**
 * Отображение иконки статуса
 * @param id string|object ID иконки статуса или её объект
 * @param act string статус(loading,loading_white,success,error)
 * @param data string данные после иконки
 * @return null
 */
function status_icon(id, act, data) {
    if (id[0]=='#')
        id = id.substr(1);
    var $si = jQuery('#'+id);
    $si.empty();
    $si.attr("class", "status_icon");
    if (act) {
        $si.addClass("status_icon_"+act);
        $si.show();
        $si.append(data?data:"");
    } else
        $si.hide();
}
/**
 * Функция входа пользователя
 * @param $file string URL для посылки формы
 * @param $form object объект формы
 * @param $si object объект иконки загрузки
 * @param $referer string URL реферера
 * @return null
 */
function login($file, $form, $si, $referer) {
    status_icon($si, 'loading');
    jQuery.post($file, jQuery($form).serialize(), function(data) {
        if (data == "OK!") {
            status_icon($si, 'success');
            if (!$referer)
                setTimeout("window.location = baseurl", 1000);
            else
                setTimeout("window.location = '"
                    + ($referer + '').replace(/([\\"'])/g, "\\$1").replace(/\u0000/g, "\\0") + "'", 1000);
        } else
            status_icon($si, 'error', data);
    });
    return;
}
/**
 * Установка позиции объект по центру от родителя
 * @param $object object данный объект
 * @param $parent object родительский объект
 * @return null
 */
function position_obj($object, $parent) {
    $object.css({
        "position" : "absolute",
        "z-index" : 10,
        "left" : $parent.offset().left - $object.width()/1.5 + $parent.width(),
        "top" : $parent.offset().top + $parent.height()
    });
    jQuery(window).bind("resize", {
        "object" : $object,
        "parent" : $parent
    }, function() {
        position_obj($object, $parent);
    });
}
/**
 * Отображение меню для BB-Кодов
 * @param obj object объект кнопки/ссылки
 * @param ul2div bool искать контент в DIV?
 * @param taked object объект меню
 * @return null
 */
function toggle_menu(obj, ul2div, taked) {
    if (!taked) {
        var $obj = jQuery(obj);
        var $sobj, $menu;
        if (!$obj.is("a"))
            $sobj = $obj.parent("a");
        else
            $sobj = $obj;
        if (!ul2div)
            $menu = $sobj.parent("div").children("ul");
        else
            $menu = $sobj.parent("div").children("div.menu");
        $menu.addClass("toggledMenu");
        $obj.attr("onclick", "");
        $obj.unbind("click");
        $menu.unbind("mouseleave");
        $obj.bind("click", {
            menu : $menu,
            sobj : $sobj
        }, function(event) {
            toggle_menu(event.data.menu, false, event.data.sobj);
        });
        $menu.appendTo("body");
    } else {
        $menu = obj;
        $sobj = taked;
    }
    $menu.addClass("this_toggle_menu");
    /* Чистим все запущенные toggle */
    jQuery(".toggledMenu:not(.this_toggle_menu)").hide();
    position_obj($menu, $sobj);
    $menu.slideToggle('fast');
    $menu.removeClass("this_toggle_menu");
}
/**
 * Установка значения рейтинга
 * @param value float значение рейтинга
 * @param toid int ID ресурса
 * @param type string тип ресурса
 * @param element object|callback элемент, куда будет подставлено значение из AJAX
 * или функция, которая всё сама сделает{@see function rating_sel()}
 * @param refresh bool обновить значение?
 * @param stoid int доп. ID ресурса(см. реализацию в PHP)
 * @param stype string доп тип ресурса(см. реализацию в PHP)
 * @return null
 */
function set_rating(value, toid, type, element, refresh, stoid, stype) {
    jQuery.post(baseurl + 'index.php?module=rating_manage&act=vote&from_ajax=true', {
        "value" : value,
        "toid" : toid,
        "type" : type,
        "stoid" : stoid,
        "stype" : stype
    }, function(data) {
        if (data == "OK!") {
            if (jQuery.isFunction(element)) {
                if (refresh) {
                    jQuery.post(baseurl + 'index.php?module=rating_manage&act=get&from_ajax=true', {
                        "toid" : toid,
                        "type" : type
                    }, function($data) {
                        if (parseFloat($data)!='NaN')
                            element(parseFloat($data), value, toid, type);
                    });
                } else {
                    element(value, toid, type);
                }
            } else if (jQuery(element).length) {
                var $el = jQuery(element);
                if (refresh) {
                    jQuery.post(baseurl + 'index.php?module=rating_manage&act=get&from_ajax=true', {
                        "toid" : toid,
                        "type" : type
                    }, function($data) {
                        $el.empty();
                        $el.append($data);
                    });
                } else {
                    var $text = $el.text();
                    if (parseFloat($text)!='NaN') {
                        $el.empty();
                        $el.append(parseFloat($text) + parseFloat(value));
                    }
                }
            }
            else {
                return;
            }
        } else
            alert(error_text+": "+data);
    });
}

/**
 * Выборка рейтинга
 * @param $ret string то, что вернулось из AJAX
 * @param $val float значение рейтинга
 * @param $rid int ID ресурса
 * @param $rtype string тип ресурса
 * @return null
 */
function rating_sel($ret, $val, $rid, $rtype) {
    jQuery("input.rating" + $rid + $rtype).rating("select", "" + $ret, false);
    jQuery("input.rating" + $rid + $rtype).rating("readOnly", true);
    alert(success_text + "!");
}

/**
 * Очистка поля select на значении {@link $val}
 * @param $this object данное поле
 * @param $val mixed значение для очистки
 * @return null
 */
function clear_select($this, $val) {
    var notselect = 0;
    if (!$val)
        $val = 0;
    $val = ''+$val;
    jQuery($this).children().each(function() {
        var v = $(this).attr("value");
        if (v == $val && $(this).attr("selected"))
            notselect = 1;
        if (notselect) {
            if (v != $val)
                $(this).removeAttr("selected");
            else
                $(this).attr("selected", "selected");
        }
    });
}
/**
 * Выборка всех чекбоксов
 * @param $this object данный чекбокс
 * @param $el object все остальные
 * @return null
 */
function select_all($this, $el) {
    $el = jQuery($el);
    var $val = jQuery($this).attr("checked");
    $el.each(function() {
        if (!$val)
            jQuery(this).removeAttr("checked");
        else {
            jQuery(this).attr("checked", 'checked');
        }
    });
}
/**
 * Инициализация сортировки таблицы
 * @param rf callback своя функция для удалённой сортировки(не remote_ts_location)
 * @return null
 */
function init_tablesorter(rf) {
    jQuery(document).ready(function($) {
        if (!rf && typeof remote_ts_location != "undefined")
            rf = remote_ts_location;
        var $o = $("table.tablesorter:not(.inited_ts)");        
        if (typeof $o.tablesorter !== 'undefined')
            $o.tablesorter({
                widgets : ['zebra'],
                widthFixed : true,
                remote_function : rf
            }).addClass('inited_ts');
        remake_odd_ts();
    });
}
/**
 * Перерисовка таблицы-"зебры"
 * @return null
 */
function remake_odd_ts() {
    jQuery(document).ready(function($) {
        var $num = 0;
        $("table.tablesorter:not(.not_auto_odd) tbody tr").each(function() {
            jQuery(this).removeClass("odd");
            if ($num % 2 != 0) {
                jQuery(this).addClass("odd");
            }
            $num++;
        });
    });
}
/**
 * Инициализация табов
 * @param $name string класс табов
 * @param $sub string выбранный таб
 * @param $noremote bool не удалённые?
 * @return null
 */
function init_tabs($name, $sub, $noremote) {
    if (!$name)
        $name = "ajax_tabs";
    if (!$sub)
        $sub = {};
    if (!$noremote)
        $sub.remote = true;
    $sub.spinner = loading_text + '&#8230;';
    jQuery(document).ready(function($) {
        $("div." + $name).tabs($sub);
    });
}
/**
 * Счётчик ЛС на главной странице
 * @return null
 */
function get_index_msgs() {
    prehide_ls();
    jQuery.post(baseurl + 'index.php?module=ajax_index&act=get_msgs&from_ajax=1', function(data) {
        jQuery('#ajax_index_msgs').empty();
        jQuery('#ajax_index_msgs').append(data);
    });
}
/**
 * Изменение картинки закладки
 * @param $id int ID ресурса
 * @return null
 */
function toggle_bookmark($id) {
    jQuery('img#bookmark_add_' + $id).parent("a").toggleClass('hidden');
    jQuery('img#bookmark_del_' + $id).parent("a").toggleClass('hidden');
}
/**
 * Добавление закладки
 * @param $id int ID ресурса
 * @param $type string тип ресурса
 * @return null
 */
function add_bookmark($id, $type) {
    jQuery.post(baseurl + 'index.php?module=usercp&act=add_bookmark&from_ajax=1', {
        "toid" : $id,
        "type" : $type
    }, function(data) {
        if (data == "OK!") {
            toggle_bookmark($id);
            alert(success_text + "!");
        } else
            alert(error_text + "! " + data);
    });
}
/**
 * Удаление закладки
 * @param $id string ID закладки
 * @param $hide bool true, если надо скрыть строку в таблице
 * @return null
 */
function delete_bookmark($id, $hide) {
    if (!confirm(are_you_sure_to_delete_this_bookmark))
        return;
    var $params = {};
    $params.id = $id;
    if (typeof $hide == 'string')
        $params.type = $hide;
    jQuery.post(baseurl + 'index.php?module=usercp&act=delete_bookmark&from_ajax=1', $params, function(data) {
        if (data == "OK!") {
            if (typeof $hide != 'string' && $hide) {
                jQuery("#usercp_bookmark_" + $id).children("td").fadeOut(2000, function() {
                    jQuery(this).parent().remove();
                });
            }
            toggle_bookmark($id);
            alert(success_text + "!");
        } else
            alert(error_text + "! " + data);
    });
}

/**
 * Предвратиельный поиск
 * @param $element object объект поля поиска
 * @return null
 */
function pre_search($element) {
    var $child = null;
    $element
    .keyup(function(key) {
        if (!$element.val())
            return;
        if (key.keyCode == 116 || key.keyCode == 16 || key.keyCode == 17 || key.keyCode == 37
            || key.keyCode == 38 || key.keyCode == 39 || key.keyCode == 35)
            return;
        if (!$element.parent().is('.fast_search')) {
            var $par = $element.parent().addClass("fast_search");
            $par
            .append("<div class='dropdown'><div class='cornerText js_notop'></div><div class=\"close_block\" align=\"right\"><a class=\"close_button\" href=\"javascript:void(0);\"><img src=\""
                + theme_path + "engine_images/delete.png\" alt=''></a></div></div>");
            $child = $par.children('div.dropdown');
            position_obj($child, $element);
            $child.appendTo("body");
            $child.children("div.close_block").children("a.close_button").click(function() {
                $child.parent(".dropdown").hide();
            });
            $child = $child.children('div.cornerText');
            init_corners();
        }
        $child.parent(".dropdown").show();
        $child.empty().append(loading_text + "...");
        jQuery.post(baseurl + 'index.php?module=ajax_index&act=search_pre&from_ajax=1', {
            "text" : $element.val()
        }, function(data) {
            data += '';
            $child.empty().append(data);
        });
    });
}

/**
 * Смена картинки спойлера и разворачивание/сворачивание его
 * @param $icon object объект картинки
 * @param $object object объект того, что сворачиванием/разворачиваем
 * @return null
 */
function spoiler_pic($icon, $object) {
    if ($object.css('display') == "none")
        $icon.css("background-image", "url('" + theme_path + "engine_images/sp_minus.gif')");
    else
        $icon.css("background-image", "url('" + theme_path + "engine_images/sp_plus.gif')");
    $object.slideToggle('fast');
}

/**
 * Инициализация спойлеров
 * @return null
 */
function spoiler_init() {
    jQuery(document).ready(function($) {
        $('.spoiler:not(.inited_spoiler) .spoiler_title').click(function() {
            var $parent = $(this).parent('.spoiler');
            var $content = $parent.children('.spoiler_content');
            spoiler_pic($(this).children('.spoiler_icon'), $content);
        }).parent('.spoiler').addClass('inited_spoiler');
        // инициализируем и хайлайт синтаксиса
        var $o = $('div.syntaxhighlighter pre:not(.inited_highlight)');
        if (typeof $o.chili !== 'undefined')
            $o.chili().addClass('inited_highlight');
    });
}

/**
 * Открытие тултипа
 * @param obj object объект ссылки
 * @param what object то, что будем отображать, должен быть среди родителей {@link obj}
 * @return null
 */
function tooltip_open(obj, what) {
    obj = jQuery(obj);
    var p=obj;
    do  {
        p=p.parent();
    } while (!jQuery(what, p).length && !p.is('body'));
    if (p.is('body'))
        return;
    var e = jQuery(what, p);    
    if (jQuery('div.tooltip').length) {
        var eh = e.html();
        close_tooltip();
        if (!eh)
            return;
    }
    var w = jQuery('div.tooltip_html',e);
    if (!w.length)
        w = e.wrapInner('<div class="tooltip_html"/>').children();
    previous_tooltip_obj = e;
    var tooltip = jQuery('<div class="tooltip">'
        +'<div class="tooltip_content">'
        +'<div class="tooltip_tail"></div>'
        +'</div>'
        +'</div>');
    var tc = jQuery('div.tooltip_content', tooltip);
    w.prependTo(tc);
    tooltip.appendTo('body');
    var left = obj.eq(0).offset().left;
    var top = obj.eq(0).offset().top;
    var th = jQuery('div.tooltip_html', tc);
    left -= th.outerWidth()/2-obj.outerWidth()/2;
    top -= th.outerHeight()+obj.outerHeight()+4;
    tooltip.css({
        'position': 'absolute',
        'z-index': 15,
        'left': left+'px',
        'top': top+'px'
    });
    opacity_emulator(tooltip.eq(0), '0.9', true);
}

/**
 * Закрытие тултипа
 * @return null
 */
function close_tooltip() {
    if (!previous_tooltip_obj || !previous_tooltip_obj.length)
        return;
    jQuery('div.tooltip div.tooltip_html').appendTo(previous_tooltip_obj);
    jQuery('div.tooltip').remove();
    previous_tooltip_obj = null;
}


/**
 * Сворачивание блока торрента
 * @param obj object объект ссылки
 * @return null
 */
function close_torrent(obj) {
    obj = jQuery(obj);
    var img = obj.children('img');
    if (img.attr("src").match(/close\.([a-z]+)$/i))
        img.attr("src", img.attr("src").replace(/close\.([a-z]+)$/i, 'open.$1'));
    else
        img.attr("src", img.attr("src").replace(/open\.([a-z]+)$/i, 'close.$1'));
    obj = obj.parents('div.center_block').children("div.center_block_content");
    obj.slideToggle("fast");
}

/**
 * Сохранение в подписках
 * @param id int ID ресурса
 * @param type string тип ресурса
 * @param interval int интервал подписки
 * @return null
 */
function make_mailer(id, type, interval) {
    jQuery.post(baseurl+"index.php?module=usercp&from_ajax=1&act=make_mailer",
    {
        "id":id,
        "type":type,
        "interval":interval,
        "upd":(interval?1:0)
    }, function (data) {
        if (data == "OK!") {
            alert(success_text + "!");
        } else
            alert(error_text + ": " + data + "!");
    });
}
/**
 * Экранирование для JavaScript
 * @param str string входящая строка
 * @return string исходящая строка
 */
function addslashes (str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

/**
 * Кодирование строки в URL
 * @param str string входящая строка
 * @return string исходящая строка
 */
function urlencode (str) {
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}
/**
 * Открытие окна
 * @param url string URL после {@link file}
 * @param file string URL для файла(по-умолчанию index.php?window=1&)
 * @return null
 */
function default_windopen(url, file) {
    if (!file)
        file = 'index.php?window=1&';
    window.open(baseurl+file+url,
        'child'+(new Date()).getTime(), 'width=800,height=500,scrollbars=yes');
}
/**
 * Открытие формы поиска пользователя
 * @param form string имя формы
 * @param username_field string имя поля логина
 * @param arr object ассоциативный массив для поиска
 * @return null
 */
function open_searchuwind(form, username_field, arr) {
    if (!arr)
        arr = {
            "user":jQuery('form[name='+form+'] input[name='+username_field+']').val()
        };
    var serialized = "&user="+urlencode(arr.user)
    +(arr.email?"&email="+urlencode(arr.email):"")
    +(arr.ip?"&ip="+urlencode(arr.ip):"");
    default_windopen('module=search_module&act=user&form='+form+'&field='+username_field+serialized);
    
}
/**
 * Отображение изображения флага
 * @param path string путь к изображениям
 * @param obj object объект селектора
 * @param to string ID изображения
 * @return null
 */
function show_flag_image(path, obj, to) {
    jQuery('#'+to).hide();
    if (!jQuery(obj).children('option:selected').attr('rel')) {
        jQuery(obj).removeClass('countries_select');
        return;
    }
    jQuery('#'+to).children('img').attr('src', path+jQuery(obj).children('option:selected').attr('rel'));
    jQuery('#'+to).show();
    if (!jQuery(obj).is('.countries_select'))
        jQuery(obj).addClass('countries_select');
}
/**
 * Селектор периода
 * @param obj object объект селектора
 * @return null
 */
function period_selector(obj) {
    obj = jQuery(obj);
    var n = obj.next();
    var nn = jQuery("input", n);
    var ov = obj.val();
    if (ov == -1)
        n.removeClass("hidden");
    else {
        n.addClass("hidden");
        nn.val(ov);
    }
}

/**
 * Инициализация возможности расширения поля textarea
 * @return null
 */
function textarea_resizer() {    
    jQuery(document).ready(function () {
        var $o = jQuery("textarea:not(.processed)");
        if (typeof $o.TextAreaResizer !== 'undefined')
            $o.TextAreaResizer();
        jQuery('form').submit(function () {
            make_tobbcode();
        });
    });
}

/**
 * Выполнение некоторых функци по окончанию запроса AJAX
 * @return null
 */
function ajax_complete() {
    textarea_resizer();
    init_corners();
    init_tablesorter();
    onhovered_dd();
    spoiler_init();
    autoclear_fields();
}

/**
 * Преобразование полей BBCode из режима WYSIWYG
 * @return null
 * @tutorial необходимо вызывать каждый раз, когда передаётся
 * значение поля BBCode через AJAX
 */
function make_tobbcode() {
    jQuery("div.textinput_box div.bbcode_editor").each(function () {
        var t = jQuery(this);
        if (!jQuery.isFunction(window['editor_type']))
            return;
        editor_type(jQuery("textarea", t), false, true);
    });
}

/**
 * Генерация пароля
 * @param name string имя поля пароля
 * @param name2 string имя поля повтора пароля
 * @return null
 */
function passgen(name, name2) {
    if (!name)
        name = 'password';
    if (!name2)
        name2 = 'passagain';
    var letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    letters = letters.split("");
    var max = 14;
    var min = 8;
    var length = parseInt(Math.random()*(max-min))+min;
    var llen = letters.length;
    var passwrd = "";
    for (i = 0; i < length; i++)
        passwrd += letters[parseInt(Math.random()*llen)];
    var r = jQuery('input:not([class^="styled_"])[name="'+name+'"]'+(name2?', input[name="'+name2+'"]':"")).val(passwrd);
    r.each(function () {
        jQuery(this).replaceWith('<input type="text" name="'+jQuery(this).attr('name')+'" value="'+jQuery(this).val()+'">');
    });
}

/**
 * Открытие спойлера
 * @param obj object объект кнопки спойлера
 * @return null
 */
function open_spoiler(obj) {
    var obj = jQuery(obj);
    var curobj=obj.nextAll('div.spoiler_content');
    if (curobj)
        spoiler_pic(obj, curobj);
}

/**
 * Сохранение шаблона
 * @param id int ID шаблона
 * @param form object форма шаблона
 * @return null
 */
function save_pattern(id, form) {
    make_tobbcode();
    var to='adding_form';
    var obj = jQuery('form[name="'+to+'"]', window.opener.document);
    var v = null;
    var regexpe = new RegExp('(\\\\[nr])+', 'g');
    jQuery.post(baseurl+'index.php?module=ajax_index&from_ajax=1&act=check_pattern&id='+id, 
        jQuery(form).serialize(), function (data) {
            if (data.substr(0, 3)=='OK!') {
                data = data.substr(3);
                jQuery(document).append('<script type="text/javascript">pattern_fdata = '+data+';</script>');
                for (var key in pattern_fdata) {
                    v = pattern_fdata[key];                    
                    jQuery('input[name="'+key+'"]', obj).val(v.replace(regexpe, ''));
                    jQuery('textarea[name="'+key+'"]', obj).val(v.replace(regexpe, "\n"));
                }
                window.close();
            }else
                alert(error_text+': '+data);
        });
}


/**
 * Автоочистка полей(input.autoclear_fields) с предустановленным значением
 * @return null
 */
function autoclear_fields() {
    jQuery(document).ready(function () {
        jQuery('input.autoclear_fields:not(.ac_inited)').each(function () {
            var t = jQuery(this);
            t.addClass('ac_inited');
            t.attr('data-acvalue', jQuery(this).val());
            t.removeAttr('onfocus').removeAttr('onblur');
            t.unbind('focus').unbind('blur');
            t.bind('focus', function () {
                var t = jQuery(this);
                if (t.val()==t.attr('data-acvalue'))
                    t.val('');
            })
            t.bind('blur', function () {
                var t = jQuery(this);
                if (t.val()=='')
                    t.val(t.attr('data-acvalue'));
            })
        });
    });
}

/**
 * Реализация костыля по сбросу страниц при смене сортировки таблицы
 * @param i int номер пагинатора
 * @return null
 */
function reset_paginator(i) {
    if (typeof reset_paginators == 'undefined')
        return false;
    if (typeof i != 'undefined') {
        reset_paginators[i](1);
        return true;
    }
    for (i=0;i<reset_paginators.length;i++)
        reset_paginators[i](1);
    return true;
}

/**
 * Увеличение индекса в имени поля
 * @param obj object объект поля
 * @param num string новое число
 * @return null
 */
function incrase_name_num(obj, num) {
    obj.each(function () {
        var t = jQuery(this);
        var n = t.attr('name').match(/^(\w+)\[([0-9]+)\]$/);
        if (!n)
            return;
        if (!num)
            num = parseInt(n[2])+1;
        t.attr('name', n[1]+'['+num+']');
    });
}

/**
 * Смена типа поля input
 * @param obj object объект поля
 * @param type string новый тип
 * @return null
 */
function change_input_type(obj, type) {
    obj = jQuery(obj);
    var h = obj.clone().wrap('<div/>').parent().html();
    h = h.replace(/(type=)['"]\w+['"]/, 'type="'+type+'"');
    obj.replaceWith(h);
}