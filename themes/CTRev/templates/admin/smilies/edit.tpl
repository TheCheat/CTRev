<li id='smilieid_[*$row.id*]'>
    <table width='100%'>
        <tr>
            <td width='16'><span class="sortable_icon"></span></td>   
            <td width='200'><input type='text' value='[*$row.name*]' name='name' size='14'></td>                         
            <td width='200'><input type='text' value='[*$row.code*]' name='code' size='14'></td>
            <td><input type='text' value='[*$row.image*]' name='image' size='30'></td>
            <td width='80'><input type='checkbox' name='show_bbeditor' value='1'
                                  [*if $row.show_bbeditor*]
                                      checked='checked'
                                  [*/if*]></td>
            <td width='75'>
                <a href="javascript:element_edit('[*$row.id*]', 'smilieid_', true);"><img
                        src="[*$theme_path*]engine_images/save.png" alt="[*'save'|lang*]"></a>
                <a href="javascript:element_delete('[*$row.id*]', 'smilieid_')"><img
                        src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
            </td>
        </tr>
    </table>
</li>