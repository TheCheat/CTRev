[*include file='admin/sortable.tpl'*]
<script type='text/javascript'>
    $langsorted = {};
    function edit_langkey(obj) {
        obj = jQuery(obj);
        var v = obj.text();
        obj.replaceWith('<'+'input type="text" name="keys['+v+']" value="'+v+'" size="25">');
    }
    function edit_langcatkey(obj) {
        obj = jQuery(obj);
        var v = obj.text();
        jQuery('input.lang_catfield_val[type=hidden]', obj.parents('li')).remove();
        obj.replaceWith('<'+'input type="text" name="values[]" value="'+v+'" size="25">');
    }
    function remove_langfield(obj) {
        jQuery(obj).parents('li').fadeOut(700, function () {
            jQuery(this).remove();
        });
    }
    function change_langfield(obj) {
        obj = jQuery(obj).parents('dd');
        var w = jQuery('div.langfield_input', obj);
        var i = jQuery('input', w);
        var big = false;
        if (!i.length) {
            i = jQuery('textarea', w);
            big = true;
        }
        var name = i.attr('name');
        var val = i.val();
        if (!big) {
            val = val.replace(new RegExp('\\\\n', "g"), "\n");
            w.empty().append('<'+'textarea name="'+name+'" cols="54" rows="7">'+val+'<'+'/textarea>');
            textarea_resizer();
        } else
            w.empty().append('<'+'input type="text" name="'+name+'" value="'+val+'" size="55">');
    }
    function add_langfield() {
        var obj = jQuery('ul.sortable');
        var li = jQuery('li.sortable_thin:first', obj).clone().show();
        jQuery('dt', li).empty().append('<'+'input type="text" name="keys[]" size="25">');
        jQuery('div.langfield_input', li).empty().append('<'+'input type="text" name="values[]" size="55">');
        obj.append(li);
    }
    function add_langfield_cat() {
        var obj = jQuery('ul.sortable');
        var li = jQuery('li.sortable_disabled:first', obj).clone();
        jQuery('input.lang_catfield_val[type=hidden]', li).remove();
        jQuery('a.lang_catfield', li).replaceWith('<'+'input type="text" name="values[]" size="25">');
        jQuery('a.remove_lang_catfield', li).show();
        obj.append(li);
    }
    function sort_langfields(obj) {
        // Bydlocode begins
        obj = jQuery(obj).parents('li');
        var c = null;
        var a = {};
        var b = [];
        var v = null;
        var val = '';
        var i = 0;
        var id = jQuery('input', obj).val();
        c = obj.next();
        while (c.length && c.is('li') && !c.is('li.lang_category')) {
            v = jQuery('dt input', c);
            if (v.length)
                val = v.val();
            else
                val = jQuery('dt', c).text();
            b[i++] = val;
            a[val] = c.clone();
            c = c.next();
        }
        b.sort();  
        if (!$langsorted[id] || $langsorted[id]=='desc') 
            $langsorted[id] = 'asc';
        else {
            b.reverse();
            $langsorted[id] = 'desc';
        }
        c = obj.next();
        for (var k = 0; k < i; k++) {
            c.html(a[b[k]].html());
            v = jQuery('textarea', c).removeClass('processed');
            if (v.length)
                v.parent().replaceWith(v);
            c = c.next();
        }   
        textarea_resizer();
        // Bydlocode ends.
    }
    [*if !$file*]
        jQuery(document).ready(function () {
            add_langfield();
            remove_langfield('li.sortable_thin:first a');
        });
    [*/if*]
