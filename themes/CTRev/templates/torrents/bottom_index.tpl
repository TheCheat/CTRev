<div class="tr">
    <div class="td">
        [*if $full_torrents*]
            [*if $torrents.last_edit*]
                <br><hr align='left' class='torrents_lastedit'><font size="1">
                    [*'torrents_last_edit'|lang*][*$torrents.eu|gcl:$torrents.eg*] [*date time=$torrents.last_edit format='ymdhis'*].
                    [*'torrents_edit_count'|pf:$torrents.edit_count*]
                    [*if $torrents.edit_reason*]
                        <br>
                        <b>[*'torrents_last_edit_reason'|lang*]</b> <i>[*$torrents.edit_reason*]</i>
                    [*/if*]
                </font>
            [*/if*]
        [*/if*]
        <hr style="padding-top: 5px;">

        <div class="content">
            <div class="tr">
                <div class="td" align="left">
                    [*$torrents.cats_arr|@print_cats:$torrents.cat_parents*]
                </div>
                <div class="td" align="right">
                    [*if $curuser*]
                        <a href="javascript:make_mailer('[*$torrents.id*]', 'torrents');">
                            <img src="[*$theme_path*]engine_images/email_add_small.png"
                                 title="[*'torrents_add_torrent_to_mailer'|lang*]"
                                 alt="[*'torrents_add_torrent_to_mailer'|lang*]">
                        </a>
                    [*/if*]
                    [*'torrents_added'|lang*]
                    [*date time=$torrents.posted_time*], 
                    <!--[*'torrents_poster'|lang*]-->
                    [*assign var="karma" value=$torrents.username|gcl:$torrents.group*]
                    [*assign var="uid" value=$torrents.poster_id*]
                    [*assign var="tid" value=$torrents.id*]
                    [*include file='profile/karma.tpl'*]
                    [*if isset($torrents.comm_count)*]
                        <img src="[*$theme_path*]engine_images/comment.png" alt="[*'comment'|lang*]"
                             width="12">&nbsp;[*$torrents.comm_count*]
                    [*/if*]
                    [*if !$full_torrents*]&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="[*gen_link module='torrents' id=$torrents.id title=$torrents.title*]"><b>[[*'torrents_full'|lang*]]</b></a>
                    [*/if*]
                </div>
            </div>
        </div>
    </div>
</div>