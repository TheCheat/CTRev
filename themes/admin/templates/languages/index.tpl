[*include file='admin/tablesorter.tpl'*]
<script type='text/javascript'>
    function lang_bydefault(obj, id, c) {
        jQuery.post('[*$admin_file|sl*]&act=default&from_ajax=1', {'id':id}, function (data) {
            if (is_ok(data)) {
                jQuery('a.'+c).removeClass('hidden');
                jQuery('span.'+c).addClass('hidden');
                jQuery(obj).parent().children('.'+c).toggleClass('hidden');
                //alert(success_text);
            } else
                alert(error_text);
        });
    }
    function lang_clone(id) {
        var nid = prompt('[*'languages_enter_new_name'|lang*]');
        jQuery.post('[*$admin_file|sl*]&act=clone&from_ajax=1', {'id':id, 'new':nid}, function (data) {
            if (is_ok(data))
                window.location = '[*$admin_file|sl*]';
            else
                alert(error_text+': '+data);
        });
    }
    function show_dellang_button() {
        var e = jQuery('tr.lang_row a.lang_delbutton');
        if (jQuery('tr.lang_row').length > 1)
            e.show();
        else            
            e.hide();
    }
    jQuery(document).ready(function () {
        show_dellang_button();
    });
</script>
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'languages_title'|lang*]</legend>
        <table class='tablesorter'>
            <thead>
                <tr>
                    <th>[*'languages_language_name'|lang*]</th>
                    <th width='130'>[*'languages_language_by_default'|lang*]</th>
                    <th width='150'>[*'languages_language_rewritable'|lang*]</th>
                    <th width='100' class='js_nosort'>[*'actions'|lang*]</td>
                </tr>
            </thead>
            <tbody>
                [*foreach from=$rows item='row'*]
                    <tr id='langid_[*$row*]' class='lang_row'>
                        <td>[*"lang_$row"|lang*]([*$row*])</td>
                        <td><span class='lang_state_switch[*if 'default_lang'|config!=$row*] hidden[*/if*]'>[*'yes'|lang*]</span>
                            <a href='javascript:void(0);' onclick="lang_bydefault(this, '[*$row*]', 'lang_state_switch')" 
                               class='lang_state_switch[*if 'default_lang'|config==$row*] hidden[*/if*]'>[*'no'|lang*]</a></td>
                        <td>
                            [*assign var='lpath' value=$smarty.const.LANGUAGES_PATH*]
                            [*assign var='rw' value="$lpath/$row"|is_writable:1:1*]
                            [*if $rw==2*]
                                [*'yes'|lang*]
                            [*elseif $rw==1*]
                                [*'languages_part_rewritable'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td><a href="javascript:lang_clone('[*$row*]')"><img
                                    src="[*$atheme_path*]engine_images/add_small.png" title="[*'languages_language_copy'|lang*]"
                                    alt="[*'languages_language_copy'|lang*]"></a>
                            <a href="[*$admin_file|uamp*]&amp;act=files&amp;id=[*$row*]"><img
                                    src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                            <a href="javascript:element_delete('[*$row*]', 'langid_', show_dellang_button)" class='lang_delbutton hidden'><img
                                    src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                        </td>
                    </tr>
                [*/foreach*]
            </tbody>
        </table>
    </fieldset>
</div>