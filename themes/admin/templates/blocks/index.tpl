[*include file='admin/sortable.tpl'*]
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'blocks_title'|lang*]</legend>
        <ul class='sortable_header'>
            <li>
                <table width='100%'>
                    <tr>
                        <td width='16'></td>
                        <td width='16'>[*'blocks_block_id'|lang*]</td>
                        <td width='50%'>[*'blocks_block_name'|lang*]</td>
                        <td>[*'blocks_block_template'|lang*]</td>
                        <td width='50'>[*'blocks_block_enabled'|lang*]</td>
                        <td width='75'>[*'actions'|lang*]</td>
                    </tr>
                </table>
            </li>
        </ul>
        [*foreach from=$types item="type"*]
            <ul class='sortable blocks_order'>
                <li class='sortable_disabled'>
                    <div class='padding_left'>[*'blocks_block_type'|lang*]
                        <b>[*"blocks_block_type_$type"|lang*]</b>
                    </div>
                </li>
                [*foreach from=$rows.$type item='row'*]
                    <li id='blockid_[*$row.id*]'>
                        <table width='100%'>
                            <tr>
                                <td width='16'><span class="sortable_icon"></span></td>
                                <td width='16'>[*$row.id*]</td>
                                <td width='50%'>[*$row.title*]([*$row.file*])</td>
                                <td><b>
                                        [*if $row.tpl*]
                                            [*$row.tpl*]
                                        [*else*]
                                            -
                                        [*/if*]
                                    </b>
                                </td>
                                <td width='50'>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'block_state_switch')" 
                                       class='block_state_switch[*if !$row.enabled*] hidden[*/if*]'>[*'yes'|lang*]</a>
                                    <a href='javascript:void(0);' onclick="switch_element_state(this, '[*$row.id*]', 'block_state_switch')" 
                                       class='block_state_switch[*if $row.enabled*] hidden[*/if*]'>[*'no'|lang*]</a>
                                </td>
                                <td width='75'><a href="[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$row.id*]"><img
                                            src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                                    <a href="javascript:element_delete('[*$row.id*]', 'blockid_')"><img
                                            src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                                </td>
                            </tr>
                        </table>
                    </li>
                [*/foreach*]
            </ul>
        [*/foreach*]
        <div align='center'><input type='button' class='styled_button_big' 
                                   onclick='save_order(".blocks_order");' 
                                   value='[*'save_order'|lang*]'>            
            <a href="[*$admin_file|uamp*]&amp;act=add">
                <img src="[*$atheme_path*]engine_images/add.png" align='right'
                     title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
        </div>
    </fieldset>
</div>