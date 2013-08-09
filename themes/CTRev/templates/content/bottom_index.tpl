<div class="tr">
    <div class="td">
        [*if $full_content*]
            [*if !"torrents_on"|config || !$content.info_hash*]
                [*assign var='ctags' value=$content.tags*]
            [*/if*]
            [*if $ctags || $content.last_edit*]
                <br><hr align='left' class='content_lastedit'><font size="1">
            [*/if*]
            [*if $ctags*]
                <div class='content'>
                    <font size='1'>[*'content_tags_details'|lang*]: [*$ctags*]</font>
                </div>
            [*/if*]
            [*if $content.last_edit*]
                [*'content_last_edit'|lang*][*$content.eu|gcl:$content.eg*] [*date time=$content.last_edit format='ymdhis'*].
                [*'content_edit_count'|pf:$content.edit_count*]
                [*if $content.edit_reason*]
                    <br>
                    <b>[*'content_last_edit_reason'|lang*]</b> <i>[*$content.edit_reason*]</i>
                [*/if*]
                </font>
            [*/if*]
        [*/if*]
        <hr style="padding-top: 5px;margin-bottom:3px;">

        <div class="content">
            <div class="tr">
                <div class="td" align="left">
                    [*$content.cats_arr|@print_cats:$content.cat_parents*]
                </div>
                <div class="td" align="right">
                    [*if $curuser && "mailer_on"|config*]
                        <a href="javascript:make_mailer('[*$content.id*]', 'content');">
                            <img src="[*$theme_path*]engine_images/email_add_small.png"
                                 title="[*'content_add_item_to_mailer'|lang*]"
                                 alt="[*'content_add_item_to_mailer'|lang*]" class='mailer_icon'>
                        </a>
                    [*/if*]
                    [*'content_added'|lang*]
                    [*date time=$content.posted_time*], 
                    <!--[*'content_poster'|lang*]-->
                    [*assign var="karma" value=$content.username|gcl:$content.group*]
                    [*assign var="uid" value=$content.poster_id*]
                    [*assign var="tid" value=$content.id*]
                    [*include file='profile/karma.tpl'*]
                    [*if isset($content.comm_count)*]
                        <img src="[*$theme_path*]engine_images/comment.png" alt="[*'comment'|lang*]"
                             width="12">&nbsp;[*$content.comm_count*]
                    [*/if*]
                    [*if !$full_content*]&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="[*gen_link module='content' id=$content.id title=$content.title*]"><b>[[*'content_full'|lang*]]</b></a>
                    [*/if*]
                </div>
            </div>
        </div>
    </div>
</div>