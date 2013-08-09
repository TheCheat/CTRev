<form action="javascript:void(0);" id="edit_form_[*$row.id*]" method="post">
    [*fk ajax=0*]
    <dl class="info_text">
        <dt>[*'content_title'|lang*]:</dt>
        <dd style="width: 450px;"><input type="text" value="[*$row.title*]" name='title' size='56'></dd>
        <dt>&nbsp;&nbsp;</dt>
        <dd style="width: 450px;">[*input_form text=$row.content name='content'*]</dd>
        <dt>[*'content_edit_reason'|lang*]:</dt>
        <dd style="width: 450px;"><input type="text" value="[*$row.edit_reason*]" name="edit_reason" size='56'></dd>
        <dt>[*'content_tags'|lang*]:</dt>
        <dd><input type="text" name="tags" size="50" value='[*$row.tags*]'></dd>
            [*if "torrents_on"|config && 'ct_price'|perm*]
            <dt>[*'content_torrent_price'|lang*]</dt>
            <dd><input type="text" name="price" size="25" value='[*$row.price*]'></dd>
            [*/if*]
            [*if 'msticky_content'|perm*]
            <dt>[*'content_sticky'|lang*]:</dt>
            <dd><input type="radio" name="sticky" value="1" 
                       [*if $row.sticky*]
                           checked="checked"
                       [*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                       name="sticky" value="0" 
                       [*if !$row.sticky*]
                           checked="checked"
                       [*/if*]>[*'no'|lang*]</dd>
            [*/if*]
            [*if !"torrents_on"|config && "edit_content"|perm:2*]
            <dt>[*'content_on_top'|lang*]</dt>
            <dd><input type="radio" name="on_top" value="1" [*if $row.on_top*]
                       checked="checked"[*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                       name="on_top" value="0" [*if !$row.on_top*]
                       checked="checked"[*/if*]>[*'no'|lang*]
            </dd>
        [*/if*]
    </dl>
    <div align="right">
        <input type="submit" onclick="save_content('[*$row.id*]', '#edit_form_[*$row.id*]', '[*$full*]');" value='[*'save'|lang*]'>&nbsp;
        <input type="button" class="clickable" value='[*'cancel'|lang*]'
               onclick='cancel_edit_content("[*$row.id*]")'>
    </div>
</form>