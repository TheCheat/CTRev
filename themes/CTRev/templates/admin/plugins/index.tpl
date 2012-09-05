[*if $trewritable==2*]
    [*assign var='trewritable' value="plugins_rewritable_yes"|lang*]
    [*assign var='ttype' value="success"*]
[*elseif $trewritable==1*]
    [*assign var='trewritable' value="plugins_rewritable_piece"|lang*]
    [*assign var='ttype' value="info"*]
[*else*]
    [*assign var='trewritable' value="plugins_rewritable_no"|lang*]
    [*assign var='ttype' value="error"*]
[*/if*]
[*message lang_var='plugins_rewritable_templates'|pf:$trewritable type=$ttype die='0' title=0*]<br>
[*if !$res*]
    [*message lang_var='plugins_no_one' type='info'*]
[*else*]
    [*include file='admin/tablesorter.tpl'*]
    <div class='cornerText gray_color gray_border'>
        <fieldset><legend>[*'plugins_title'|lang*]</legend>
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th>[*'plugins_area_name'|lang*]</th>
                        <th>[*'plugins_area_author'|lang*]</th>
                        <th>[*'plugins_area_version'|lang*]</th>
                        <th>[*'plugins_area_compatibility'|lang*]</th>
                        <th class="js_nosort">[*'actions'|lang*]</th>
                    </tr>
                </thead>
                <tbody>
                    [*foreach from=$res item='file'*]
                        <tr id='plugin_[*$file*]'>
                            <td>[*$file|pvar:'name'*]([*$file*])<br>
                                <font size='1'>[*$file|pvar:'descr'*]</font>
                            </td>
                            <td>[*$file|pvar:'author'*]</td>
                            <td>[*$file|pvar:'version'*]</td>
                            <td>
                                [*if $file|pcompatibility==2*]
                                    [*'plugins_comp_best'|lang*]
                                [*elseif $file|pcompatibility==1*]
                                    [*'yes'|lang*]
                                [*else*]
                                    [*'no'|lang*]
                                [*/if*]</td>
                            <td>
                                [*if $file|psettings*]
                                    <a href="[*$admin_file|uamp*]&amp;act=settings&amp;id=[*$file*]">
                                        <img src="[*$theme_path*]engine_images/settings.png"
                                             alt="[*'plugins_parameters'|lang*]" 
                                             title="[*'plugins_parameters'|lang*]">
                                    </a>
                                [*/if*]
                                <a href="javascript:plugin_act('reinstall','[*$file|sl*]');">
                                    <img src="[*$theme_path*]engine_images/update.png"
                                         alt="[*'plugins_plugin_reinstall'|lang*]" 
                                         title="[*'plugins_plugin_reinstall'|lang*]">
                                </a>
                                <a href="javascript:element_delete('[*$file|sl*]', 'plugin_');">
                                    <img src="[*$theme_path*]engine_images/delete.png"
                                         alt="[*'delete'|lang*]" title="[*'delete'|lang*]">
                                </a>
                            </td>
                        </tr>
                    [*/foreach*]
                </tbody>
            </table>
        </fieldset>
    </div>
[*/if*]
<div align="right" class='padding_right'>
    [*assign var='pluginselector' value=$res|@plugin_selector*]
    [*if $pluginselector*]
        [*$pluginselector*]
        <a href="javascript:plugin_act('add', jQuery('select[name=plugin_files]').val());">
            <img src="[*$theme_path*]engine_images/add_small.png" title="[*'add'|lang*]" alt="[*'add'|lang*]">
        </a>
        <div class='status_icon' id='plugins_status_icon'></div>
    [*/if*]
</div>
<script type='text/javascript'>
    function plugin_act(act, file) {
        jQuery.post('[*$admin_file|sl*]&from_ajax=1&act='+act, {'id':file}, function (data) {
            if (data=='OK!') {
                if (act == 'add')
                    window.location = '[*$admin_file|sl*]';
                else
                    alert(success_text);
            } else
                alert(error_text+data);
        });
    }
    [*if $pluginselector*]
        jQuery('select[name=plugin_files]').change(function () {      
            var si = 'plugins_status_icon';
            status_icon(si, 'loading_white');
            jQuery.post('[*$admin_file|sl*]&from_ajax=1&act=check', {'id':jQuery(this).val()}, function (data) {
                var st = '';
                if (data=='2')
                    st = '[*'plugins_comp_best_simple'|lang|sl*]';
                else if (data=='1')
                    st = '[*'yes_simple'|lang|sl*]';
                else                
                    st = '[*'no_simple'|lang|sl*]';
                status_icon(si, data=='2' || data=='1'?'success':'error', '[*'plugins_area_compatibility'|lang|sl*]: '+st);
            });
        });
    [*/if*]
</script>