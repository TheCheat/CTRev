[*assign var='owntitle' value=1*]
<legend>
    <span class='hidden' id='chat_loader'>
        <img src="[*$theme_path*]images/[*$color_path*]loading/white_loading.gif"
             alt="[*'loading'|lang*]" title="[*'loading'|lang*]"></span>
        [*'chat_block'|lang*]
</legend>
<script type='text/javascript'>
    chat_no_messages = '[*'chat_no_messages'|lang|sl*]';
    chat_server_time = '[*$smarty.now*]';
    fk_ajax = '?[*fk ajax=1*]';
</script>
<script type='text/javascript' src='js/jquery.chat.js'></script>
<div class='chat_box'>
    <div id='chat_area'>[*'loading'|lang*]...</div>
</div>
[*if 'chat'|perm:2*]
    <br>
    <table class='chat_itable'>
        <tr valign='top'>
            <td class='chat_input'>
                <input type='text' id='chat_textarea' name='shout'><div class="br"></div>
                [*'chat_update'|lang*]
                <select name="update_time" onchange='chat_interval(this.value);'>
                    <option value="15" selected="selected">15 [*'chat_seconds'|lang*]</option>
                    <option value="30">30 [*'chat_seconds'|lang*]</option>
                    <option value="45">45 [*'chat_seconds'|lang*]</option>
                    <option value="60">60 [*'chat_seconds'|lang*]</option>
                </select>
            </td>
            <td class='chat_buttons'>
                <input type='submit' class='very_simple_button'  onclick='chat_say();' value='[*'chat_say'|lang*]'>
                <input type='button' class='very_simple_button'  onclick='chat_clear();' value='[*'chat_clear'|lang*]'>
                [*if 'del_chat'|perm:3*]
                    <input type='button' class='very_simple_button'  onclick='chat_truncate();' value='[*'chat_truncate'|lang*]'>
                [*/if*]
                <input type='button' class='very_simple_button'  onclick='chat_prev();' value='[*'chat_prev_messages'|lang*]'>
            </td>
        </tr>
    </table>
[*/if*]