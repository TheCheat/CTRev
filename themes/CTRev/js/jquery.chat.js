cint = null;
last_ctime = 0;
cur_cedit = 0;
setInterval('chat_server_time++;', 1000);
/**
 * Скроллинг чата вниз
 * @return null
 */
function chat_scroll() {
    jQuery('div.chat_box').scrollTop(jQuery('#chat_area').height());
}
/**
 * Отправка сообщения в чате
 * @return null
 */
function chat_say() {
    var text = trim(jQuery('#chat_textarea').val());
    if (!text)
        return;
    chat_clear();
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=chat&from_ajax=1&act=save&id='+cur_cedit, {
        'text':text
    }, function () {
        if (cur_cedit)
            jQuery('#chat_mess'+cur_cedit).removeClass('chat_message_editing');
        cur_cedit = 0;
        chat_update(true);
    });
}
/**
 * Очистить поле ввода для чата
 * @param value string значение после очистки
 * @return null
 */
function chat_clear(value) {
    jQuery('#chat_textarea').val(value?value:'');
}
/**
 * Очистить чат
 * @return null
 */
function chat_truncate() {
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=chat&from_ajax=1&act=truncate', function (data) {
        jQuery('#chat_area').empty().append(data);
    });
    chat_lctime();
}
/**
 * Пред. сообщения чата
 * @return null
 */
function chat_prev() {
    prehide_ls();
    var pid = parseInt(jQuery('#chat_area div.chat_message:first').attr('id').replace('chat_mess', ''));
    jQuery.post(baseurl+'index.php?module=chat&from_ajax=1&time='+pid+'&prev=1', function (data) {
        chat_clear_nm();
        jQuery('#chat_area').prepend(data);
    });
}

/**
 * Интервал обновления чата
 * @param time int интервал
 * @return null
 */
function chat_interval(time) {
    if (cint)
        clearInterval(cint);
    cint = setInterval('chat_update();', time*1000);
}

/**
 * Синхронизация времени чата с временем на сервере
 * @return null
 */
function chat_lctime() {
    jQuery('#chat_loader').hide();
    jQuery('#chat_area a.profile_link').unbind('click');
    jQuery('#chat_area a.profile_link').bind('click', function (e) {
        e.preventDefault();
        var t = '[b]'+jQuery(this).text()+'[/b],';
        var v = jQuery('#chat_textarea').val();
        var nv = v.replace(new RegExp('^'+regex_quote(t)+'\\s*', 'g'), '');
        if (nv == v)
            chat_clear(t+' '+v);
        else
            chat_clear(nv);
    }) 
    last_ctime = chat_server_time;
}

/**
 * Очистка поля чата
 * @return null
 */
function chat_clear_nm() {
    if (!jQuery('#chat_area div.chat_message').length)
        jQuery('#chat_area').empty();
}

/**
 * Обновление чата
 * @param scrollme bool переместиться вниз?
 * @return null
 */
function chat_update(scrollme) {
    jQuery('#chat_loader').show();
    if (!last_ctime)
        jQuery('#chat_area').empty();
    prehide_ls();
    jQuery.post(baseurl+'index.php?module=chat&from_ajax=1&time='+last_ctime, function (data) {
        if (!last_ctime) {
            jQuery('#chat_area').append(data);
            add_chat_event();
            chat_lctime();
            chat_scroll();
            return;
        } else
            jQuery('#chat_no_mess').remove();
        chat_clear_nm();
        jQuery('body').append('<div id="temporary_chat" class="hidden">'+data+'</div>');
        var o = jQuery('#temporary_chat');
        jQuery('div.chat_message', o).each(function () {
            var so = jQuery('#chat_area #'+jQuery(this).attr('id'));
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
                jQuery('#chat_area #chat_mess'+n).remove();
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
 * @return null
 */
function add_chat_event() {
    jQuery('.chat_message').unbind('mouseenter mouseleave').bind(
    {
        'mouseenter': function () {
            jQuery('span.chat_edit_row', this).removeClass('hidden');
        },
        'mouseleave': function () {
            jQuery('span.chat_edit_row', this).addClass('hidden');
        }
    });
}
/**
 * Удаление сообщения чата
 * @param id int ID сообщения
 * @return null
 */
function chat_delete(id) {
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=chat&from_ajax=1&act=delete&id='+id, function (data) {
        jQuery('#chat_mess'+id).remove();
    });
}
/**
 * Редактирование сообщения чата
 * @param id int ID сообщения
 * @return null
 */
function chat_edit(id) {
    if (cur_cedit)
        jQuery('#chat_mess'+cur_cedit).removeClass('chat_message_editing');
    jQuery.post(baseurl+'index.php?module=chat&from_ajax=1&act=text&id='+id, function (data) {
        cur_cedit = id;
        jQuery('#chat_mess'+cur_cedit).addClass('chat_message_editing');
        chat_clear(data);
    });
}

jQuery(document).ready(function () {
    jQuery('#chat_textarea').keypress(function (event) {
        if (event.which==13)
            chat_say();
    });
    chat_update();
    chat_interval(15);
});