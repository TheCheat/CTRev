/**
 * Сохранение статуса торрента
 * @param obj object объект статуса
 * @param id int ID торрента
 * @return null
 */
function save_status(obj, id) {
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=torrents&from_ajax=1&act=status',
    {
        "id": id,
        "status":jQuery(obj).val()
    }, function (data) {
        jQuery(obj).replaceWith(data);
    });
}
/**
 * Загрузка сторонних пиров торрента
 * @param obj object кнопка спойлера
 * @param id int ID торрента
 * @return null
 */
function load_peers(obj, id) {
    var o = jQuery(obj).nextAll('div.spoiler_content');
    if (!o.html()) {
        jQuery.post(baseurl+'index.php?module=torrents&act=getpeers&from_ajax=1', {
            "id": id
        }, function (data) {
            o.empty();
            o.append(data);
        });
    }
}
/**
 * Удаление торрента
 * @param id int ID торрента
 * @return null
 */
function delete_torrents(id) {
    if (!confirm(torrents_are_sure_to_delete_torrents))
        return;
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=torrents&from_ajax=1&act=delete', {
        "id": id
    }, function (data) {
        if (data == "OK!") {
            $("#torrents_"+id).fadeOut(2000, function() {
                $(this).remove();
                if (full_torrents)
                    window.location = baseurl+'index.php';
                else {
                    if (!$page) {
                        $page = 0;
                    }
                    jQuery.post($pageurl+$page, function ($data) {
                        $("#body-ajax-torrents").empty();
                        $("#body-ajax-torrents").append($data);
                    });
                }

            });

            alert(success_text);
        } else {
            alert(error_text+': '+data+' '+please_refresh_page);
        }
    });
}
/**
 * Очистка комментариев торрента
 * @param id int ID торрента
 * @return null
 */
function clear_tcomments(id) {
    if (!confirm(are_you_sure_to_do_this))
        return;
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=torrents&from_ajax=1&act=clear_comm', {
        "id": id
    }, function (data) {
        if (data == "OK!") {
            jQuery('#allcomments_body').empty().append(lang_no_comments);
            alert(success_text);
        } else {
            alert(error_text+': '+data+' '+please_refresh_page);
        }
    });
}
/**
 * Смена страницы торрентов
 * @param $page_s int новая страница
 * @return null
 */
function change_tpage($page_s) {
    if (!$page_s) {
        $page_s = 1;
    }
    var $page = $page_s;
    jQuery.post($pageurl+$page+"&nno=1", function (data) {
        $("#body-ajax-torrents").empty();
        $("#body-ajax-torrents").append(data);
        init_corners();
        window.location = "#body-ajax-torrents";
    });
}

/**
 * Сохранение торрента
 * @param $id int ID торрента
 * @param form object объект формы
 * @param full bool детали торрента?
 * @return null
 */
function save_torrents($id, form, full) {
    make_tobbcode();
    form = jQuery(form).serialize();
    jQuery.post(baseurl+"index.php?module=torrents&act=save&from_ajax=1&full="+full_torrents+"&c="+full+"&id="+$id,
        form, function (data) {
            if (data.indexOf('OK!')==0) {
                jQuery("#torrents_"+$id).empty().append(data.substr(3));
            } else
                alert(error_text+': '+data);
        });
}

/**
 * Отмена редактирования торрента
 * @param $id int ID торрента
 * @return null
 */
function cancel_edit_torrents($id) {
    jQuery("#new_torrents_"+$id).remove();
    jQuery("#old_torrents_"+$id).show().attr("id", "torrents_body_"+$id);
}

/**
 * Быстрое редактирование торрента
 * @param $id int ID торрента
 * @param obj object поле торрента
 * @return null
 */
function edit_torrents($id, obj) {
    //cancel_edit_torrents($id);
    if (!jQuery(obj).length)
        return;
    jQuery.post(baseurl+"index.php?module=torrents&act=quick_edit&from_ajax=1",
    {
        "id":$id,
        "full":full_torrents
    },
    function (data) {
        obj = jQuery(obj);
        obj.clone().hide().insertBefore(obj).attr("id","old_torrents_"+$id);
        obj.empty().attr("id","new_torrents_"+$id).append(data);
    });
}