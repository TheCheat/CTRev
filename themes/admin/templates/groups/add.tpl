[*assign var="n" value=$row.name*]
<script type="text/javascript" src="js/jquery.colorpicker.js"></script>
<script type='text/javascript'>
    jQuery(document).ready(function () {
                var v = jQuery('#group_color').val();
                if (v)
                    v = v.substr(1);
        jQuery('#group_colorpicker').ColorPicker({
            'flat': true,
            'color':v,
            'onSubmit': function (cobj, color) {
                jQuery('#group_color').val('#'+color);
            }
        });
    });
</script>
<form action='[*$admin_file|uamp*]&amp;act=save' method='post'>
    <input type='hidden' name='id' value='[*$id*]'>
    <div class='cornerText gray_color2'>
        <fieldset>
            [*if $id*]
                [*assign var='a' value="edit"*]
            [*else*]
                [*assign var='a' value="add"*]
            [*/if*]
            <legend>[*"groups_group_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'groups_area_name'|lang*]</dt>
                <dd><input type='text' name='name' value='[*$n*]' size='35'>
                    [*if $id && $n|lang:1*]
                        &nbsp;([*$n|lang*])
                    [*/if*]
                </dd>
                <dt>[*'groups_area_color'|lang*]</dt>
                <dd><input type='text' name='color' id='group_color' value='[*$row.color*]' size='35'>                    
                    <div class="group_colorpicker">
                        <a href="javascript:void(0);" onclick="toggle_menu(this, true);">
                            <img src="[*$atheme_path*]engine_images/view.png" 
                                 title="[*'groups_choose_color'|lang*]"
                                 alt="[*'groups_choose_color'|lang*]">
                        </a>
                        <div class="menu colorpicker" id="group_colorpicker"></div>
                    </div>
                    [*if $id*]
                        &nbsp;(<font color='[*$row.color*]'>[*$row.color*]</font>)
                    [*/if*]
                </dd>
                <dt>[*'groups_area_allowed_modules'|lang*]</dt>
                <dd>[*simple_selector name='acp_modules' values=$allowed_modules current=$row.acp_modules size=5 null=true*]</dd>
                <dt>[*'groups_area_pm_count'|lang*]</dt>
                <dd><input type='text' name='pm_count' value='[*$row.pm_count*]' size='10'></dd>
                <dt>[*'groups_area_admin'|lang*]</dt>
                <dd>
                    <input type='radio' name='system' value='1'[*if $row.system*] checked='checked'[*/if*]>&nbsp;[*'yes'|lang*]
                    <input type='radio' name='system' value='0'[*if !$row.system*] checked='checked'[*/if*]>&nbsp;[*'no'|lang*]
                </dd>
                <dt>[*'groups_area_default'|lang*]</dt>
                <dd>
                    <input type='radio' name='default' value='1'[*if $row.default*] checked='checked'[*/if*]>&nbsp;[*'yes'|lang*]
                    <input type='radio' name='default' value='0'[*if !$row.default*] checked='checked'[*/if*]>&nbsp;[*'no'|lang*]
                </dd>
                <dt>[*'groups_area_bot'|lang*]</dt>
                <dd>
                    <input type='radio' name='bot' value='1'[*if $row.bot*] checked='checked'[*/if*]>&nbsp;[*'yes'|lang*]
                    <input type='radio' name='bot' value='0'[*if !$row.bot*] checked='checked'[*/if*]>&nbsp;[*'no'|lang*]
                </dd>
                <dt>[*'groups_area_guest'|lang*]</dt>
                <dd>
                    <input type='radio' name='guest' value='1'[*if $row.guest*] checked='checked'[*/if*]>&nbsp;[*'yes'|lang*]
                    <input type='radio' name='guest' value='0'[*if !$row.guest*] checked='checked'[*/if*]>&nbsp;[*'no'|lang*]
                </dd>
                <dt>[*'groups_area_content_count'|lang*]</dt>
                <dd><input type='text' name='content_count' value='[*$row.content_count*]' size='10'></dd>
                <dt>[*'groups_area_karma_count'|lang*]</dt>
                <dd><input type='text' name='karma_count' value='[*$row.karma_count*]' size='10'></dd>
                <dt>[*'groups_area_bonus_count'|lang*]</dt>
                <dd><input type='text' name='bonus_count' value='[*$row.bonus_count*]' size='10'></dd>
            </dl>
            [*include file='admin/groups/perms.tpl'*]
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>