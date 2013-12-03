cint = null;
last_ctime = 0;
cur_cedit = 0;
last_cinterval = 15;
setInterval('chat_server_time++;', 1000);
/**
 * Скроллинг чата вниз
 * @returns {null}
 */
function chat_scroll() {
    jQuery('div.chat_box').scrollTop(jQuery('#chat_area').height());
}

/**
 * Отображение загрузчика чата
 * @param {bool} hide скрыть загрузчик?
 * @returns {null}
 */
function chat_loader(hide) { 
    if (hide) {        
        jQuery('#chat_loader').hide();
        return;
    }
    prehide_ls();  
    jQuery('#chat_loader').show();
}
/**
 * Отправка сообщения в чате
 * @returns {null}
 */
function chat_say() {
    var text = trim(jQuery('#chat_textarea').val());
    if (!text)
        return;
    chat_clear();  
    chat_loader();
    jQuery.post('index.php' + fk_ajax + 'module=chat&from_ajax=1&act=save&id=' + cur_cedit, {
        'text': text
    }, function() {
        if (cur_cedit) {
            jQuery('#chat_mess' + cur_cedit).removeClass('chat_message_editing');
            cur_cedit = 0;
            chat_interval(last_cinterval);
        }
        chat_update(true);
    });
}
/**
 * Очистить поле ввода для чата
 * @param {string} value значение после очистки
 * @returns {null}
 */
function chat_clear(value) {
    jQuery('#chat_textarea').val(value ? value : '');
}
/**
 * Очистить чат
 * @returns {null}
 */
function chat_truncate() {
    chat_loader();
    jQuery.post('index.php' + fk_ajax + 'module=chat&from_ajax=1&act=truncate', function(data) {
        jQuery('#chat_area').empty().append(data);
        chat_loader(true);
    });
    chat_lctime();
}
/**
 * Пред. сообщения чата
 * @returns {null}
 */
function chat_prev() {
    prehide_ls();
    var pid = parseInt(jQuery('#chat_area div.chat_message:first').attr('id').replace('chat_mess', ''));
    jQuery.post('index.php?module=chat&from_ajax=1&time=' + pid + '&prev=1', function(data) {
        chat_clear_nm();
        jQuery('#chat_area').prepend(data);
    });
}

/**
 * Интервал обновления чата
 * @param {int} time интервал
 * @param {bool} clear только очистить?
 * @returns {null}
 */
function chat_interval(time, clear) {
    if (cint)
        clearInterval(cint);
    if (!clear) {
        cint = setInterval('chat_update();', time * 1000);
        last_cinterval = time;
    }
}

/**
 * Синхронизация времени чата с временем на сервере
 * @returns {null}
 */
function chat_lctime() {
    chat_loader(true);
    jQuery('#chat_area a.profile_link').unbind('click');
    jQuery('#chat_area a.profile_link').bind('click', function(e) {
        e.preventDefault();
        var t = '[b]' + jQuery(this).text() + '[/b],';
        var v = jQuery('#chat_textarea').val();
        var nv = v.replace(new RegExp('^' + regex_quote(t) + '\\s*', 'g'), '');
        if (nv == v)
            chat_clear(t + ' ' + v);
        else
            chat_clear(nv);
    })
    last_ctime = chat_server_time;
}

/**
 * Очистка поля чата
 * @returns {null}
 */
function chat_clear_nm() {
    if (!jQuery('#chat_area div.chat_message').length)
        jQuery('#chat_area').empty();
}

/**
 * Обновление чата
 * @param {bool} scrollme переместиться вниз?
 * @returns {null}
 */
function chat_update(scrollme) {
    chat_loader();
    if (!last_ctime)
        jQuery('#chat_area').empty();
    prehide_ls();
    jQuery.post('index.php?module=chat&from_ajax=1&time=' + last_ctime, function(data) {
        if (!last_ctime) {
            jQuery('#chat_area').append(data);
            add_chat_event();
            chat_lctime();
            chat_scroll();
            return;
        } else
            jQuery('#chat_no_mess').remove();
        chat_clear_nm();
        jQuery('body').append('<div id="temporary_chat" class="hidden">' + data + '</div>');
        var o = jQuery('#temporary_chat');
        jQuery('div.chat_message', o).each(function() {
            var so = jQuery('#chat_area #' + jQuery(this).attr('id'));
            if (!so.length)
                jQuery('#chat_area').append(jQuery(this).wrap('<div>').parent().html());
            else {
                so.html(jQuery(this).html());
                so.attr('class', jQuery(this).attr('class'));
            }
        });
        var d = jQuery('div.chat_deleted_messages', o).text();
        if (d) {
            d = d.split(',');
            for (var i in d) {
                var n = parseInt(d[i]);
                if (!n)
                    jQuery('#chat_area').empty();
                jQuery('#chat_area #chat_mess' + n).remove();
            }
        }
        o.remove();
        if (!jQuery('#chat_area div.chat_message').length)
            jQuery('#chat_area').empty().append(chat_no_messages);
        add_chat_event();
        chat_lctime();
        if (scrollme)
            chat_scroll();
    });
}
/**
 * Добавление эвентов к сообщениям чата
 * @returns {null}
 */
function add_chat_event() {
    jQuery('.chat_message').unbind('mouseenter mouseleave').bind(
    {
        'mouseenter': function() {
            jQuery('span.chat_edit_row', this).removeClass('hidden');
        },
        'mouseleave': function() {
            jQuery('span.chat_edit_row', this).addClass('hidden');
        }
    });
}
/**
 * Удаление сообщения чата
 * @param {int} id ID сообщения
 * @returns {null}
 */
function chat_delete(id) {
    chat_loader();
    jQuery.post('index.php' + fk_ajax + 'module=chat&from_ajax=1&act=delete&id=' + id, function(data) {
        jQuery('#chat_mess' + id).remove();
        chat_loader(true);
    });
}
/**
 * Редактирование сообщения чата
 * @param {int} id ID сообщения
 * @returns {null}
 */
function chat_edit(id) {
    if (cur_cedit)
        jQuery('#chat_mess' + cur_cedit).removeClass('chat_message_editing');
    chat_loader();
    jQuery.post('index.php?module=chat&from_ajax=1&act=text&id=' + id, function(data) {
        cur_cedit = id;
        jQuery('#chat_mess' + cur_cedit).addClass('chat_message_editing');
        chat_clear(html_decode(data));
        chat_interval(null, true);
        chat_loader(true);
    });
}

jQuery(document).ready(function() {
    jQuery('#chat_textarea').keypress(function(event) {
        if (event.which == 13)
            chat_say();
    });
    chat_update();
    chat_interval(last_cinterval);
});