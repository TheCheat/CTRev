<script type="text/javascript">
    //[*if !$from_ajax*]

    $comments_page = 0;
    $comments_pageurl = baseurl+'index.php?module=comments_manage&act=show&from_ajax=1&comments_page=';
    comments_name = "[*$name|sl*]";
    comments_resid = '[*$resid*]';
    comments_type = '[*$type|sl*]';
    fk_ajax = '?[*fk ajax=1*]';
    are_you_sure_to_delete_this_comment = '[*'are_you_sure_to_delete_this_comment'|lang|sl*]';
    //[*/if*]
</script>
<script type='text/javascript' src='[*$theme_path*]js/jquery.comments.js'></script>
<div id="comments_[*$name*]">
    <div class="white_color cornerText gray_border">
        <fieldset><legend>[*'comments_list'|lang*]</legend>
            [*if $comments*]
                <div id='allcomments_body'>
                    [*foreach from=$comments item=trow key=num*]
                        <div id="comment_[*$trow.id*]" class="comment_body">
                            <div
                                class="body_comment comment cornerText gray_color gray_border2">
                                <div class="title">
                                    [*if $title4comments*]
                                        <a href="[*gen_link module=$trow.type id=$trow.toid title=$title4comments cid=$trow.id*]">
                                        [*/if*]
                                        <img src="[*$theme_path*]engine_images/comment.png"
                                             alt="[*'comment'|lang*]" width="12">
                                        [*if $title4comments*]
                                        </a>
                                    [*/if*]
                                    [*if $trow.subject*]
                                        [*$trow.subject*]
                                    [*else*]
                                        [*'comment_no_subject'|lang*]
                                    [*/if*]
                                    <div class="author">[*$trow.username|gcl:$trow.group*], 
                                        [*date time=$trow.posted_time format='ymdhis'*]
                                        [*if  check_owner($trow.poster_id, 'edit_comm') || check_owner($trow.poster_id, 'del_comm') || ($trow.type == 'users' && $trow.toid == 'id'|user) || 'comment'|perm:2*]
                                            <span id="comment_[*$trow.id*]_buttons" class="hidden">&nbsp;&nbsp;&nbsp;&nbsp;[
                                                [*if 'comment'|perm:2*]
                                                    <a href="javascript:quote_comment('[*$trow.username|sl*]', [*$trow.id*]);">
                                                        <img src="[*$theme_path*]engine_images/quote.png" alt="[*'edit'|lang*]"
                                                             width="12">
                                                    </a>
                                                [*/if*]
                                                [*if check_owner($trow.poster_id, 'edit_comm')*]
                                                    <a href="javascript:edit_comment('comment_[*$trow.id*]', [*$trow.id*]);">
                                                        <img src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]"
                                                             width="12">
                                                    </a>
                                                [*/if*]
                                                [*if  check_owner($trow.poster_id, 'del_comm') || ($trow.type == 'users' && $trow.toid == 'id'|user)*]
                                                    <a href="javascript:del_comment('comment_[*$trow.id*]', [*$trow.id*]);">
                                                        <img src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"
                                                             width="12">
                                                    </a>
                                                [*/if*]]
                                            </span> 
                                        [*/if*]
                                    </div>
                                </div>
                                [*assign var='trow' value=$trow|@decus*]
                                <div class="body">
                                    <div class='content'>
                                        <div class='cavatar_field'>[*$trow.avatar|ua*]</div>
                                        <div class='cpadded_body'>
                                            [*$trow.text|ft:false:true*]
                                        </div>
                                    </div>
                                    [*if $trow.signature*]
                                        <div class='br'></div>
                                        <hr style="width: 200px; text-align: left;">
                                        [*$trow.signature|ft*]
                                    [*/if*]
                                </div>
                            </div>
                            <br>
                        </div>
                    [*/foreach*]
                    <div align="left" class="cornerText gray_color">
                        [*$pages*]
                    </div>
                </div>
            [*else*]
                [*message lang_var='no_comments' type='info'*]
            [*/if*]
        </fieldset>
    </div>
</div>