[*if !$from_ajax*]
    <script type='text/javascript'>
        function set_status(obj, id) {
        [*if !'edit_content'|perm:2*]
            return;
        [*/if*]
            var html = jQuery('div.torrent_status_selector select').clone();
            html.attr('onchange', 'save_status(this, ' + id + ')');
            jQuery(obj).parent().replaceWith(html);
        }
    </script>
    <div class='hidden torrent_status_selector'>
        <select>
            <option value="">-[*'nothing_selected'|lang|sl*]-</option>
            [*foreach from=$statuses key="status" item="type"*]
                <option value="[*$status*]">[*"torrent_status_$status"|lang*] - 
                    [*if !$statuses.$status*]
                        [*'torrent_status_sub_ok'|lang*]
                    [*elseif $statuses.$status==1*]
                        [*'torrent_status_sub_banned'|lang*]
                    [*else*]
                        [*'torrent_status_sub_banned'|lang*]
                        [*'torrent_status_sub_noedit'|lang*]
                    [*/if*]
                </option>
            [*/foreach*]
        </select>
    </div>
[*/if*]
<b>
    <font size='3'>
    <span>
        [*if !$content.status*]
            <a href='javascript:void(0);'
               onclick='set_status(this, "[*$content.id*]");'
               title='[*'torrent_status_details_unchecked'|lang*]'>
                [*'torrent_status_unchecked'|lang*]
            </a>
        [*else*]
            [*assign var='status' value=$content.status*]
            <a href='javascript:void(0);'
               onclick='set_status(this, "[*$content.id*]");'
               title='[*"torrent_status_details_$status"|lang*]'>[*"torrent_status_pre_$status"|lang*]
                [*"torrent_status_$status"|lang*]&nbsp;-[*if !$statuses.$status*]
                    <span style="color: green;">[*'torrent_status_sub_ok'|lang*]</span>
                [*elseif $statuses.$status==1*]
                    <span style="color: red;">[*'torrent_status_sub_banned'|lang*]</span>
                [*else*]
                    <span style="color: red;">[*'torrent_status_sub_banned'|lang*][*'torrent_status_sub_noedit'|lang*]</span>
                [*/if*]
            </a>:&nbsp;[*$content.su|gcl:$content.sg*]
        [*/if*]
    </span>
    </font>
</b>