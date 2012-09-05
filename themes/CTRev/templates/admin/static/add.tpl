<script type='text/javascript'>
    function open_static_content(obj) {
        var a = jQuery('div.static_content_input', jQuery(obj).parents('dd'));
        if (jQuery(obj).attr('checked')) {
            a.eq(0).show();
            a.eq(1).hide();
            jQuery('textarea', a.eq(0)).val(jQuery('textarea', a.eq(1)).val());
        } else {
            make_tobbcode();
            a.eq(1).show();
            a.eq(0).hide();
            jQuery('textarea', a.eq(1)).val(jQuery('textarea', a.eq(0)).val());
        }
    }
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
            <legend>[*"static_static_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'static_area_url'|lang*]</dt>
                <dd><input type='text' name='url' value='[*$row.url*]' size='25'></dd>
                <dt>[*'static_area_title'|lang*]</dt>
                <dd><input type='text' name='title' value='[*$row.title*]' size='61'></dd>
                <dt>[*'static_area_content'|lang*]</dt>
                <dd>
                    [*if $row.bbcode*]
                        [*assign var='content' value=$row.content*]
                    [*else*]
                        [*assign var='content' value=$row.content|he*]
                    [*/if*]
                    <div class='static_content_input[*if !$row.bbcode*] hidden[*/if*]'>
                        [*input_form name='content' text=$content*]
                    </div>
                    <div class='static_content_input[*if $row.bbcode*] hidden[*/if*]'>
                        <textarea name='html' cols='60' rows='10'>[*$content*]</textarea>
                    </div>
                    <br>
                    <input type='checkbox' name='bbcode'[*if $row.bbcode*] checked='checked'[*/if*] 
                           value='1' onclick='open_static_content(this);'>&nbsp;[*'static_area_bbcode'|lang*]
                </dd>
            </dl>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>