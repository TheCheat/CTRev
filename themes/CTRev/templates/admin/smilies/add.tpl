[*include file='admin/sortable.tpl'*]
<script type='text/javascript'>
    function check_smilies_remains() {        
        var l = jQuery('#smilies_addbox').children('li').length;
        jQuery('ul li').each(function () {
            var o = jQuery('table td:last', this);
            if (l<2)
                o.hide();
            else
                o.show();
        });        
    }
    function smilie_delete(obj) {
        jQuery(obj).parents('li').fadeOut(400, function () {
            jQuery(this).remove();
            check_smilies_remains();
        });
    }
    function reload_smilie_image(obj) {
        obj = jQuery(obj);
        var li = jQuery(obj).parents('li');
        var name = jQuery('input[name^="name"]', li).val();
        var image = jQuery('input[name^="image"]', li).val();
        obj.attr('alt', name).attr('title', name);
        obj.attr('src',  baseurl+'[*'smilies_folder'|config|sl*]/'+image);
    }
    function smilie_add() {
        var obj = jQuery('#smilies_addbox');
        var li = jQuery('li:last', obj).clone();
        jQuery('input[type="text"]', li).val('');
        jQuery('input[type="checkbox"]', li).removeAttr('checked');
        incrase_name_num(jQuery('input', li));
        reload_smilie_image(jQuery('img.preview_smilie', li));
        obj.append(li);
        check_smilies_remains();
    }
    jQuery(document).ready(function () {
        check_smilies_remains();
    });
</script>
<form action='[*$admin_file|uamp*]&amp;act=save' method='post'>
    <div class='cornerText gray_color2'>
        <fieldset>
            <legend>[*"smilies_smilie_add"|lang*]</legend>
            <ul class='sortable_header'>
                <li>
                    <table width='100%'>
                        <tr>
                            <td width='16'></td>
                            <td width='90'>[*'smilies_area_preview'|lang*]</td>
                            <td width='150'>[*'smilies_area_name'|lang*]</td>
                            <td width='150'>[*'smilies_area_code'|lang*]</td>
                            <td>[*'smilies_area_image'|lang*]</td>
                            <td width='90'>[*'smilies_area_show_bbeditor'|lang*]                            
                                <input type='checkbox' onclick='select_all(this, "input.show_bbeditor")'>
                            </td>
                            <td width='50'>[*'delete'|lang*]</td>
                        </tr>
                    </table>
                </li>
            </ul>
            <ul class='sortable' id='smilies_addbox'>
                [*assign var='num' value='0'*]
                [*foreach from=$smilies key='image' item="row"*]
                    <li>
                        <table width='100%'>
                            <tr>
                                <td width='16'><span class="sortable_icon"></span></td>  
                                <td width='90'>
                                    <img src="[*$BASEURL*][*'smilies_folder'|config*]/[*$image*]"
                                         alt="[*$row[1]*]" title="[*$row[1]*]" 
                                         class=' cornerText preview_smilie clickable'
                                         onclick='reload_smilie_image(this);'>
                                </td>                                    
                                <td width='150'>
                                    <input type='text' value='[*$row[1]*]' name='name[[*$num*]]' size='14'>
                                </td>                         
                                <td width='150'>
                                    <input type='text' value='[*$row[0]*]' name='code[[*$num*]]' size='14'>
                                </td>
                                <td>
                                    <input type='text' value='[*$image*]' name='image[[*$num*]]' size='30'>
                                </td>
                                <td width='90'>
                                    <input type='checkbox' value='1' class='show_bbeditor' name='show_bbeditor[[*$num*]]'>
                                </td>
                                <td width='50'>
                                    <a href="javascript:void(0);" onclick="smilie_delete(this);">
                                        <img src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </li>
                    <!--[*$num++*]-->
                [*/foreach*]
            </ul>
            <div align='center'>
                <input type='submit' value='[*'save'|lang*]'>                
                <a href="javascript:smilie_add();">
                    <img src="[*$theme_path*]engine_images/add.png" align='right'
                         title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
            </div>
        </fieldset>
    </div>
</form>