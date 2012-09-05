/**
 * Добавление друга
 * @param $username string имя пользователя
 * @return null
 */
function add_friend($username) {
    var $act = "";
    if (confirm(usercp_friends_are_you_sure_add))
        $act = "f";
    else if (confirm(usercp_friends_you_want_to_block))
        $act = "b";
    else
        return;
    jQuery.post(baseurl+'index.php?module=usercp&from_ajax=1&act=add_friend', {
        "username":$username, 
        "type":$act
    },
    function (data) {
        if (data.substr(0,3) == "OK!") {
            jQuery('#add_to_friend').addClass('hidden');
            jQuery('#remove_from_friend').removeClass('hidden');
            if ($act == "b") {
                jQuery('#send_to_block').removeClass('hidden');
                jQuery('#send_to_friend').addClass('hidden');
            } else {
                jQuery('#send_to_block').addClass('hidden');
                jQuery('#send_to_friend').removeClass('hidden');
            }
            jQuery('#error_place_friends').remove();
            jQuery('#add_place_friends').append(data.substr(3));
            alert(success_text);
        } else
            alert(error_text+': '+data);
    }
    );
}
/**
 * Удаление друга
 * @param $id int ID пользователя
 * @return null
 */
function delete_friend($id) {
    if (!confirm(usercp_friends_are_you_sure_to_delete))
        return;
    jQuery.post(baseurl+'index.php?module=usercp&from_ajax=1&act=delete_friend', {
        "id":$id
    },
    function (data) {
        if (data == "OK!") {
            jQuery('#add_to_friend').removeClass('hidden');
            jQuery('#remove_from_friend').addClass('hidden');
            jQuery("#ucp_friend_"+$id).fadeOut(2000, function () {
                jQuery(this).remove();
            });
            alert(success_text);
        } else
            alert(error_text+': '+data);
    }
    );
}
/**
 * Изменения типа дружбы
 * @param $id int ID пользователя
 * @param $type string f - дружба, b - вражда
 * @return null
 */
function change_type_friend($id, $type) {
    if ($type=="f") {
        if (!confirm(usercp_friends_you_want_to_block))
            return;
    } else if (!confirm(usercp_friends_are_you_sure_add))
        return;
    jQuery.post(baseurl+'index.php?module=usercp&from_ajax=1&act=change_tfriend', {
        "id":$id
    },
    function (data) {
        if (data.substr(0, 3) == "OK!") {
            if ($type == "f") {
                jQuery('#send_to_block').removeClass('hidden');
                jQuery('#send_to_friend').addClass('hidden');
            } else {
                jQuery('#send_to_block').addClass('hidden');
                jQuery('#send_to_friend').removeClass('hidden');
            }
            jQuery("#ucp_friend_"+$id).empty();
            jQuery("#ucp_friend_"+$id).append(data.substr(3));
            alert(success_text);
        } else
            alert(error_text+': '+data);
    }
    );
}


/**
 * Удаление подписки
 * @param obj object объект подписки
 * @param id int ID ресурса
 * @param type string тип ресурса
 * @return null
 */
function delete_mailer(obj, id, type) {
    if (!confirm(usercp_mailer_confirm_delete))
        return;
    jQuery.post(baseurl+'index.php?module=usercp&from_ajax=1&act=delete_mailer', {
        "id":id,
        "type":type
    }, function (data) {
        if (data=="OK!") {
            alert(success_text);
            jQuery(obj).parents('tr').children("td").fadeOut(1000, function () {
                jQuery(this).parent().remove();
            });
        } else {
            alert(error_text+': '+data);
        }
    });
}
/**
 * Смена интервала подписки
 * @param obj object объект подписки
 * @param id int ID ресурса
 * @param type string тип ресурса
 * @return null
 */
function mchange_type(obj, id, type) {
    obj = jQuery(obj);
    var was = obj.html();
    obj.empty();
    obj.append(select_mailer);
    var sel = obj.children('select');
    sel.children('option').each(function () {
        if (jQuery(this).html()==was)
            jQuery(this).attr('selected', 'selected');
    });
    sel.attr('onchange', 'msave_type(this, "'+addslashes(id)+'", "'+addslashes(type)+'");');
}
/**
 * Сохранение интервала подписки
 * @param obj object объект подписки
 * @param id int ID ресурса
 * @param type string тип ресурса
 * @return null
 */
function msave_type(obj, id, type) {
    obj = jQuery(obj);
    jQuery.post(baseurl+'index.php?module=usercp&from_ajax=1&act=make_mailer', {
        "id":id,
        "type":type,
        "interval":obj.val(),
        "upd":1
    }, function (data) {
        if (data=="OK!") {
            alert(success_text);
            var text = obj.children("option:selected").html();
            obj = obj.parent();
            obj.empty();
            obj.append(text);
        } else {
            alert(error_text+': '+data);
        }
    });
}

/**
 * Удаление инвайта
 * @param invite_id int ID инвайта
 * @return null
 */
function delete_invite(invite_id) {
    if (!confirm(usercp_are_you_sure_to_delete_invite))
        return;
    jQuery.post(baseurl+'index.php?module=usercp&act=delete_invite&from_ajax=1', {
        "invite_id": invite_id
    }, function (data) {
        if (data=="OK!") {
            jQuery("#invite"+invite_id).children("td").each(function () {
                jQuery(this).fadeOut(2000, function () {
                    jQuery(this).parent().remove();
                });
            });
            alert(success_text);
        } else {
            alert(error_text+": "+data);
        }
    });
}
/**
 * Подтверждение пользователя по инвайту
 * @param invite_id int ID инвайта
 * @return null
 */
function confirm_invite(invite_id) {
    jQuery.post(baseurl+'index.php?module=usercp&act=confirm_invite&from_ajax=1', {
        "invite_id": invite_id
    }, function (data) {
        if (data=="OK!") {
            var $el = jQuery("#invite"+invite_id).children("td.confirm");
            $el.empty();
            $el.append(yes_text);
            alert(success_text);
        } else {
            alert(error_text+": "+data);
        }
    });
}
/**
 * Добавление инвайта
 * @return null
 */
function add_invite() {
    jQuery.post(baseurl+'index.php?module=usercp&act=add_invite&from_ajax=1', function (data) {
        jQuery("#ucp_invite_table tbody").append(data);
        init_tablesorter();
    });
}