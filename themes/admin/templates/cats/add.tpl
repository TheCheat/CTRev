<script type="text/javascript">
    function make_catname(obj, val) {
        if (!jQuery(obj).val())
            jQuery(obj).val(translite(jQuery(val).val()));
    }
</script>
<form action='[*$admin_file|uamp*]&amp;act=save&amp;type=[*$cat_type*]' method='post'>
    <input type='hidden' name='id' value='[*$id*]'>
    <div class='cornerText gray_color2'>
        <fieldset>
            [*if $id*]
                [*assign var='a' value="edit"*]
            [*else*]
                [*assign var='a' value="add"*]
            [*/if*]
            <legend>[*"cats_cat_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'cats_area_name'|lang*]</dt>
                <dd><input type='text' name='name' value='[*$row.name*]' id='category_name'></dd>
                <dt>[*'cats_area_transl_name'|lang*]</dt>
                <dd><input type='text' name='transl_name' onfocus='make_catname(this, "#category_name")'
                           value='[*$row.transl_name*]'></dd>
                <dt>[*'cats_area_parent'|lang*]</dt>
                <dd>[*select_categories name='parent_id' null=true type=$cat_type size='1' current=$row.parent_id*]</dd>
                [*if $pattern_selector*]
                    <dt>[*'cats_area_pattern'|lang*]</dt>
                    <dd>[*$pattern_selector*]</dd>
                [*/if*]
                <dt>[*'cats_area_descr'|lang*]</dt>
                <dd>[*input_form name='descr' text=$row.descr*]</dd>
                <dt>[*'cats_area_post_allow'|lang*]</dt>
                <dd><input type='checkbox' value='1' name='post_allow'
                           [*if $row.post_allow || !$row.id*] 
                               checked='checked'
                           [*/if*]></dd>
            </dl>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>