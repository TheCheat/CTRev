<tr id='warnid_[*$res.id*]'>
    <td>[*$res.username|gcl:$res.group*]</td>
    <td>-</td>
    <td>-</td>
    <td><textarea name="reason" rows='5' cols='30'>[*$res.reason*]</textarea></td>
    <td><a href="javascript:element_edit('[*$res.id*]', 'warnid_', true);"><img
                src="[*$theme_path*]engine_images/save.png" alt="[*'save'|lang*]"></a>
        <a href="javascript:element_delete('[*$res.id*]', 'warnid_')"><img
                src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a></td>
</tr>