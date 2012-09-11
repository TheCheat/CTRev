<div class="cornerText gray_color gray_border">
    <fieldset><legend>[*'torrents_adding'|lang*]</legend>
        <script type="text/javascript" src="[*$theme_path*]js/jquery.dimensions.js"></script>
        <script type="text/javascript" src="[*$theme_path*]js/jquery.accordion.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#accordion_descr').accordion( {
                    "autoheight" : false
                });
            });
            var cats_was_selected = [*if $id*]true[*else*]false[*/if*];
            function check_torrentsform(form) {
                make_tobbcode();
                try {
                    if (!jQuery('input[name=title]', form).val()) 
                        throw '[*'torrents_no_title'|lang|sl*]';
                    if (!cats_was_selected)
                        throw '[*'torrents_no_selected_cat'|lang|sl*]';
                    if (!jQuery('textarea[name=content]', form).val())
                        throw '[*'torrents_no_content'|lang|sl*]';
                } catch (e) {
                    alert(e);
                    return false;
                }
                return true;
            }
        </script>
        <!-- Для шаблона имя формы 'adding_form' -->
        <form method="post" enctype="multipart/form-data" name='adding_form' onsubmit='return check_torrentsform(this);'
              action="[*if !$id*][*gen_link module='torrents' act='add'*][*else*][*gen_link module='torrents' act='edit' id=$id*][*/if*]">
            [*fk ajax=0*]
            <input type="hidden" name="confirm" value="1">
            <dl class="info_text">
                <dt>[*'torrents_file'|lang*]</dt>
                <dd><input type="file" name="torrent" size="35"></dd>
                <dt>[*'torrents_title'|lang*]</dt>
                <dd><input type="text" name="title" value="[*$nrow.title*]" size="50"></dd>
                <dt>[*'torrents_category'|lang*]</dt>
                <dd>[*$categories_selector*]</dd>
            </dl>
            <center>
                <div class="accordion accordion_sw" id="accordion_descr">
                    <a class="accordion_header">[*'torrents_text'|lang*]</a>
                    <div>[*input_form name="content" text=$nrow.content*]</div>
                    <a class="accordion_header">[*'torrents_screenshots'|lang*]</a>
                    <div align='left'>[*include file='torrents/screenshots.tpl'*]</div>
                    [*if 'polls'|perm:2*]
                        <a class="accordion_header">[*'torrents_polls'|lang*]</a>
                        <div align="left">[*add_polls toid=$id*]</div>
                    [*/if*]
                </div>
            </center>
            <dl class="info_text">
                [*if 'ct_price'|perm*]
                    <dt>[*'torrents_price'|lang*]</dt>
                    <dd><input type="text" name="price" size="25" value='[*$nrow.price*]'></dd>

                [*/if*]
                [*if $id*]
                    <dt>[*'torrents_edit_reason'|lang*]</dt>
                    <dd><input type="text" name="edit_reason" size="50" value='[*$nrow.edit_reason*]'></dd>

                [*/if*]
                <dt>[*'torrents_tags'|lang*]</dt>
                <dd><input type="text" name="tags" size="50" value='[*$nrow.tags*]'></dd>

                [*if 'msticky_torrents'|perm*]
                    <dt>[*'torrents_sticky'|lang*]</dt>
                    <dd><input type="radio" name="sticky" value="1" [*if $nrow.sticky*]
                               checked="checked"[*/if*]>[*'yes'|lang*]&nbsp;<input type="radio"
                               name="sticky" value="0" [*if !$nrow.sticky*]
                               checked="checked"[*/if*]>[*'no'|lang*]</dd>

                [*/if*]
            </dl>
            <center><input type="submit" value="[*'torrents_submit'|lang*]"></center>
        </form>
    </fieldset>
</div>