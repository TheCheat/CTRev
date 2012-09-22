[*if !$from_edit*]
    <script type="text/javascript">
        init_tablesorter();
        $pageurl = '[*$BASEURL|sl*]index.php?module=torrents&from_ajax=1&[*$add_url|sl*]&page=';
        [*if !$from_ajax*]
            fk_ajax = '?[*fk ajax=1*]';
            full_torrents = '[*if $full_torrents*]1[*else*]0[*/if*]';
            lang_no_comments = '[*'no_comments'|lang|sl*]';
            torrents_are_sure_to_delete_torrents = '[*'torrents_are_sure_to_delete_torrents'|lang|sl*]';
            please_refresh_page = '[*'please_refresh_page'|lang|sl*]';
            $page = 0;
        [*/if*]
    </script>

    [*if !$from_ajax*]
        <script src="[*$theme_path*]js/jquery.torrents.js" type="text/javascript"></script>
    [*/if*]
    <div id="top-ajax-torrents"></div>
    <div id="body-ajax-torrents">
        [*if !$torrents_row*]
            <div class="cat_torrents">
                <div class="cornerText gray_color">
                    <div class="cat_torrents_name">[*'torrents_no_torrents'|lang*]</div>
                    <div class="cat_torrents_descr">[*'torrents_no_torrents_descr'|lang*]
                        [*if 'torrents'|perm:2*]
                            [*'torrents_no_torrents_descr_want'|lang*]
                            <a href="[*gen_link module='torrents' act='add'*]">
                                [*'torrents_no_torrents_descr_add'|lang*]</a>
                            [*/if*]
                    </div>
                </div>
            </div>
        [*else*] 
            [*if $cat_rows[0]*]
                <div class="cat_torrents">
                    <div class="cornerText gray_color">
                        <div class="cat_torrents_name"><a
                                href="[*gen_link module='torrents' cat=$cat_rows[2] act='rss'*]"><img
                                    src="[*$theme_path*]engine_images/rss-feed.png"
                                    title="[*'torrents_rss_torrents'|lang*]" alt="[*'torrents_rss_torrents'|lang*]"></a>&nbsp;[*$cat_rows[0]*]</div>
                        <div class="cat_torrents_descr">[*$cat_rows[1]*]</div>
                        <div align="right">
                            [*if $curuser*]
                                <a href="javascript:make_mailer('[*$cat_rows[3]*]', 'category');"><img
                                        src="[*$theme_path*]engine_images/email_add.png"
                                        title="[*'torrents_add_cat_to_mailer'|lang*]" alt="[*'torrents_add_cat_to_mailer'|lang*]"></a>
                                [*/if*]
                                [*if 'torrents'|perm:2*]
                                <a href="[*gen_link module='torrents' cat=$cat_rows[2] act='add'*]"><img
                                        src="[*$theme_path*]engine_images/add.png"
                                        title="[*'torrents_add_torrents'|lang*]" alt="[*'torrents_add_torrents'|lang*]"></a>
                                [*/if*]
                        </div>
                    </div>
                </div>
            [*elseif 'torrents'|perm:2 && !$full_torrents*]
                <!--<div class="add_torrent"><a href="[*gen_link module='torrents' act='add'*]"><img
                            src="[*$theme_path*]engine_images/add_small.png" alt="[*'add'|lang*]"
                            title="[*'add'|lang*]">&nbsp;<b>[*'torrents_add_torrents'|lang*]</b></a><br><br>
                </div>-->
            [*/if*]
        [*/if*]
    [*/if*]
    [*assign var="torrents_buttons_on" value="true"*]

    [*foreach from=$torrents_row item=torrents*]
        [*$full_torrents|torrents_prefilter:$torrents*]
        <div id="torrents_[*$torrents.id*]">

            [*if $full_torrents*]
                <!-- Верняя часть деталей торрента.Начало -->
                [*include file='torrents/top_index.tpl'*]
                <!-- Верняя часть деталей торрента.Конец -->
            [*/if*]

            [*assign var="title" value=$torrents.title*]
            [*assign var='langnew' value='new'|lang*]
            [*assign var='langtsticky' value='torrents_sticky'|lang*]
            [*if !$torrents.readed && !$full_torrents && $curuser && $torrents.posted_time>$last_clean_rt*]
                [*assign var='title' value=$title|prepend_title_icon:'new':$langnew*]
            [*/if*]
            [*if $torrents.sticky*]
                [*assign var='title' value=$title|prepend_title_icon:'sticky':$langtsticky*]
            [*/if*]

            [*include file='blocks/center_block_header.tpl'*]

            <!-- Менюшка сверху торрента.Начало -->
            <div class="hidden">
                [*if check_owner($torrents.poster_id, 'edit_torrents') || check_owner($torrents.poster_id, 'del_torrents') || 'del_comm'|perm:2*]
                    <div class='torrents_actions'>
                        <ul>
                            [*if check_owner($torrents.poster_id, 'edit_torrents') && $torrents.banned != 2*]
                                <li><a href="javascript:edit_torrents('[*$torrents.id*]', '#torrents_body_[*$torrents.id*]');close_tooltip();">
                                        [*'torrents_fast_edit'|lang*]</a></li>
                                <li><a href="[*gen_link module='torrents' act='edit' id=$torrents.id*]" onclick="close_tooltip();">
                                        [*'torrents_edit_torrents'|lang*]</a></li>
                                    [*/if*]
                                    [*if 'del_comm'|perm:2*]
                                <li><a href="javascript:clear_tcomments('[*$torrents.id*]');close_tooltip();">
                                    [*'torrents_del_comments'|lang*]</a></li>[*/if*]
                                    [*if check_owner($torrents.poster_id, 'del_torrents')*]
                                <li><a href="javascript:delete_torrents('[*$torrents.id*]');close_tooltip();">
                                    [*'torrents_del_torrents'|lang*]</a></li>[*/if*] 
                        </ul>
                    </div>
                [*/if*]
                <div class='torrents_rating'>[*display_rating rid=$torrents.id owner=$torrents.poster_id res=$torrents*]</div>
            </div>
            <!-- Менюшка сверху торрента.Конец -->

            [*include file='blocks/center_block_content.tpl'*]

            <div class="content" id="torrents_body_[*$torrents.id*]">
                <div class="tr">
                    <div class="td"><div class="content">
                            [*$torrents.screenshots|show_image:true*]
                            [*$torrents.content|ft:false:true*]
                            [*assign var='tscreen' value=$torrents.screenshots|show_image:false*]
                            [*if $full_torrents && $tscreen*]<fieldset class='dscreenshots'>
                                    <legend>[*'torrents_details_screenshots'|lang*]</legend>
                                    <center>[*$tscreen*]</center>
                                </fieldset>
                            [*/if*]
                        </div></div>
                </div>

                <!-- Нижняя часть деталей торрента.Начало -->
                [*include file='torrents/bottom_index.tpl'*]
                <!-- Нижняя часть деталей торрента.Конец -->

            </div>
            [*include file='blocks/center_block_footer.tpl'*]</div>
        <br>
    [*/foreach*]
    [*if !$from_edit*] 
        [*if !$full_torrents*]
            <div align="left" class="cornerText gray_color torrents_paginator">
                <noscript>
                    [*if $page>1*]
                        <a href="[*gen_link module='torrents' page=$page-1*]">[*'paginator_prev'|lang*]</a>&nbsp;
                    [*/if*]
                    [*if $page<$maxpage*]
                        <a href="[*gen_link module='torrents' page=$page+1*]">[*'paginator_next'|lang*]</a>
                    [*/if*]
                </noscript>
                [*$pages*]
            </div>
        [*/if*]
    </div>
[*/if*]
[*if !$from_edit && $full_torrents*]
    [*display_comments resid=$torrents.id*]
[*/if*]