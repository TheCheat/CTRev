<div class="cornerText gray_color gray_border">
    <fieldset><legend>[*'content_adding'|lang*]</legend>
        <script type="text/javascript" src="js/jquery.dimensions.js"></script>
        <script type="text/javascript" src="js/jquery.accordion.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                jQuery('#accordion_descr').accordion({
                    "autoheight": false
                });
            });
            var cats_was_selected = [*if $id*]true[*else*]false[*/if*];
            function check_contentform(form) {
                make_tobbcode();
                try {
                    if (!jQuery('input[name=title]', form).val())
                        throw '[*'content_no_title'|lang|sl*]';
                    if (!cats_was_selected)
                        throw '[*'content_no_selected_cat'|lang|sl*]';
                    if (!jQuery('textarea[name=content]', form).val())
                        throw '[*'content_no_content'|lang|sl*]';
                } catch (e) {
                    alert(e);
                    return false;
                }
                return true;
            }
        </script>
        <!-- Для шаблона имя формы 'adding_form' -->
        <form method="post" enctype="multipart/form-data" name='adding_form' onsubmit='return check_contentform(this);'
              action="[*if !$id*][*gen_link module='content' act='add'*][*else*][*gen_link module='content' act='edit' id=$id*][*/if*]">
            [*fk ajax=0*]

            <input type="hidden" name="confirm" value="1">
            <dl class="info_text">
                [*if "torrents_on"|config*]
                    <dt>[*'content_torrent_file'|lang*]</dt>
                    <dd><input type="file" name="torrent" size="35"></dd>

                [*/if*]
                <dt>[*'content_title'|lang*]</dt>
                <dd><input type="text" name="title" value="[*$nrow.title*]" size="50"></dd>
                <dt>[*'content_category'|lang*]</dt>
                <dd>[*$categories_selector*]</dd>
            </dl>
            <center>
                <div class="accordion accordion_sw" id="accordion_descr">
                    <a class="accordion_header">[*'content_text'|lang*]</a>
                    <div>[*input_form name="content" text=$nrow.content*]</div>
                    [*if "torrents_on"|config*]
                        <a class="accordion_header">[*'content_torrent_screenshots'|lang*]</a>
                        <div align='left'>[*include file='content/screenshots.tpl'*]</div>
                    [*/if*]
                    [*if 'attach'|perm && 'attach_manage'|mstate*]
                        <a class="accordion_header">[*'attachments'|lang*]</a>
                        <div>[*add_attachments type='content' toid=$id*]</div>
                    [*/if*]
                    [*if 'polls'|perm:2 && 'polls_manage'|mstate*]
                        <a class="accordion_header">[*'content_polls'|lang*]</a>
                        <div align="left">[*add_polls type='content' toid=$id*]</div>
                    [*/if*]
                </div>
            </center>
            <dl class="info_text">
                [*if "torrents_on"|config && 'ct_price'|perm*]
                    <dt>[*'content_torrent_price'|lang*]</dt>
                    <dd><input type="text" name="price" size="25" value='[*$nrow.price*]'></dd>

                [*/if*]
                [*if $id*]
                    <dt>[*'content_edit_reason'|lang*]</dt>
                    <dd><input type="text" name="edit_reason" size="50" value='[*$nrow.edit_reason*]'></dd>

                [*/if*]
                <dt>[*'content_tags'|lang*]</dt>
                <dd><input type="text" name="tags" size="50" value='[*$nrow.tags*]'></dd>

                [*if 'msticky_content'|perm*]
                    <dt>[*'content_sticky'|lang*]</dt>
                    <dd><input type="radio" name="sticky" value="1" [*if $nrow.sticky*]
                               checked="checked"[*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                               name="sticky" value="0" [*if !$nrow.sticky*]
                               checked="checked"[*/if*]>[*'no'|lang*]</dd>

                [*/if*]

                [*if !"torrents_on"|config && "edit_content"|perm:2*]
                    <dt>[*'content_on_top'|lang*]</dt>
                    <dd><input type="radio" name="on_top" value="1" [*if !$nrow || $nrow.on_top*]
                               checked="checked"[*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                               name="on_top" value="0" [*if $nrow && !$nrow.on_top*]
                               checked="checked"[*/if*]>[*'no'|lang*]
                    </dd>
                [*/if*]
            </dl>
            <center><input type="submit" value="[*'content_submit'|lang*]"></center>
        </form>
    </fieldset>
</div>