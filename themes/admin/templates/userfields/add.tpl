<script type='text/javascript'>
    allowed_types = [*$types_array*];
    selector = 'select[name="type"]';
    jQuery(document).ready(function() {
        jQuery(selector + ' option').each(function() {
            var o = jQuery(this);
            var t = trim(o.text());
            var r = t.match(/^'([^']+)'([\s\S]+?)$/);
            if (r) {
                o.attr('title', r[1]);
                o.text(r[2]);
            }
        });
        jQuery(selector).change(field_type_change);
        field_type_change();
    });
    function field_type_change() {
        var obj = jQuery(selector);
        var type = obj.val();
        var txt = jQuery('option:selected', obj).attr("title");
        jQuery('#field_type_descr').text(txt ? txt : "");
        var av = jQuery('#allowed_dont_need,input[name="allowed"],#allowed_values');
        av.hide();
        var t = parseInt(allowed_types[type]);
        jQuery(av.eq(t)).show();

    }
    function add_allowed() {
        var av = jQuery('.allowed_value:first').clone();
        jQuery('input', av).val("");
        jQuery('a', av).removeClass('hidden');
        av.appendTo('#allowed_values');
        jQuery('#add_allowed').appendTo('#allowed_values');
    }
    function delete_allowed(obj) {
        jQuery(obj).parents('.allowed_value').fadeOut(300, function() {
            jQuery(this).remove();
        });
    }
</script>
<form method="post" action='[*$admin_file|uamp*]&amp;act=save'>
    <input type='hidden' name='old_field' value='[*$row.field*]'>
    <div class="cornerText gray_color2">
        <fieldset><legend>[*'userfields_title'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'userfields_field'|lang*]</dt>
                <dd><input type='text' name='field' size='20' value='[*$row.field*]'></dd>
                <dt>[*'userfields_name'|lang*]</dt>
                <dd><input type='text' name='name' size='40' value='[*$row.name*]'><br>
                    <font size='1'>[*'userfields_name_necessary'|lang*]</font></dd>
                <dt>[*'userfields_descr'|lang*]</dt>
                <dd>
                    <textarea rows='5' cols='40' name='descr'>[*$row.descr|he:false:true*]</textarea>
                </dd>
                <dt>[*'userfields_type'|lang*]</dt>
                <dd>
                    [*simple_selector name='type' current=$row.type values=$types keyed='userfields_type_'*]<br>
                    <font size='1' id='field_type_descr'></font>
                </dd>
                <dt>[*'userfields_allowed'|lang*]</dt>
                <dd>
                    <div id='allowed_dont_need'>
                        <b>[*'userfields_allowed_dont_need'|lang*]</b>
                    </div>
                    <input type='text' size='40' name='allowed'
                           [*if !$values*]
                               value='[*$row.allowed*]'
                           [*/if*] class='hidden'>
                    <div id='allowed_values' class='hidden'>
                        [*foreach from=$values item="v" key="k"*]
                            [*if $v===''*]
                                [*assign var='k' value=''*]
                            [*/if*]
                            <div class='allowed_value'>
                                <div class="nobr">
                                    <input type='text' size='10' name='keys[]' value='[*$k*]'>
                                    =>
                                    <input type='text' size='25' name='values[]' value='[*$v*]'>
                                    <a href='javascript:void(0);' onclick='delete_allowed(this);' class='hidden'>
                                        <img src='[*$atheme_path*]engine_images/delete.png' alt='[*"delete"|lang*]'>
                                    </a>
                                </div>
                                <div class='br'></div>
                            </div>
                        [*/foreach*]
                        <div class='br'></div>
                        <a href='javascript:void(0);' id='add_allowed' onclick='add_allowed();'>
                            <img src='[*$atheme_path*]engine_images/add_small.png' alt='[*"add"|lang*]'>&nbsp;[*"add"|lang*]
                        </a>
                    </div>
                </dd>
                <dt>[*'userfields_options'|lang*]</dt>
                <dd><input type='checkbox' name='show_register' value='1'
                           [*if $row.show_register*] 
                               checked='checked'
                           [*/if*]>&nbsp;[*'userfields_show_register'|lang*]<br>
                    <input type='checkbox' name='show_profile' value='1'
                           [*if $row.show_profile*] 
                               checked='checked'
                           [*/if*]>&nbsp;[*'userfields_show_profile'|lang*]
                </dd>
            </dl>
            <center>
                [*if $row.field*]
                    <input type="submit" value="[*'save'|lang*]">
                [*else*]
                    <input type="submit" value="[*'add'|lang*]">
                [*/if*]
            </center>
        </fieldset>
    </div>
</form>