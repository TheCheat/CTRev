[*if !$from_ajax*]
    <script type='text/javascript'>
        function choose_file(file, parent, isdir) {
            if (isdir) {
                if (file==-1)
                    file = parent+'../';
                else
                    file = parent+file;
                jQuery.post('[*$admin_file|sl*]&act=files&id=[*$id|sl*]&from_ajax=1&nno=1', 
                {'folder':file}, function (data) {
                    if (data.substr(0, 3)=='OK!') {
                        jQuery('#addbutton_filechooser').remove();
                        jQuery('#file_chooser').replaceWith(data.substr(3));
                    } else
                        alert(error_text+data);
                });
            } else
                window.location = '[*$admin_file|sl*]&act=edit&id=[*$id|sl*]&file='+urlencode(parent+file);
        }
        function delete_file(file, parent) {
            if (!confirm('[*'filechooser_sure_to_delete_this_file'|lang|sl*]'))
            return;
            file = parent+file;
            jQuery.post('[*$admin_file|sl*]&act=delete_file&from_ajax=1', {'file':file,
                'id':'[*$id|sl*]'}, function (data) {
                if (data=='OK!')
                    choose_file(parent, '', true);
                else
                    alert(error_text+':'+data);
            });
        }
    </script>
    <div class='cornerText gray_border gray_color'>
        <fieldset><legend>[*'filechooser_title'|lang*]</legend>
        [*/if*]
        <table class='tablesorter' id='file_chooser'>
            <thead>
                <tr>
                    <th>[*'filechooser_file_name'|lang*]</th>
                    <th width='130'>[*'filechooser_file_rewritable'|lang*]</th>
                    <th width='150'>[*'filechooser_file_size'|lang*]</th>
                    <th width='150'>[*'filechooser_file_last_changed'|lang*]</th>
                </tr>
            </thead>
            <tbody>
                [*if $parent*]
                    <tr>
                        <td><a href='javascript:choose_file(-1, "[*$parent|sl*]", true);'>
                                &crarr;&nbsp;[*'back'|lang*]</a></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                [*/if*]
                [*foreach from=$files key='file' item='e'*]
                    <tr>
                        <td><a href='javascript:choose_file("[*$file|sl*]", "[*$parent|sl*]", [*if $e[0]*]true[*else*]false[*/if*])'>
                                [*if $e[0]*]
                                    &rarr;
                                [*/if*][*$file*]</a>

                            [*if !$deny_modify*]
                                <a href='javascript:delete_file("[*$file|sl*]", "[*$parent|sl*]")' class='remove_lang_catfield'>
                                    <img src="[*$theme_path*]engine_images/delete.png" width='12'
                                         alt="[*'delete'|lang*]" title="[*'delete'|lang*]">
                                </a>
                            [*/if*]
                        </td>
                        <td>
                            [*if $e[1]*]
                                [*'yes'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td>[*$e[2]|cs*]</td>
                        <td>[*date time=$e[3]*]</td>
                    </tr>
                [*/foreach*]
            </tbody>
        </table>
        [*if !$deny_modify*]
            <div align='right' id='addbutton_filechooser'>
                <a href="[*$admin_file|uamp*]&amp;act=add&amp;id=[*$id*]&amp;file=[*$parent|ue*]">
                    <img src="[*$theme_path*]engine_images/add.png" align='right'
                         title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
            </div>
        [*/if*]
        [*if !$from_ajax*]
        </fieldset>
    </div>
[*/if*]