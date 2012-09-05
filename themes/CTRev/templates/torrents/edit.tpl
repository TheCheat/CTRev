<form action="javascript:void(0);" id="edit_form_[*$row.id*]" method="post">
    [*fk ajax=0*]
    <dl class="info_text">
        <dt>[*'torrents_title'|lang*]:</dt>
        <dd style="width: 450px;"><input type="text" value="[*$row.title*]" name='title' size='56'></dd>
        <dt>&nbsp;&nbsp;</dt>
        <dd style="width: 450px;">[*input_form text=$row.content name='content'*]</dd>
        <dt>[*'torrents_edit_reason'|lang*]:</dt>
        <dd style="width: 450px;"><input type="text" value="[*$row.edit_reason*]" name="edit_reason" size='56'></dd>
        <dt>[*'torrents_tags'|lang*]:</dt>
        <dd><input type="text" name="tags" size="50" value='[*$row.tags*]'></dd>
            [*if 'ct_price'|perm*]
            <dt>[*'torrents_price'|lang*]</dt>
            <dd><input type="text" name="price" size="25" value='[*$row.price*]'></dd>
            [*/if*]
            [*if 'msticky_torrents'|perm*]
            <dt>[*'torrents_sticky'|lang*]:</dt>
            <dd><input type="radio" name="sticky" value="1" 
                       [*if $row.sticky*]
                           checked="checked"
                       [*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                       name="sticky" value="0" 
                       [*if !$row.sticky*]
                           checked="checked"
                       [*/if*]>[*'no'|lang*]</dd>
            [*/if*]
    </dl>
    <div align="right">
        <input type="submit" onclick="save_torrents('[*$row.id*]', '#edit_form_[*$row.id*]', '[*$full*]');" value='[*'save'|lang*]'>&nbsp;
        <input type="button" class="clickable" value='[*'cancel'|lang*]'
               onclick='cancel_edit_torrents("[*$row.id*]")'>
    </div>
</form>