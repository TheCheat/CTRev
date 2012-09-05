[*if !$res*]
    [*message lang_var='warnings_no_warned' type='info'*]
[*else*]
    [*if !$from_ajax*]
        [*include file='admin/tablesorter.tpl'*]
        <div class='cornerText gray_color2'>
            <fieldset><legend>[*'warnings_title'|lang*]</legend>
                <table class="tablesorter">
                    <thead>
                        <tr>
                            <th>[*'warnings_warned_user'|lang*]</th>
                            <th>[*'warnings_warnby'|lang*]</th>
                            <th>[*'warnings_warn_time'|lang*]</th>
                            <th>[*'warnings_warn_reason'|lang*]</th>
                            <th class="js_nosort">[*'actions'|lang*]</th>
                        </tr>
                    </thead>
                    <tbody>
                    [*/if*]
                    [*foreach from=$res item=row*]
                        <tr id='warnid_[*$row.id*]'>
                            <td>[*$row.bu|gcl:$row.bg*]</td>
                            <td>[*$row.username|gcl:$row.group*]</td>
                            <td>[*date time=$row.time format="ymdhis"*]</td>
                            <td>[*$row.reason*]</td>
                            <td>
                                <a href="javascript:element_edit('[*$row.id*]', 'warnid_');"><img
                                        src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                <a href="javascript:element_delete('[*$row.id*]', 'warnid_')"><img
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