[*assign var='nestedsortable' value=true*]
[*include file='admin/sortable.tpl'*]
<div class='cornerText gray_color2'>
    <fieldset><legend>[*'cats_title'|lang*]</legend>
        [*$cat_tselector*]
        [*if !$cat_tree*]
            [*message type='info' lang_var='cats_none'*]
        [*else*]
            <ul class='sortable_header'>
                <li>
                    <table width='100%'>
                        <tr>
                            <td width='16'></td>
                            <td width='25'>[*'cats_cat_id'|lang*]</td>
                            <td>[*'cats_cat_name'|lang*]</td>
                            <td width='130'>[*'cats_cat_posting_allowed'|lang*]</td>
                            <td width='50'>[*'cats_cat_pattern'|lang*]</td>
                            <td width='75'>[*'actions'|lang*]</td>
                        </tr>
                    </table>
                </li>
            </ul>
            [*$cat_tree*]
        [*/if*]
        <div align='center'><input type='button' class='styled_button_big' 
                                   onclick='save_order("#cats_order");' 
                                   value='[*'save_order'|lang*]'>            
            <a href="[*$admin_file|uamp*]&amp;act=add">
                <img src="[*$theme_path*]engine_images/add.png" align='right'
                     title="[*'add'|lang*]" alt="[*'add'|lang*]"></a>
        </div>
    </fieldset>
</div>