[*if !$from_ajax*]
    [*include file='admin/sortable.tpl'*]
    <div class='cornerText gray_color2'>
        <fieldset><legend>[*'smilies_title'|lang*]</legend>
            <ul class='sortable_header'>
                <li>
                    <table width='100%'>
                        <tr>
                            <td width='16'></td>
                            <td width='200'>[*'smilies_area_name'|lang*]</td>
                            <td width='200'>[*'smilies_area_code'|lang*]</td>
                            <td>[*'smilies_area_image'|lang*]</td>
                            <td width='80'>[*'smilies_area_show_bbeditor'|lang*]</td>
                            <td width='75'>[*'actions'|lang*]</td>
                        </tr>
                    </table>
                </li>
            </ul>
            <ul class='sortable' id='smilies_order'>
            [*/if*]
            [*foreach from=$res item="row"*]
                <li id='smilieid_[*$row.id*]'>
                    <table width='100%'>
                        <tr>
                            <td width='16'><span class="sortable_icon"></span></td>   
                            <td width='200'>[*$row.name*]</td>                         
                            <td width='200'>[*$row.code*]</td>
                            <td><img src="[*'smilies_folder'|config*]/[*$row.image*]"
                                                 alt="[*$row.name*]" title="[*$row.name*]"></td>
                            <td width='80'>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'smilie_state_switch')" 
                                   class='smilie_state_switch[*if !$row.show_bbeditor*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'smilie_state_switch')" 
                                   class='smilie_state_switch[*if $row.show_bbeditor*] hidden[*/if*]'>[*'no'|lang*]</a>
                            </td>
                            <td width='75'>
                                <a href="javascript:element_edit('[*$row.id*]', 'smilieid_');"><img
                                        src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                <a href="javascript:element_delete('[*$row.id*]', 'smilieid_')"><img
                                        src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                            </td>
                        </tr>
                    </table>
                </li>
            [*/foreach*]
            [*if !$from_ajax*]
            </ul>
            <div align='center'>
                <input type='button' class='styled_button_big' 
                       onclick='save_order("#smilies_order");' 
                       value='[*'save_order'|lang*]'>    
                <a href="[*$admin_file|uamp*]&amp;act=files">
                    <img src="[*$atheme_path*]engine_images/add.png" align='right'
                         title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
            </div>
        </fieldset>
    </div>
[*/if*]