[*if !$no_js_comm*]
    <br>
    <script type="text/javascript">
        function add_comment() {
            make_tobbcode();
            var $comment = jQuery("form[name='[*$name|sl*]_form']").serialize();
            var $resid = '[*$resid*]';
            var $type = '[*$type|sl*]';
            $comment += "&resid="+$resid;
            $comment += "&type="+$type;
            var si = "comments_status_icon";
            status_icon(si, 'loading_white');
            prehide_ls();
            jQuery.post("[*$BASEURL|sl*]index.php?module=comments_manage&act=add&from_ajax=1", $comment, function (data) {
                if (data=="OK!") {
                    reload_comment($resid, $type);
                    status_icon(si, 'success');
                } else {
                    alert("[*'error'|lang|sl*]: "+data);
                    status_icon(si, 'error');
                }
            });
        }
        function quote_comment(author, id) {
            var arr = getTa('body');
            var o = arr[0];
            var wysiwyg = arr[1];
            var txt = trim(getSelectionText(window));
            if (    !txt) {
                jQuery.post('[*$BASEURL|sl*]index.php?module=comments_manage&act=quote&from_ajax=1', {'id':id}, function (data) {
                    add_tota(arr,author,data);
                });
            } else
                add_tota(arr,author,txt);
        }
    </script>
    <form name="[*$name*]_form" action="javascript:void(0);">
        [*fk ajax=0*]
        <div class="bbcode_comments">
            <div class="white_color cornerText gray_border">
                <fieldset><legend>[*'comment_add'|lang*]</legend>
                    <dl class="info_text">
                        <dt class="short">[*'comment_title'|lang*]:</dt>
                        <dd><input type="text" name="title" size="45"
                                   value="[*if $trow*][*'comment_re'|lang*][*$trow.subject*][*/if*]"></dd>
                    </dl>
                    <br>
                    <center>[*input_form name="body"*]</center>
                    [*if !$curuser*]
                        <dl class="info_text">
                            <dt class="short">[*'area_captcha'|lang*]</dt>
                            <dd>[*include file="captcha.tpl"*]</dd>
                        </dl>
                    [*/if*]
                    <center>
                        <div>
                            <div class="si_downer">
                                <div class="status_icon" id="comments_status_icon"></div>
                            </div>
                            <input type="button" value="[*'add'|lang*]" onclick="add_comment();"
                                   class="clickable"></div>
                    </center>
                </fieldset>
            </div>
        </div>
    </form>
[*else*]
    <div>
        <dl class="info_text">
            <dt>[*'comment_title'|lang*]:</dt>
            <dd><input type="text" name="[*$name*]_subj" size="45"
                       value="[*$subject*]"></dd>
            <dt>&nbsp;</dt>
            <dd class='nobordered'>[*input_form name=$name text=$text*]</dd>
        </dl>
        <center>
            <div class="float_left si_downer">
                <div class="status_icon" id="comments_status_icon_[*$id*]"></div>
            </div>
            <input type="button" value="[*'save'|lang*]"
                   onclick="edit_comment_save('comment_[*$id*]', '[*$id*]', '[*$name|sl*]');"
                   class="clickable">&nbsp;&nbsp;
            <input type="button" value="[*'cancel'|lang*]"
                   onclick="cancel_edit_comment('[*$id*]');"
                   class="clickable"></center>
    </div>
[*/if*]
