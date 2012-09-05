/**
 * Перезагрузка комментариев
 * @param $resid int ID ресурса
 * @param $type string тип ресурса
 * @return null
 */
function reload_comment($resid, $type) {
    jQuery.post($comments_pageurl+$comments_page, {
        "resid":$resid,
        "type":$type
    }, function ($data) {
        jQuery("#comments_"+comments_name).empty().append($data);
        show_edit_buttons();
    //init_corners();
    //alert(success_text);
    });
}
/**
 * Удаление комментария
 * @param id_res int ID ресурса
 * @param id int ID комментария
 * @return null
 */
function del_comment(id_res,id) {
    if (!confirm(are_you_sure_to_delete_this_comment))
        return;
    jQuery.post(baseurl+"index.php"+fk_ajax+"module=comments_manage&act=del&from_ajax=1", {
        "id":id
    }, function (data) {
        if (data=="OK!") {
            jQuery("#"+id_res).fadeOut(1000,function () {
                reload_comment(comments_resid, comments_type);
            });
        } else
            alert(error_text);
    });
}
/**
 * Отмена редактирования комментария
 * @param id int ID комментария
 * @return null
 */
function cancel_edit_comment(id) {
    jQuery("#new_content_"+id).remove();
    jQuery("#old_content_"+id).show();
}
/**
 * Редактирование комментария
 * @param id_res int ID ресурса
 * @param id int ID комментария
 * @return null
 */
function edit_comment(id_res,id) {
    jQuery.post(baseurl+"index.php?module=comments_manage&act=edit&from_ajax=1", {
        "id":id
    }, function (data) {
        var $o = jQuery("#"+id_res+" .body_comment");
        $o.clone().hide().insertBefore($o).attr("id", "old_content_"+id);
        $o.empty().append(data).attr('id', 'new_content_'+id);
    });
}
/**
 * Сохранение комментария
 * @param id_res int ID ресурса
 * @param id int ID комментария
 * @param string name имя полей
 * @return null
 */
function edit_comment_save(id_res, id, name) {
    make_tobbcode();
    var $comment = jQuery("textarea[name='"+name+"']").val();
    var $title = jQuery("input[name='"+name+"_subj']").val();    
    var si = "comments_status_icon_"+id;
    status_icon(si, 'loading_white');
    jQuery.post(baseurl+"index.php"+fk_ajax+"module=comments_manage&act=edit_save&from_ajax=1", {
        "title":$title,
        "body":$comment,
        "id":id
    }, function (data) {
        if (data=="OK!") {
            reload_comment(comments_resid, comments_type);     
            status_icon(si, 'success');
        } else {
            alert(error_text+": "+data);  
            status_icon(si, 'error');
        }
    });
}
/**
 * Смена страницы комментариев
 * @param $comments_page_s int новая страница
 * @return null
 */
function change_page_comments($comments_page_s) {
    if (!$comments_page_s) {
        $comments_page_s = 1;
    }
    $comments_page = $comments_page_s;
    reload_comment(comments_resid, comments_type);
}
/**
 * Отображение иконок редактирования/удаления/цитаты
 * @return null
 */
function show_edit_buttons() {
    jQuery(document).ready(function () {
        jQuery(".comment_body").hover(function () {
            jQuery("#"+jQuery(this).attr("id")+"_buttons").show();
        }, function () {
            jQuery("#"+jQuery(this).attr("id")+"_buttons").hide();
        });
    });
}
show_edit_buttons();