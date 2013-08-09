<div class='cornerText gray_color2'>
    <fieldset><legend>[*'userfields_title'|lang*]</legend>
        [*if !$res*]    
            [*message lang_var='userfields_nothing' type='info'*]
        [*else*]
            [*include file='admin/sortable.tpl'*]
            <ul class='sortable_header'>
                <li>
                    <table width='100%'>
                        <tr>
                            <td width='150'>[*'userfields_field'|lang*]</td>
                            <td>[*'userfields_name'|lang*]</td>
                            <td width='100'>[*'userfields_type'|lang*]</td>
                            <td width='130'>[*'userfields_show_register'|lang*]</td>
                            <td width='130'>[*'userfields_show_profile'|lang*]</td>
                            <td width='75'>[*'actions'|lang*]</td>
                        </tr>
                    </table>
                </li>
            </ul>
            <ul class='sortable' id='userfields_order'>
                [*foreach from=$res item='row'*]
                    <li id='ufid_[*$row.field*]'>
                        <table width='100%'>
                            <tr>
                                <td width='150'>[*$row.field*]</td>
                                <td>[*$row.name*]</td>
                                <td width='100'>[*"userfields_type_`$row.type`"|lang|cut_type_descr*]</td>
                                <td width='130'>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.field*]', 'ufr_state_switch', 'show_register')" 
                                       class='ufr_state_switch[*if !$row.show_register*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.field*]', 'ufr_state_switch', 'show_register')" 
                                       class='ufr_state_switch[*if $row.show_register*] hidden[*/if*]'>[*'no'|lang*]</a>
                                </td>
                                <td width='130'>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.field*]', 'ufp_state_switch', 'show_profile')" 
                                       class='ufp_state_switch[*if !$row.show_profile*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.field*]', 'ufp_state_switch', 'show_profile')" 
                                       class='ufp_state_switch[*if $row.show_profile*] hidden[*/if*]'>[*'no'|lang*]</a>
                                </td>
                                <td width='75'><a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.field*]"><img
                                            src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                    <a href="javascript:element_delete('[*$row.field*]', 'ufid_')"><img
                                            src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                                </td>
                            </tr>
                        </table>
                    </li>
                [*/foreach*]
            </ul>
        [*/if*]
        <div align="center">
            [*if $res*]    
                <input type='button' class='styled_button_big' 
                       onclick='save_order("#userfields_order");' 
                       value='[*'save_order'|lang*]'>  
            [*/if*]
            <a href="[*$admin_file|uamp*]&amp;act=add">
                <img src="[*$atheme_path*]engine_images/add.png" align='right'
                     title="[*'add'|lang*]" alt="[*'add'|lang*]">
            </a>
        </div>
    </fieldset>
</div>