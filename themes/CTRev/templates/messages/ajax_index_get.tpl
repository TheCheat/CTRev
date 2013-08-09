[*if !$only_unread*](
    <a class="white_link" href="[*gen_link module='pm'*]"><img
            src='[*$theme_path*]engine_images/inbox.png' alt="[*'new'|lang*]">&nbsp;<b>[*$inbox*]</b></a>
    ,
    <a class="white_link" href="[*gen_link module='pm' act='output'*]"><img
            src='[*$theme_path*]engine_images/outbox.png' alt="[*'new'|lang*]">&nbsp;<b>[*$outbox*]</b></a>
    [*if $unread*],
        <a class="white_link" href="[*gen_link module='pm'*]"><img
                src='[*$theme_path*]engine_images/new.png' alt="[*'new'|lang*]">&nbsp;<b>[*$unread*]</b></a>
            [*if $unread_time < $unread_last.time*]
            <script type="text/javascript">
                function start_up_unreadbox() {
                    var pid = 'message_container';
                    if (isinited_popup(pid))
                        return;
                    close_popup();
                    init_popup(pid, 'lgray_color js_notop');
                    message_closer();
                }
                function message_closer() {
                    jQuery(document).ready(function($) {
                        init_modalbox_close(function() {
                            if (!confirm("[*'pm_closed_before_new_notice'|lang*]"))
                                return false;
                            setcookie('[*$msg_cookie_timer|sl*]', '[*$unread_last.time*]');
                        });
                    });
                }
                function move_message($cur_time, $after) {
                    status_icon('messages_status_icon', 'loading');
                    jQuery.post('index.php?module=ajax_index&from_ajax=1&act=move_unread', {"time": $cur_time, "after": $after}, function(data) {
                        if (data == "ERROR!") {
                            status_icon('messages_status_icon', 'error');
                            alert("[*'error'|lang|sl*]");
                        } else {
                            status_icon('messages_status_icon', 'success');
                            if (!$after) {
                                prevmsgs--;
                                nextmsgs++;
                            } else {
                                prevmsgs++;
                                nextmsgs--;
                            }
                            //close_popup();
                            replace_popup(data);
                            message_closer();
                            //start_up_unreadbox();
                            if (prevmsgs <= 0) {
                                jQuery("#before_button").hide();
                            } else {
                                jQuery("#before_button").show();
                            }
                            if (nextmsgs <= 0) {
                                jQuery("#after_button").hide();
                            } else {
                                jQuery("#after_button").show();
                            }
                        }
                    });
                }
                function offset_of_box($time) {
                    if (nextmsgs > 0) {
                        prevmsgs--;
                        move_message($time, 1);
                    }
                    else if (prevmsgs > 0) {
                        nextmsgs--;
                        move_message($time);
                    }
                    else {
                        start_up_unreadbox();
                        close_popup();
                    }
                }
                function act_box($id, $time, $act) {
                    status_icon('messages_status_icon', 'loading');
                    remove_message($id, offset_of_box, $time, ($act == "read"));
                }
                var prevmsgs = [*$count_prev*];
                var nextmsgs = [*$count_after*];
                start_up_unreadbox();
            </script>
            [*include file="messages/main_funct.tpl"*] 
        [*/if*] 
    [*/if*] 
[*/if*] 
[*if ($unread_time < $unread_last.time && $unread) || $only_unread*] 
    [*if !$only_unread*]
        <!-- begin. Всплывающее окно -->
        <div id="message_container" class='hidden'>
        [*/if*]
        <div class="modalbox_title">
            <div class="status_icon" id="messages_status_icon"></div>
            [*$unread_last.subject*]</div>
        <div class="modalbox_content">[*$unread_last.text|ft*]</div>
        <div class="modalbox_subcontent">
            <div class="float_left">
                <nobr>
                    [*'pm_sender'|lang*][*$unread_last.username|gcl:$unread_last.group*],
                    [*date time=$unread_last.time format="ymdhis"*]
                    <input type="image" id="before_button" onclick="move_message('[*$unread_last.time*]');"
                           src="[*$theme_path*]engine_images/arrow_left.png" alt="[*'prev'|lang*]"
                           class='clickable [*if !$count_prev && !$only_unread*]hidden[*/if*]'>
                    <input type="image" id="after_button" onclick="move_message('[*$unread_last.time*]', 1);"
                           class='clickable [*if !$count_after && !$only_unread*]hidden[*/if*]'
                           src="[*$theme_path*]engine_images/arrow_right.png" alt="[*'next'|lang*]">
                </nobr>
            </div>
            <div align="right"><input type="button" value="[*'pm_read'|lang*]"
                                      onclick="act_box('[*$unread_last.id*]', '[*$unread_last.time*]', 'read')">&nbsp;<input
                                      type="button" value="[*'delete'|lang*]"
                                      onclick="act_box('[*$unread_last.id*]', '[*$unread_last.time*]', 'delete')"></div>
        </div>
        [*if !$only_unread*]
        </div>
        <!-- end. Всплывающее окно -->
    [*/if*] 
[*/if*][*if !$only_unread*])[*/if*]