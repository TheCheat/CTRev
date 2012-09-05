<div>
    <table width='100%'>
        <tr>
            <td width='16'><span class="sortable_icon"></span></td>
            <td width='25'>[*$row.id*]</td>
            <td>[*$row.name*]([*$row.transl_name*])</td>
            <td width='130'>
                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'cat_state_switch')" 
                   class='cat_state_switch[*if !$row.post_allow*] hidden[*/if*]'>[*'yes'|lang*]</a>
                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'cat_state_switch')" 
                   class='cat_state_switch[*if $row.post_allow*] hidden[*/if*]'>[*'no'|lang*]</a>
            </td>
            <td width='50'>
                [*if $row.pattern*]
                    <a href='[*$iadmin_file|uamp*]&amp;module=patterns&amp;act=edit&amp;id=[*$row.pattern*]'>
                        [*'yes'|lang*]</a>
                    [*else*]
                        [*'no'|lang*]
                    [*/if*]</td>
            <td width='75'>
                <a href="[*$admin_file|uamp*]&amp;act=add&amp;id=[*$row.id*]"><img
                        src="[*$theme_path*]engine_images/add_small.png" title="[*'cats_add_as_child'|lang*]" 
                        alt="[*'cats_add_as_child'|lang*]"></a>
                <a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]"><img
                        src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                <a href="javascript:element_delete('[*$row.id*]', 'catid_')"><img
                        src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a></td>
        </tr>
    </table>
</div>