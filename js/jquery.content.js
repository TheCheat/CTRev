/**
 * Сохранение статуса контента
 * @param {object} obj объект статуса
 * @param {int} id ID контента
 * @returns {null}
 */
function save_status(obj, id) {
    jQuery.post('index.php' + fk_ajax + 'module=content&from_ajax=1&act=status',
            {
                "id": id,
                "status": jQuery(obj).val()
            }, function(data) {
        jQuery(obj).replaceWith(data);
    });
}
/**
 * Загрузка сторонних пиров контента
 * @param {object} obj кнопка спойлера
 * @param {int} id ID контента
 * @returns {null}
 */
function load_peers(obj, id) {
    var o = jQuery(obj).nextAll('div.spoiler_content');
    if (!o.html()) {
        jQuery.post('index.php?module=content&act=getpeers&from_ajax=1', {
            "id": id
        }, function(data) {
            o.empty();
            o.append(data);
        });
    }
}
/**
 * Удаление контента
 * @param {int} id ID контента
 * @returns {null}
 */
function delete_content(id) {
    if (!confirm(content_are_sure_to_delete))
        return;
    jQuery.post('index.php' + fk_ajax + 'module=content&from_ajax=1&act=delete', {
        "id": id
    }, function(data) {
        if (is_ok(data)) {
            if (full_content == 1)
                window.location = 'index.php';
            else {
                $("#content_" + id).fadeOut(2000, function() {
                    $(this).remove();
                    if (!$page)
                        $page = 0;
                    change_tpage($page);
                });
            }
            //alert(success_text);
        } else
            alert(error_text + ': ' + data + ' ' + please_refresh_page);
    });
}
/**
 * Очистка комментариев контента
 * @param {int} id ID контента
 * @returns {null}
 */
function clear_tcomments(id) {
    if (!confirm(are_you_sure_to_do_this))
        return;
    jQuery.post('index.php' + fk_ajax + 'module=content&from_ajax=1&act=clear_comm', {
        "id": id
    }, function(data) {
        if (is_ok(data)) {
            jQuery('#allcomments_body').empty().append(lang_no_comments);
            //alert(success_text);
        } else {
            alert(error_text + ': ' + data + ' ' + please_refresh_page);
        }
    });
}
/**
 * Смена страницы контента
 * @param {int} $page_s новая страница
 * @returns {null}
 */
function change_tpage($page_s) {
    if (!$page_s)
        $page_s = 1;
    $page = $page_s;
    jQuery.post($pageurl + $page + "&nno=1", function(data) {
        $("#body-ajax-content").empty();
        $("#body-ajax-content").append(data);
        init_corners();
        window.location.hash = "#body-ajax-content";
    });
}

/**
 * Сохранение контента
 * @param {int} $id ID контента
 * @param {object} form объект формы
 * @param {bool} full детали контента?
 * @returns {null}
 */
function save_content($id, form, full) {
    make_tobbcode();
    form = jQuery(form).serialize();
    jQuery.post("index.php?module=content&act=save&from_ajax=1&full=" + full_content + "&c=" + full + "&id=" + $id,
            form, function(data) {
        if (is_ok(data, true)) {
            jQuery("#content_" + $id).empty().append(cut_ok(data));
        } else
            alert(error_text + ': ' + data);
    });
}

/**
 * Отмена редактирования контента
 * @param {int} $id ID контента
 * @returns {null}
 */
function cancel_edit_content($id) {
    jQuery("#new_content_" + $id).remove();
    jQuery("#old_content_" + $id).show().attr("id", "content_body_" + $id);
}

/**
 * Быстрое редактирование контента
 * @param {int} $id ID контента
 * @param {object} obj поле контента
 * @returns {null}
 */
function edit_content($id, obj) {
    //cancel_edit_content($id);
    if (!jQuery(obj).length)
        return;
    jQuery.post("index.php?module=content&act=quick_edit&from_ajax=1",
            {
                "id": $id,
                "full": full_content
            },
    function(data) {
        obj = jQuery(obj);
        obj.clone().hide().insertBefore(obj).attr("id", "old_content_" + $id);
        obj.empty().attr("id", "new_content_" + $id).append(data);
    });
}