[*include file='admin/sortable.tpl'*]
<script type='text/javascript'>
    function change_ptype(obj) {
        var cur = jQuery(obj).parents('div.pattern_element');
        var vals = jQuery('.pattern_values', cur);
        var html = jQuery('.pattern_html', cur);
        var descr = jQuery('.pattern_descr', cur);
        var size = jQuery('.patterns_size', cur);
        vals.hide();
        html.hide();
        size.hide();
        descr.show();
        switch (jQuery(obj).val()) {
            case "input":
                size.show();
                break;
            case "select":
            case "radio":
                vals.show();
                break;
            case "html":
                html.show();
                descr.hide();
                break;
        }
    }
    function add_pelement() {
        var cur = jQuery('.sortable');
        var demo = jQuery('li:first', cur).clone();
        jQuery('input', demo).val('');
        var ta = null;
        jQuery('textarea', demo).removeClass('processed').val('').each(function () {
            ta = jQuery(this);     
            ta.parents('dd').empty().append(ta);
        });
        jQuery('select', demo).val('');
        cur.append(demo);
        showhide_pdelete();
        textarea_resizer();
    }
    function delete_pelement(obj) {
        if (!showhide_pdelete())
            return;
        jQuery(obj).parents('li').fadeOut(500, function () {
            jQuery(this).remove();
            showhide_pdelete();
        });
    }
    function showhide_pdelete() {
        var e = jQuery('div.pattern_delete');
        if (jQuery('div.pattern_element').length < 2) {
            e.hide();
            return false;
        } else
            e.show();
        return true;
    }
    jQuery(document).ready(function () {        
        showhide_pdelete();
        jQuery('div.pattern_element select').each(function () {
            change_ptype(this);
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
            <legend>[*"patterns_pattern_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'patterns_area_pattern_name'|lang*]</dt>
                <dd><input type='text' name='pattern_name' value='[*$rows.name*]' id='category_name'></dd>
            </dl>
            <ul class='sortable'>
                [*foreach from=$rows.pattern item='row'*]
                    <li class='unstyled'>
                        [*include file='admin/patterns/element.tpl'*]
                    </li>
                [*/foreach*]
            </ul>
            <p class='pattern_support'><font size='1'>[*'patterns_areas_support'|lang*]</font></p>
            <div align='center'><input type='button' onclick='add_pelement();' value='[*'add'|lang*]'>
                <input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>