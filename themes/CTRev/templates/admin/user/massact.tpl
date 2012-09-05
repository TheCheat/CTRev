<script type='text/javascript'>            
    var do_postdata = null;
    var do_act = null;
    function do_with_users(cboxs, action) {                
        do_postdata = jQuery(cboxs).serialize();
        if (!do_postdata) {
            alert("[*'nothing_selected'|lang|sl*]");
            return;
        }
        do_act = jQuery(action).val();
        if (!confirm("[*'are_you_sure_to_do_this'|lang|sl*]"))
        return;
        var pp = 'action_popup_'+do_act;
        if (jQuery('#'+pp).length) {
            if (!jQuery('#'+pp).is('.inited_search_f')) {
                jQuery('#'+pp).addClass('inited_search_f').append('<center><input type="submit" \
                    value="[*'submit'|lang|sl*]">&nbsp;<input type="button" \
                    value="[*'cancel'|lang|sl*]" onclick="close_popup();"></center>');  
                // Обычный wrapAll не хотит работать, хм.. ну и хрен с ним.
                var data = jQuery('#'+pp).html();
                jQuery('#'+pp).empty();
                jQuery('#'+pp).prepend('<form action="javascript:void(0);" onsubmit="submit_action(this);">'
                    + data
                    + '</form>');                        
            }
            init_popup(pp);
        } else
            submit_action();
    }
            
    function submit_action(form) {
        close_popup();
        var fdata = (form?jQuery(form).serialize()+'&':"")+do_postdata+'&mode='+do_act;
        jQuery.post('[*$eadmin_file|sl*]&module=users&act=massact&from_ajax=1', fdata, function (data) {
            if (data=='OK!') 
                alert('[*'success'|lang|sl*]!');
            else
                alert('[*'error'|lang|sl*]: '+data);                        
        });
    }
                
</script>
<div class='hidden' id='action_popup_ban'>
    <dl class='info_text'>                
        <dt>[*'usearch_block_period'|lang*]</dt>
        <dd>[*select_periods*]</dd>
        <dt>[*'usearch_block_reason'|lang*]</dt>
        <dd><textarea name="reason" rows='5' cols='30'></textarea></dd>
    </dl>
</div>
[*if 'system'|perm*]
    <div class='hidden' id='action_popup_change_group'><b>[*'usearch_group'|lang*]:</b>&nbsp;[*select_groups not_null=true*]</div>
[*/if*]
<div class='hidden' id='action_popup_delete_content'>
    <dl class='info_text'>                
        <dt>[*'usearch_choose_content_to_delete'|lang*]</dt>
        <dd><input type='checkbox' name='content[torrents]' value='1'>&nbsp;[*'usearch_content_torrents'|lang*]<br>
            <input type='checkbox' name='content[comments]' value='1'>&nbsp;[*'usearch_content_comments'|lang*]<br>
            <input type='checkbox' name='content[polls]' value='1'>&nbsp;[*'usearch_content_polls'|lang*]</dd>
    </dl>
</div>