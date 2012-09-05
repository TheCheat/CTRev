[*if !$res*]    
    [*message lang_var='bans_no_banned' type='info'*]
[*else*]
    [*if !$from_ajax*]
        [*include file='admin/tablesorter.tpl'*]
        <div class='cornerText gray_color2'>
            <fieldset><legend>[*'bans_title'|lang*]</legend>
                <table class="tablesorter">
                    <thead>
                        <tr>
                            <th>[*'bans_blocked_user'|lang*]</th>
                            <th>[*'bans_blocked_email'|lang*]</th>
                            <th>[*'bans_blocked_ip'|lang*]</th>
                            <th>[*'bans_banner'|lang*]</th>
                            <th>[*'bans_block_reason'|lang*]</th>
                            <th>[*'bans_to'|lang*]</th>
                            <th class="js_nosort">[*'actions'|lang*]</th>
                        </tr>
                    </thead>
                    <tbody>
                    [*/if*]
                    [*foreach from=$res item='row'*]
                        <tr id='banid_[*$row.id*]'>
                            <td>[*if $row.bu*][*$row.bu|gcl:$row.bg*][*else*]-[*/if*]</td>
                            <td>[*if $row.email*][*$row.email*][*else*]-[*/if*]</td>
                            <td>[*if $row.ip_f*][*$row.ip_f|l2ip*][*if $row.ip_t*] - [*$row.ip_t|l2ip*][*/if*][*else*]-[*/if*]</td>
                            <td>[*$row.username|gcl:$row.group*]</td>
                            <td>[*$row.reason*]</td>
                            <td>[*if $row.to_time*][*date time=$row.to_time format="ymdhis"*][*else*][*'bans_no_end'|lang*][*/if*]</td>
                            <td><a href="javascript:element_edit('[*$row.id*]', 'banid_');"><img
                                        src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                <a href="javascript:element_delete('[*$row.id*]', 'banid_')"><img
                                        src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                            </td>
                        </tr>
                    [*/foreach*]
                    [*if !$from_ajax*]
                    </tbody>
                </table>
                <div align="right"><a href="[*$admin_file|uamp*]&amp;act=add">
                        <img src="[*$theme_path*]engine_images/add.png" title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
                </div>
            </fieldset>
        </div>
    [*/if*]
[*/if*]