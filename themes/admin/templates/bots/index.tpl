[*if !$res*]
    [*message lang_var='bots_no_one' type='info'*]
[*else*]
    [*include file='admin/tablesorter.tpl'*]
    <div class='cornerText gray_color gray_border'>
        <fieldset><legend>[*'bots_title'|lang*]</legend>
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th>[*'bots_area_name'|lang*]</th>
                        <th>[*'bots_area_ip'|lang*]</th>
                        <th>[*'bots_area_agent'|lang*]</th>
                        <th class="js_nosort">[*'actions'|lang*]</th>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$res item=row*]
                        <tr id='botsid_[*$row.id*]'>
                            <td>[*$row.name*]</td>
                            <td>
                                [*if $row.firstip*]
                                    [*$row.firstip|l2ip*]
                                    [*if $row.lastip*]
                                        - [*$row.lastip|l2ip*]
                                    [*/if*]
                                [*else*]
                                    [*'no'|lang*]
                                [*/if*]
                            </td>
                            <td>
                                [*if $row.agent*]
                                    [*$row.agent|he*]
                                [*else*]
                                    [*'no'|lang*]
                                [*/if*]
                            </td>
                            <td>
                                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]">
                                    <img src="[*$atheme_path*]engine_images/edit.png"
                                         alt="[*'edit'|lang*]" title="[*'edit'|lang*]">
                                </a>
                                <a href="javascript:element_delete('[*$row.id|sl*]', 'botsid_');">
                                    <img src="[*$atheme_path*]engine_images/delete.png"
                                         alt="[*'delete'|lang*]" title="[*'delete'|lang*]">
                                </a>
                            </td>
                        </tr>
                    [*/foreach*]
                </tbody>
            </table>
        </fieldset>
    </div>
[*/if*]
<div align="right">
    <a href="[*$admin_file|uamp*]&amp;act=add">
        <img src="[*$atheme_path*]engine_images/add.png" title="[*'add'|lang*]" alt="[*'add'|lang*]">
    </a>
</div>