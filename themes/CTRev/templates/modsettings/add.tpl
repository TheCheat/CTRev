<script type='text/javascript'>
    function modsettings_selectors() {
        jQuery('div.modsetting select:not(.msett_inited)').each(function () {
            var t = jQuery(this);
            modsettings_checkval(t);
            t.addClass('msett_inited');
            t.unbind('change').bind('change', function () {modsettings_checkval(this);});
        });
    }
    function modsettings_checkval(obj) {
        obj = jQuery(obj);
        var p = obj.parents('div.modsetting');
        var sobj = null;
        if (obj.is('[name^=key]')) {
            sobj = jQuery('div.setting_keylimit', p);
            if (obj.val()=='limited')
                sobj.show();
            else
                sobj.hide();
        } else {
            sobj = jQuery('div.setting_enumvals', p);
            if (obj.val()=='enum')
                sobj.show();
            else
                sobj.hide();
        }
    }
    function modsettings_add() {
        var obj = jQuery('div.modsetting:first').clone();
        jQuery('input, select', obj).val('');
        jQuery('a', obj).show();
        jQuery('select.msett_inited', obj).removeClass('msett_inited');
        jQuery('div.setting_keylimit, div.setting_enumvals', obj).hide();
        jQuery('#modsettings_settings').append(obj);
        modsettings_selectors();
    }
    function modsettings_remove(obj) {
        jQuery(obj).parents('div.modsetting').fadeOut(700, function (){jQuery(this).remove();});
    }
    function modsettings_defaults() {
        make_tobbcode();
        var form = jQuery('input,select,textarea', '#modsettings_create').serialize();
        jQuery.post(baseurl+'index.php?module=ajax_index&from_ajax=1&act=modsettings', form, function (data) {
            jQuery('#modsettings_defaults').empty().append(data);
            var wr = jQuery('#modsettings_wrapper');
            if (data) 
                wr.show();
            else
                wr.hide();
        });
    }
    jQuery(document).ready(function () {
        modsettings_selectors();
    });
</script>
<dl class='info_text' id='modsettings_create'>
    <dt>[*'modsettings_settings'|lang*]<dt>
    <dd>
        <div id='modsettings_settings'>
            <div class='modsetting'>
                <div class='br'></div>
                <input type='text' name='mparam[]' size='15'>&nbsp;[*$mkeytypes*]&nbsp;=>&nbsp;[*$mvaltypes*]
                <a href='javascript:void(0);' onclick="modsettings_remove(this);" class='hidden'>
                    <img src="[*$theme_path*]engine_images/delete.png"
                         alt="[*'delete'|lang*]" title="[*'delete'|lang*]">
                </a>
                <div class='setting_keylimit hidden'>
                    <div class='br'></div>
                    <b>[*'modsettings_keylimit'|lang*]</b> <input type='text' name='keylimit[]' size='10'>
                </div>
                <div class='setting_enumvals hidden'>
                    <div class='br'></div>
                    <b>[*'modsettings_enumvals'|lang*]</b> <input type='text' name='enumvals[]' size='35'>
                </div>
                <div class='br'></div>
                <hr class='gray_border'>
            </div>
        </div>
        <div class='br'></div>
        <a href="javascript:modsettings_add();">
            <img src="[*$theme_path*]engine_images/add_small.png" align='right'
                 title="[*'add'|lang*]" alt="[*'add'|lang*]">
        </a>
    </dd>
    <dt>[*'modsettings_defaults'|lang*]<dt>
    <dd>
        <a href='javascript:modsettings_defaults();'>[*'modsettings_defaults_update'|lang*]</a>
    </dd>
    <fieldset id="modsettings_wrapper" class='hidden'>
        <legend>[*'modsettings_defaults'|lang*]</legend>
        <div id='modsettings_defaults'>
        </div>
    </fieldset>
</dl>