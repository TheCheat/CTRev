<script type='text/javascript'>
    function open_static_content(type) {
        var a = jQuery('div.static_content_input');
        type = parseInt(type);
        a.hide();
        a.eq(type).show();
        if (type == 1)
            jQuery('textarea', a.eq(1)).val(jQuery('textarea', a.eq(2)).val());
        else if (type == 2) {
            make_tobbcode();
            jQuery('textarea', a.eq(2)).val(jQuery('textarea', a.eq(1)).val());
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
                    [*assign var='content' value=''*]
                    [*if $row.type=='bbcode'*]
                        [*assign var='content' value=$row.content*]
                    [*elseif $row.type=='html'*]
                        [*assign var='content' value=$row.content|he:false:true*]
                    [*/if*]
                    <div class='static_content_input[*if $row.type!='tpl'*] hidden[*/if*]'>
                        <input type='text' name='tpl' value='[*if $row.type=='tpl'*][*$row.content*][*/if*]' size='61'><br>
                        <font size='1'>[*'static_area_tpl_info'|lang*]</font>
                    </div>
                    <div class='static_content_input[*if $row.type!='bbcode'*] hidden[*/if*]'>
                        [*input_form name='content' text=$content*]
                    </div>
                    <div class='static_content_input[*if $row.type!='html' && $row.type*] hidden[*/if*]'>
                        <textarea name='html' cols='60' rows='10'>[*$content*]</textarea>
                    </div>
                </dd>
                <dt>[*'static_area_type'|lang*]</dt>
                <dd><input type='radio' name='type' value='tpl' [*if $row.type=='tpl'*] checked='checked'[*/if*] 
                           onclick='open_static_content(0);'>&nbsp;[*'static_area_tpl'|lang*]<br>
                    <input type='radio' name='type' value='bbcode' [*if $row.type=='bbcode'*] checked='checked'[*/if*] 
                           onclick='open_static_content(1);'>&nbsp;[*'static_area_bbcode'|lang*]<br>
                    <input type='radio' name='type' value='html' [*if $row.type=='html' || !$row.type*] checked='checked'[*/if*] 
                           onclick='open_static_content(2);'>&nbsp;[*'static_area_html'|lang*]</dd>
            </dl>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>