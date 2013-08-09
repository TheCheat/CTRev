[*include file='admin/tablesorter.tpl'*]
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'patterns_title'|lang*]</legend>
        [*if !$rows*]
            [*message type='info' lang_var='patterns_none'*]
        [*else*]
            <table class='tablesorter'>
                <thead>
                    <tr>
                        <th>[*'patterns_pattern_id'|lang*]</th>
                        <th>[*'patterns_pattern_name'|lang*]</th>
                        <th class='js_nosort'>[*'actions'|lang*]</td>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$rows item='row'*]
                        <tr id='pattid_[*$row.id*]'>
                            <td>[*$row.id*]</td>
                            <td>[*$row.name*]</td>
                            <td><a href="[*$admin_file|uamp*]&amp;act=add&amp;id=[*$row.id*]"><img
                                        src="[*$atheme_path*]engine_images/add_small.png" title="[*'patterns_add_by_this'|lang*]"
                                        alt="[*'groups_add_by_this'|lang*]"></a>
                                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]"><img
                                        src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                <a href="javascript:element_delete('[*$row.id*]', 'pattid_')"><img
                                        src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                            </td>
                        </tr>
                    [*/foreach*]
                </tbody>
            </table>
        [*/if*]
        <div align='right'>          
            <a href="[*$admin_file|uamp*]&amp;act=add">
                <img src="[*$atheme_path*]engine_images/add.png"
                     title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
        </div>
    </fieldset>
</div>