[*include file='admin/tablesorter.tpl'*]
<script type='text/javascript'>
    function style_bydefault(obj, id, c) {
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
    function style_clone(id) {
        var nid = prompt('[*'styles_enter_new_name'|lang*]');
        jQuery.post('[*$admin_file|sl*]&act=clone&from_ajax=1', {'id':id, 'new':nid}, function (data) {
            if (is_ok(data))
                window.location = '[*$admin_file|sl*]';
            else
                alert(error_text+': '+data);
        });
    }
    function show_delstyle_button() {
        var e = jQuery('tr.style_row a.style_delbutton');
        if (jQuery('tr.style_row').length > 1)
            e.show();
        else            
            e.hide();
    }
    jQuery(document).ready(function () {
        show_delstyle_button();
    });
</script>
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'styles_title'|lang*]</legend>
        <table class='tablesorter'>
            <thead>
                <tr>
                    <th>[*'styles_style_name'|lang*]</th>
                    <th width='120'>[*'styles_style_parent'|lang*]</th>
                    <th width='100'>[*'styles_style_comp'|lang*]</th>
                    <th width='100'>[*'styles_style_by_default'|lang*]</th>
                    <th width='120'>[*'styles_style_rewritable'|lang*]</th>
                    <th width='100' class='js_nosort'>[*'actions'|lang*]</td>
                </tr>
            </thead>
            <tbody>
                [*foreach from=$rows item='row'*]
                    [*assign var='rconf' value=$row|get_style_conf:''*]
                    <tr id='styleid_[*$row*]' class='style_row'>
                        <td>[*$rconf.style_name*]
                            [*'styles_style_version'|lang*][*$rconf.style_version*]([*$row*])<br>
                            [*'styles_style_author'|lang*]<b>[*$rconf.style_author*]</b></td>
                        <td>
                            [*if $rconf.style_parent*]
                                [*$rconf.style_parent*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td>
                            [*if $rconf.compatibility_best==$smarty.const.ENGINE_VERSION*]
                                [*'styles_style_comp_best'|lang*]
                            [*elseif $rconf.compatibility_min<=$smarty.const.ENGINE_VERSION && $rconf.compatibility_max>=$smarty.const.ENGINE_VERSION*]
                                [*'yes'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td><span class='style_state_switch[*if 'default_style'|config!=$row*] hidden[*/if*]'>[*'yes'|lang*]</span>
                            <a href='javascript:void(0);' onclick="style_bydefault(this, '[*$row*]', 'style_state_switch')" 
                               class='style_state_switch[*if 'default_style'|config==$row*] hidden[*/if*]'>[*'no'|lang*]</a></td>
                        <td>[*'styles_rewritable_templates'|lang*]
                            [*assign var='tpath' value=$smarty.const.THEMES_PATH*]
                            [*assign var='tplpath' value=$smarty.const.TEMPLATES_PATH*]
                            [*assign var='rw' value="$tpath/$row/$tplpath"|is_writable:1:1*]
                            [*if $rw==2*]
                                [*'yes'|lang*]
                            [*elseif $rw==1*]
                                [*'styles_part_rewritable'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                            <br>
                            [*'styles_rewritable_css'|lang*]
                            [*assign var='rw' value="$tpath/$row/css"|is_writable:1:1*]
                            [*if $rw==2*]
                                [*'yes'|lang*]
                            [*elseif $rw==1*]
                                [*'styles_part_rewritable'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                            <br>
                            [*'styles_rewritable_js'|lang*]
                            [*assign var='rw' value="$tpath/$row/js"|is_writable:1:1*]
                            [*if $rw==2*]
                                [*'yes'|lang*]
                            [*elseif $rw==1*]
                                [*'styles_part_rewritable'|lang*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td><a href="javascript:style_clone('[*$row*]')"><img
                                    src="[*$atheme_path*]engine_images/add_small.png" title="[*'styles_style_copy'|lang*]"
                                    alt="[*'styles_style_copy'|lang*]"></a>
                            <a href="[*$admin_file|uamp*]&amp;act=files&amp;id=[*$row*]"><img
                                    src="[*$atheme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>
                            <a href="javascript:element_delete('[*$row*]', 'styleid_', show_delstyle_button)" class='style_delbutton hidden'><img
                                    src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                        </td>
                    </tr>
                [*/foreach*]
            </tbody>
        </table>
    </fieldset>
</div>