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
                        <th>[*'static_area_type'|lang*]</th>
                        <th class="js_nosort">[*'actions'|lang*]</th>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$res item=row*]
                        <tr id='staticid_[*$row.id*]'>
                            <td><a href='[*gen_link module='static' page=$row.url*]' target="blank">[*$row.url*]</a></td>
                            <td>[*$row.title*]</td>
                            <td>
                                [*"static_area_`$row.type`"|lang*]
                            </td>
                            <td>
                                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]">
                                    <img src="[*$atheme_path*]engine_images/edit.png"
                                         alt="[*'edit'|lang*]" title="[*'edit'|lang*]">
                                </a>
                                <a href="javascript:element_delete('[*$row.id|sl*]', 'staticid_');">
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