[*include file='admin/sortable.tpl'*]
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'groups_title'|lang*]</legend>
        <ul class='sortable_header'>
            <li>
                <table width='100%'>
                    <tr>
                        <td width='16'></td>
                        <td width='16'>[*'groups_group_id'|lang*]</td>
                        <td width='50%'>[*'groups_group_name'|lang*]</td>
                        <td>[*'groups_group_types'|lang*]</td>
                        <td width='75'>[*'actions'|lang*]</td>
                    </tr>
                </table>
            </li>
        </ul>
        <ul class='sortable' id='groups_order'>
            [*foreach from=$groups item="row"*]
                <li id='groupid_[*$row.id*]'>
                    <table width='100%'>
                        <tr>
                            <td width='16'><span class="sortable_icon"></span></td>
                            <td width='16'>[*$row.id*]</td>
                            <td width='50%'><b><a href='[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]'>[*$row.id|gc*]</a></b><br>
                                ([*$rows[$row.id]+0*] [*'groups_group_n_users'|lang*])</td>
                            <td>
                                [*assign var='c' value='0'*]
                                [*foreach from=$params key='num' item='par'*]
                                    [*if $row.$par*]
                                        [*if $c*]
                                            |
                                        [*/if*]
                                        [*"groups_parameters_$par"|lang*]<!--[*$c++*]-->
                                    [*/if*]
                                [*/foreach*]
                                [*if !$c*]
                                    [*'nothing'|lang*]
                                [*/if*]
                            </td>
                            <td width='75'>
                                <a href="[*$admin_file|uamp*]&amp;act=add&amp;id=[*$row.id*]">
                                    <img src="[*$theme_path*]engine_images/add_small.png" title="[*'groups_add_by_this'|lang*]"
                                         alt="[*'groups_add_by_this'|lang*]">
                                </a>
                                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]"><img
                                        src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                    [*if !$row.notdeleted*]
                                    <a href="javascript:element_delete('[*$row.id*]', 'groupid_')"><img
                                            src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                                    [*/if*]
                            </td>
                        </tr>
                    </table>
                </li>
            [*/foreach*]
        </ul>
        <div align='center'><input type='button' class='styled_button_big' 
                                   onclick='save_order("#groups_order");' 
                                   value='[*'save_order'|lang*]'></div>
    </fieldset>
</div>