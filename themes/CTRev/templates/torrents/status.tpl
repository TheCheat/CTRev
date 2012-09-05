[*if !$from_ajax*]
    <script type='text/javascript'>
        function set_status(obj, id) {                
        [*if !'edit_torrents'|perm:2*]
                return;
        [*/if*]        
                var html = jQuery('div.torrents_status_selector select').clone();
                html.attr('onchange', 'save_status(this, '+id+')');
                jQuery(obj).parent().replaceWith(html);
            }
    </script>
    <div class='hidden torrents_status_selector'>
        <select>
            <option value="">-[*'nothing_selected'|lang|sl*]-</option>
            [*foreach from=$statuses key="status" item="type"*]
                <option value="[*$status*]">[*"torrents_status_$status"|lang*] - 
                    [*if !$statuses.$status*]
                        [*'torrents_status_sub_ok'|lang*]
                    [*elseif $statuses.$status==1*]
                        [*'torrents_status_sub_banned'|lang*]
                    [*else*]
                        [*'torrents_status_sub_banned'|lang*]
                        [*'torrents_status_sub_noedit'|lang*]
                    [*/if*]
                </option>
            [*/foreach*]
        </select>
    </div>
[*/if*]
<span>
    [*if !$torrents.status*]
        <a href='javascript:void(0);'
           onclick='set_status(this, "[*$torrents.id*]");'
           title='[*'torrents_status_details_unchecked'|lang*]'>
            [*'torrents_status_unchecked'|lang*]
        </a>
    [*else*]
        [*assign var='status' value=$torrents.status*]
        <a href='javascript:void(0);'
           onclick='set_status(this, "[*$torrents.id*]");'
           title='[*"torrents_status_details_$status"|lang*]'>[*"torrents_status_pre_$status"|lang*]
            [*"torrents_status_$status"|lang*]&nbsp;-[*if !$statuses.$status*]
                <span style="color: green;">[*'torrents_status_sub_ok'|lang*]</span>
            [*elseif $statuses.$status==1*]
                <span style="color: red;">[*'torrents_status_sub_banned'|lang*]</span>
            [*else*]
                <span style="color: red;">[*'torrents_status_sub_banned'|lang*][*'torrents_status_sub_noedit'|lang*]</span>
            [*/if*]
        </a>:&nbsp;[*$torrents.su|gcl:$torrents.sg*]
    [*/if*]
</span>