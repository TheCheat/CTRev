<div class='cornerText gray_color2'>
    <fieldset><legend>[*'allowedft_title'|lang*]</legend>
        [*if !$res*]    
            [*message lang_var='allowedft_nothing' type='info'*]
        [*else*]
            [*include file='admin/tablesorter.tpl'*]
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th width='30%'>[*'allowedft_name'|lang*]</th>
                        <th width='40%'>[*'allowedft_types'|lang*]</th>
                        <th>[*'allowedft_allowed_for_attach'|lang*]</th>
                        <th>[*'allowedft_makes_preview'|lang*]</th>
                        <th class="js_nosort">[*'actions'|lang*]</th>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$res item='row'*]
                        <tr id='aftid_[*$row.name*]'>
                            <td>
                                [*if $row.image*]
                                    <img src="[*'ftypes_folder'|config*]/[*$row.image*]" alt="[*$row.name*]"
                                         style='margin-bottom: -4px;'>
                                [*/if*]
                                [*$row.name*]
                            </td>
                            <td>[*$row.types*]</td>
                            <td>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.name*]', 'afta_state_switch', 'allowed')" 
                                   class='afta_state_switch[*if !$row.allowed*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.name*]', 'afta_state_switch', 'allowed')" 
                                   class='afta_state_switch[*if $row.allowed*] hidden[*/if*]'>[*'no'|lang*]</a>
                            </td>
                            <td>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.name*]', 'aftmp_state_switch', 'makes_preview')" 
                                   class='aftmp_state_switch[*if !$row.makes_preview*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.name*]', 'aftmp_state_switch', 'makes_preview')" 
                                   class='aftmp_state_switch[*if $row.makes_preview*] hidden[*/if*]'>[*'no'|lang*]</a>
                            </td>
                            <td><a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.name*]"><img
                                        src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                    [*if !$row.name|aftbasic*]
                                    <a href="javascript:element_delete('[*$row.name*]', 'aftid_')"><img
                                            src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                                    [*/if*]
                            </td>
                        </tr>
                    [*/foreach*]
                </tbody>
            </table>
        [*/if*]
        <div align="right">
            <a href="[*$admin_file|uamp*]&amp;act=add">
                <img src="[*$atheme_path*]engine_images/add.png" title="[*'add'|lang*]" alt="[*'add'|lang*]">
            </a>
        </div>
    </fieldset>
</div>