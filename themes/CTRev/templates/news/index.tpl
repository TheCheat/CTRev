[*assign var='owntitle' value=1*]
<legend>
    [*'news_block'|lang*]
    [*if 'news'|perm*]
        <a href="[*gen_link module='news' act='add'*]"><img
                src="[*$theme_path*]engine_images/add_small.png" alt="[*'add'|lang*]"
                title="[*'add'|lang*]">
        </a>
    [*/if*]
</legend>
<script src="[*$theme_path*]js/jquery.jtip.js" type="text/javascript"></script>
<script type='text/javascript'>
    function delete_news($id) {
        if (!confirm('[*'news_are_you_sure_to_delete'|lang*]'))
        return;
        var fk = {[*fk ajax=2*]};
        jQuery.post('[*$BASEURL*]index.php?module=news&act=delete&from_ajax=1&id='+$id, fk, function() {
            jQuery('#news_id'+$id).fadeOut(300, function () {
                jQuery(this).remove()
            });
        });
    }
</script>
[*if !$rows*]
    [*message lang_var='news_no_news' type='info'*]
[*/if*]
[*foreach from=$rows key='num' item="row"*]
    <div class='hidden' id='nbody_id[*$row.id*]_body'>
        [*$row.content|ft*]<br>
        [*'news_by'|lang*][*$row.username|gcl:$row.group*]
    </div>
    <div class='news_element' id='news_id[*$row.id*]'>
        <span>[*date format='d-M-Y' time=$row.posted_time*]</span><a
            name='[*$row.title*]' id='nbody_id[*$row.id*]'
            href='javascript:void(0);'
            class="jTip news_[*if !$num*]bold[*else*]simple[*/if*]">[*$row.title*]</a>    
        <div class='news_edit_row'>
            [*if check_owner($row.poster_id, 'edit_news')*]
                <a href="[*gen_link module='news' act='edit' id=$row.id*]"><img
                        src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"></a>

            [*/if*]
            [*if check_owner($row.poster_id, 'del_news')*]
                <a href="javascript:delete_news('[*$row.id*]')"><img
                        src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>

            [*/if*]
        </div>
    </div>
[*/foreach*]