</script>
<form action='[*$admin_file|uamp*]&amp;act=save' method="post">
    <input type='hidden' name='id' value='[*$id*]'>
    <input type='hidden' name='file' value='[*$file*]'>
    <div class='cornerText gray_border gray_color'>
        <fieldset>
            <legend>
                [*if $file*]
                    [*'languages_edit_title'|pf:$file:$id*]&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;
                    <a href='[*$admin_file*]&amp;act=files&amp;id=[*$id*]&amp;folder=[*$parent|ue*]'>
                        [*'back'|lang*]
                    </a>
                [*else*]
                    [*'languages_add_title'|pf:$id*]&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;
                    <a href='[*$admin_file*]&amp;act=files&amp;id=[*$id*]&amp;folder=[*$filename|ue*]'>
                        [*'back'|lang*]
                    </a>
                [*/if*]
            </legend>
            <dl class='info_text'>
                <dt>[*'languages_filename'|lang*]</dt>
                <dd><input type='text' name='filename' value='[*$filename*]' size='55'></dd>
                <dt>[*'languages_is_writable'|lang*]</dt>
                <dd>
                    [*if $is_writable*]
                        [*'yes'|lang*]
                    [*else*]
                        [*'no'|lang*]
                    [*/if*]
                </dd>
            </dl>
            <ul class='sortable'>
                [*assign var='was' value=0*]
                [*foreach from=$languages item='value' key='key'*]
                    [*assign var='ov' value=$value*]
                    [*assign var='value' value=$value|cut_langsplitter*]
                    [*if is_numeric($key) && $ov!=$value*]
                        <li class='sortable_disabled lang_category'>
                            <div class='padding_left'>
                                [*'languages_category'|lang*]
                                <a class='lang_catfield' href='javascript:void(0);' 
                                   onclick='edit_langcatkey(this);'><b>[*$value*]</b></a>                         
                                <a href="javascript:void(0);" onclick="remove_langfield(this);"
                                   class='remove_lang_catfield[*if !$was*] hidden[*/if*]'>
                                    <img src="[*$theme_path*]engine_images/delete.png" title="[*'delete'|lang*]"
                                         alt="[*'delete'|lang*]"></a>
                                &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                                <a href='javascript:void(0);' onclick='sort_langfields(this);'>[*'languages_sort'|lang*]</a>
                                <input type='hidden' class='lang_catfield_val' 
                                       name='values[]' value='[*$value*]'>
                                <input type='hidden' name='keys[]' value='0'>
                            </div>
                        </li>
                        [*assign var='was' value=1*]
                    [*else*]
                        <li class='sortable_thin[*if !$file*] hidden[*/if*]'>
                            <dl class='info_text'>
                                <dt><a href='javascript:void(0);' onclick='edit_langkey(this);'>[*$key*]</a></dt>
                                <dd><div class='clear_both'>
                                        <div class='langfield_input'>
                                            <input type='text' name='values[[*$key*]]'  value='[*$value|he:false:true|rnl*]' size='55'>
                                        </div>
                                        <div class='lang_buttons'>
                                            <a href='javascript:void(0);' onclick='change_langfield(this);'>
                                                <img src="[*$theme_path*]engine_images/arrow_inout.png" 
                                                     title="[*'languages_change_fieldtype'|lang*]"
                                                     alt="[*'languages_change_fieldtype'|lang*]"></a>                           
                                            <a href="javascript:void(0);" onclick="remove_langfield(this);">
                                                <img src="[*$theme_path*]engine_images/delete.png" title="[*'delete'|lang*]"
                                                     alt="[*'delete'|lang*]"></a>
                                        </div>
                                    </div>
                                </dd>
                            </dl>
                        </li>
                    [*/if*]
                [*/foreach*]
            </ul>
            <div align='center'>
                <font size='1'><b>[*'languages_newline_notice'|lang*]</b></font><br>
                <input type='submit' value='[*'save'|lang*]'>  
                <a href="javascript:add_langfield_cat();">
                    <img src="[*$theme_path*]engine_images/cat_add.png" align='right'
                         title="[*'languages_add_category'|lang*]" 
                         alt="[*'languages_add_category'|lang*]" class='spadding_left'></a>
                <a href="javascript:add_langfield();">
                    <img src="[*$theme_path*]engine_images/add.png" align='right'
                         title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
            </div>
        </fieldset>
    </div>
</form>