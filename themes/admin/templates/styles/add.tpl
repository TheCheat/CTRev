<form action='[*$admin_file|uamp*]&amp;act=save' method="post">
    <input type='hidden' name='id' value='[*$id*]'>
    <input type='hidden' name='file' value='[*$file*]'>
    <input type='hidden' name='folder' value='[*$folder*]'>
    <div class='cornerText gray_border gray_color'>
        <fieldset>
            <legend>
                [*if $file*]
                    [*'styles_edit_title'|pf:$filename:$id*]
                [*else*]
                    [*'styles_add_title'|pf:$id*]
                [*/if*]
                &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                <a href='[*$admin_file*]&amp;act=files&amp;id=[*$id*]&amp;folder=[*$parent|ue*]'>
                    [*'back'|lang*]
                </a>
            </legend>
            <dl class='info_text'>
                <dt>[*'styles_filename'|lang*]</dt>
                <dd><input type='text' name='filename' value='[*$filename*]' size='50'></dd>
                <dt>[*'styles_is_writable'|lang*]</dt>
                <dd>
                    [*if $is_writable*]
                        [*'yes'|lang*]
                    [*else*]
                        [*'no'|lang*]
                    [*/if*]
                </dd>
                <dt>[*'styles_file_content'|lang*]</dt>
                <dd><textarea name='content' rows='15' cols='65'>[*$contents|he:false:true*]</textarea></dd>
            </dl>
            <div align='center'>
                <input type='submit' value='[*'save'|lang*]'>
            </div>
        </fieldset>
    </div>
</form>