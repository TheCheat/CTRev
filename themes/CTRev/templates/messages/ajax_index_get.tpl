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
                var prevmsgs = [*$count_prev*];
                var nextmsgs = [*$count_after*];
                function start_up_unreadbox() {
                    init_popup('message_container', 'lgray_color js_notop');
                    jQuery(document).ready(function ($) {
                        init_modalbox_close(function () {
                            if (!confirm("[*'pm_closed_before_new_notice'|lang*]"))
                            return false;
                            jQuery.post('[*$baseurl|sl*]index.php?module=ajax_index&act=save_last&from_ajax=1', {"time":'[*$unread_last.time*]'});
                        });
                    });
                }
                start_up_unreadbox();
                function move_message($cur_time, $type) {
                    jQuery.post('[*$baseurl|sl*]index.php?module=ajax_index&from_ajax=1&act=move_unread', {"time":$cur_time, "type":$type}, function (data) {
                        if (data == "ERROR!") {
                            alert("[*'error'|lang|sl*]");
                        } else {
                            close_popup();
                            if ($type=="before") {
                                prevmsgs = prevmsgs - 1;
                                nextmsgs = nextmsgs + 1;
                            } else {
                                prevmsgs = prevmsgs + 1;
                                nextmsgs = nextmsgs - 1;
                            }
                            jQuery("#message_container").empty();
                            jQuery("#message_container").append(data);
                            start_up_unreadbox();
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
                        prevmsgs = prevmsgs-1;
                        move_message($time, "after");
                    }
                    else if (prevmsgs > 0) {
                        nextmsgs = nextmsgs-1;
                        move_message($time, "before");
                    }
                    else {
                        start_up_unreadbox();
                        close_popup();
                    }
                }
                function act_box($id, $time, $act) {
                    remove_message($id, offset_of_box, $time, ($act=="read"));
                }
            </script>
            [*include file="messages/main_funct.tpl"*] 
        [*/if*] 
    [*/if*] 
[*/if*] 
[*if ($unread_time < $unread_last.time && $unread) || $only_unread*] 
    [*if !$only_unread*]
        <!-- begin. Всплывающее окно -->
        <div id="message_container">
        [*/if*]
        <div class="modalbox_title">[*$unread_last.subject*]</div>
        <div class="modalbox_content">[*$unread_last.text|ft*]</div>
        <div class="modalbox_subcontent">
            <div class="float_left"><nobr>
                    [*'pm_sender'|lang*][*$unread_last.username|gcl:$unread_last.group*],
                    [*date time=$unread_last.time format="ymdhis"*]&nbsp;<input type="image"
                                 id="before_button"
                                 onclick="move_message('[*$unread_last.time*]', 'before');"
                                 src="[*$theme_path*]engine_images/arrow_left.png" alt="[*'prev'|lang*]"
                                 [*if !$count_prev && !$only_unread*] s
                                     class='hidden'
                                 [*/if*]>&nbsp;<input  type="image" id="after_button"
                                 onclick="move_message('[*$unread_last.time*]', 'after');"
                                 src="[*$theme_path*]engine_images/arrow_right.png" alt="[*'next'|lang*]"
                                 [*if !$count_after && !$only_unread*] 
                                     class='hidden'
                                 [*/if*]></nobr></div>
            <div align="right"><input type="button" value="[*'pm_read'|lang*]"
                                      onclick="act_box('[*$unread_last.id*]','[*$unread_last.time*]', 'read')">&nbsp;<input
                                      type="button" value="[*'delete'|lang*]"
                                      onclick="act_box('[*$unread_last.id*]','[*$unread_last.time*]', 'delete')"></div>
        </div>
        [*if !$only_unread*]
        </div>
        <!-- end. Всплывающее окно -->
    [*/if*] 
[*/if*])