/**
 * Проверка на макс. кол-во ответов
 * @param obj object объекты ответов
 * @param id int ID опроса
 * @param max_votes int макс. кол-во голосов
 * @param shrt bool в блоке?
 * @return null
 */
function check_max_selected(obj, id, max_votes, shrt) {
    var $chkboxes = jQuery('.answer_'+id+(shrt?"_short":"")+':checked');
    if ($chkboxes.length > max_votes) {
        jQuery(obj).attr('checked', false);
        alert(polls_you_re_select_max_vote);
    }
}
/**
 * Отображение результатов голосования
 * @param poll_id int ID опроса
 * @param voting bool из голосования?
 * @param shrt bool в блоке?
 * @return null
 */
function change_voting_type(poll_id, voting, shrt) {
    var type = -1;
    if (!voting)
        type = 1; 
    var si = "poll"+poll_id+(shrt?"_short":"")+"_status_icon";
    status_icon(si, 'loading_white');
    jQuery.get(baseurl+'index.php', {
        'module':'polls_manage',
        'nno':'1',
        'from_ajax':'1',
        'id':poll_id,
        'votes':type,
        'short':shrt
    }, function (data) {
        status_icon(si, 'success');
        jQuery('#question_id'+poll_id+(shrt?"_short":"")).replaceWith(data);
        if (type==1)
            init_votes(poll_id, shrt);
    });
}
/**
 * Голосование в опросе
 * @param poll_id int ID опроса
 * @param shrt bool в блоке?
 * @return null
 */
function poll_vote(poll_id, shrt) {
    var answers = jQuery('#question_id'+poll_id+(shrt?"_short":"")+' form').serialize();
    var si = "poll"+poll_id+(shrt?"_short":"")+"_status_icon";
    status_icon(si, 'loading_white');
    jQuery.post(baseurl+'index.php?module=polls_manage&from_ajax=1&act=vote&id='+poll_id, answers, function (data) {
        if (data=="OK!") {
            status_icon(si, 'success');
            change_voting_type(poll_id, 0, shrt);
        } else {
            alert(error_text+': '+data);
            status_icon(si, 'error');
        }
    });
}
/**
 * Инициализация голосов в опросе
 * @param poll_id int ID опроса
 * @param shrt bool в блоке?
 * @return null
 */
function init_votes(poll_id, shrt) {
    jQuery('#question_id'+poll_id+(shrt?"_short":"")+' div.votes').each(function () {
        var $this = jQuery(this);
        var $width = parseFloat($this.children('span').text());
        if ($width) {
            $width = ($width!="0"?$width+"%":'1px');
            if ($width!='1px')
                $this.animate({
                    'width': $width
                }, 1000, 'swing');
        }
    });
}
/**
 * Редактирование опроса
 * @param poll_id int ID опроса
 * @param shrt bool в блоке?
 * @return null
 */
function edit_polls(poll_id, shrt) {
    var si = "poll"+poll_id+(shrt?"_short":"")+"_status_icon";
    status_icon(si, 'loading_white');
    jQuery.get(baseurl+'index.php', {
        'module':'polls_manage',
        'act':'edit',
        'from_ajax':'1',
        'id':poll_id,
        'nno':1
    }, function (data) {
        status_icon(si, 'success');
        jQuery('#question_id'+poll_id+(shrt?"_short":"")).empty();
        jQuery('#question_id'+poll_id+(shrt?"_short":"")).append(data);
    });
}
/**
 * Удаление опроса
 * @param poll_id int ID опроса
 * @param shrt bool в блоке?
 * @return null
 */
function delete_polls(poll_id, shrt) {
    if (!confirm(polls_you_re_sure_to_delete))
        return;
    var si = "poll"+poll_id+(shrt?"_short":"")+"_status_icon";
    status_icon(si, 'loading_white');
    jQuery.post(baseurl+'index.php'+fk_ajax+'module=polls_manage&act=delete&from_ajax=1&id='+poll_id, function (data) {
        if (data=="OK!") {
            alert(success_text);
            var obj = jQuery('#question_id'+poll_id+(shrt?"_short":""));
            obj.fadeOut(1000, function () {
                status_icon(si, 'success');
                if (jQuery(this).is('.single_poll'))
                    window.location = polls_link;
                jQuery(this).remove();
            });
        } else {
            alert(error_text);
            status_icon(si, 'error');
        }
    });
}