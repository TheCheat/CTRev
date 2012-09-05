[*if !$res*]
    [*message lang_var='static_no_one' type='info'*]
[*else*]
    [*include file='admin/tablesorter.tpl'*]
    <div class='cornerText gray_color gray_border'>
        <fieldset><legend>[*'static_title'|lang*]</legend>
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th>[*'static_area_url'|lang*]</th>
                        <th>[*'static_area_title'|lang*]</th>
                        <th>[*'static_area_bbcode'|lang*]</th>
                        <th class="js_nosort">[*'actions'|lang*]</th>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$res item=row*]
                        <tr id='staticid_[*$row.id*]'>
                            <td>[*$row.url*]</td>
                            <td>[*$row.title*]</td>
                            <td>
                                [*if $row.bbcode*]
                                    [*'yes'|lang*]
                                [*else*]
                                    [*'no'|lang*]
                                [*/if*]
                            </td>
                            <td>
                                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]">
                                    <img src="[*$theme_path*]engine_images/edit.png"
                                         alt="[*'edit'|lang*]" title="[*'edit'|lang*]">
                                </a>
                                <a href="javascript:element_delete('[*$row.id|sl*]', 'staticid_');">
                                    <img src="[*$theme_path*]engine_images/delete.png"
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
        <img src="[*$theme_path*]engine_images/add.png" title="[*'add'|lang*]" alt="[*'add'|lang*]">
    </a>
</div>