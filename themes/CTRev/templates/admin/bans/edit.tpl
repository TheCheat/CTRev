<tr id='banid_[*$res.id*]'>
    <td><input type="text" name="username" value="[*$res.username*]"></td>
    <td><input type="text" name="email" value="[*$res.email*]"></td>
    <td><input type="text" value="[*$res.ip_f|l2ip*]" name="ip_f" > - <br>
        <input type="text" value="[*$res.ip_t|l2ip*]" name="ip_t" > </td>
    <td>-</td>
    <td><textarea name="reason" rows='5' cols='30'>[*$res.reason*]</textarea></td>
    <td>[*select_periods current=$res.period*]<br>
        <input type='checkbox' name='update' value='1'>&nbsp;[*'update'|lang*]</td>
    <td><a href="javascript:element_edit('[*$res.id*]', 'banid_', true);"><img
                src="[*$theme_path*]engine_images/save.png" alt="[*'save'|lang*]"></a>
        <a href="javascript:element_delete('[*$res.id*]', 'banid_')"><img
                src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a></td>
</tr>