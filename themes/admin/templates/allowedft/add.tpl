<form method="post" action='[*$admin_file|uamp*]&amp;act=save'>
    <input type='hidden' name='old_name' value='[*$row.name*]'>
    <div class="cornerText gray_color2">
        <fieldset><legend>[*'allowedft_title'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'allowedft_name'|lang*]</dt>
                <dd><input type='text' name='name' size='20' value='[*$row.name*]'></dd>
                    [*assign var='cfg' value='ftypes_folder'|config*]
                <dt>[*'allowedft_image'|lang|pf:$cfg*]</dt>
                <dd><input type='text' name='image' size='40' value='[*$row.image*]'></dd>
                <dt>[*'allowedft_types'|lang*]</dt>
                <dd>
                    <textarea rows='5' cols='40' name='types'>[*$row.types*]</textarea><br>
                    <font size='1'>[*'allowedft_divide_notice'|lang*]</font>
                </dd>
                <dt>[*'allowedft_mimes'|lang*]</dt>
                <dd>
                    <textarea rows='5' cols='40' name='MIMES'>[*$row.MIMES*]</textarea><br>
                    <font size='1'>[*'allowedft_divide_notice'|lang*]</font>
                </dd>
                <dt>[*'allowedft_max_filesize'|lang*]</dt>
                <dd><input type='text' name='max_filesize' size='20' value='[*$row.max_filesize*]'></dd>
                <dt>[*'allowedft_max_width'|lang*]</dt>
                <dd><input type='text' name='max_width' size='10' value='[*$row.max_width*]'></dd>
                <dt>[*'allowedft_max_height'|lang*]</dt>
                <dd><input type='text' name='max_height' size='10' value='[*$row.max_height*]'></dd>
                <dt>[*'allowedft_options'|lang*]</dt>
                <dd><input type='checkbox' name='makes_preview' value='1'
                           [*if $row.makes_preview*] 
                               checked='checked'
                           [*/if*]>&nbsp;[*'allowedft_makes_preview'|lang*]<br>
                    <input type='checkbox' name='allowed' value='1'
                           [*if $row.allowed || !$row*] 
                               checked='checked'
                           [*/if*]>&nbsp;[*'allowedft_allowed_for_attach'|lang*]<br>
                </dd>
            </dl>
            <center>
                [*if $row.name*]
                    <input type="submit" value="[*'save'|lang*]">
                [*else*]
                    <input type="submit" value="[*'add'|lang*]">
                [*/if*]
            </center>
        </fieldset>
    </div>
</form>