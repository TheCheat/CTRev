<script type='text/javascript'>
    function add_modvalue(obj) {
        obj = jQuery(obj).parents('dd');
        var c = jQuery('div.modsetting_item:first', obj).clone();
        jQuery('input[type="text"], select', c).val('');
        jQuery('input[type="checkbox"]', c).removeAttr('checked');
        jQuery('span.remove_modsetting', c).show();
        obj.children('div.parameter_addfield').append(c);
    }
    function remove_modvalue(obj) {
        jQuery(obj).parents('div.modsetting_item').fadeOut(500, function (data) {
            jQuery(this).remove();
        });
    }
</script>
<dl class='info_text'>
    [*if $settings_langprefix*]
        [*assign var='stext' value='_settings_'*]
    [*else*]
        [*assign var='stext' value='settings_'*]
    [*/if*]
    [*foreach from=$parsed_settings key='k' item='s'*]
        [*assign var='langvar' value="$settings_langprefix$stext$k"*]
        <dt>[*$langvar|lang*]</dt>
        <dd>[*assign var='was' value=0*]
            [*foreach from=$used_settings.$k item='val' key='key'*]
                <div class='modsetting_item'>     
                    [*parameters_compiler val=$val key=$key s=$s k=$k langvar=$langvar*]
                    [*if $unlim*]
                        <span class='[*if !$was*]hidden [*/if*]remove_modsetting'>
                            <a href="javascript:void(0);" onclick="remove_modvalue(this);">
                                <img src="[*$theme_path*]engine_images/delete.png" title="[*'delete'|lang*]"
                                     alt="[*'delete'|lang*]">
                            </a>
                        </span>
                    [*/if*]
                    <div class='br'></div>
                </div>
                [*assign var='was' value=1*]
            [*/foreach*]
            <div class='parameter_addfield'></div>
            [*if $unlim*]
                <div class="padding_left">
                    <a href="javascript:void(0);" onclick="add_modvalue(this);">
                        <img src="[*$theme_path*]engine_images/add_small.png" title="[*'add'|lang*]"
                             alt="[*'add'|lang*]">
                    </a>
                </div>
            [*/if*]
            <font size='1'>[*"`$langvar`_descr"|lang:true*]</font>
        </dd>
    [*/foreach*]
</dl>