[*if !$from_edit*]
    <script type="text/javascript">
        init_tablesorter();
        $pageurl = 'index.php?module=content&from_ajax=1&[*$add_url|sl*]&page=';
        [*if !$from_ajax*]
        fk_ajax = '?[*fk ajax=1*]';
        full_content = '[*if $full_content*]1[*else*]0[*/if*]';
        lang_no_comments = '[*'no_comments'|lang|sl*]';
        content_are_sure_to_delete = '[*'content_are_sure_to_delete'|lang|sl*]';
        please_refresh_page = '[*'please_refresh_page'|lang|sl*]';
        $page = 0;
        [*/if*]
    </script>

    [*if !$from_ajax*]
        <script src="js/jquery.content.js" type="text/javascript"></script>
    [*/if*]
    <div id="top-ajax-content"></div>
    <div id="body-ajax-content">
        [*include file="content/categories.tpl"*]
    [*/if*]
    [*assign var="content_buttons_on" value="true"*]

    [*foreach from=$content_row item=content*]
        [*$full_content|content_prefilter:$content*]
        <div id="content_[*$content.id*]">

            [*if $full_content*]
                <!-- Верняя часть деталей торрента.Начало -->
                [*include file='content/top_index.tpl'*]
                <!-- Верняя часть деталей торрента.Конец -->
            [*/if*]

            [*assign var="title" value=$content.title*]
            [*assign var='langnew' value='new'|lang*]
            [*assign var='langtsticky' value='content_sticky'|lang*]
            [*if !$content.readed && !$full_content && $curuser && $content.posted_time>$last_clean_rc*]
                [*assign var='title' value=$title|prepend_title_icon:'new':$langnew*]
            [*/if*]
            [*if $content.sticky*]
                [*assign var='title' value=$title|prepend_title_icon:'sticky':$langtsticky*]
            [*/if*]

            [*include file='blocks/center_block_header.tpl'*]

            <!-- Менюшка сверху торрента.Начало -->
            <div class="hidden">
                [*if check_owner($content.poster_id, 'edit_content') || check_owner($content.poster_id, 'del_content') || 'del_comm'|perm:2*]
                    <div class='content_actions'>
                        <ul>
                            [*if check_owner($content.poster_id, 'edit_content') && $content.banned != 2*]
                                <li><a href="javascript:edit_content('[*$content.id*]', '#content_body_[*$content.id*]');close_tooltip();">
                                        [*'content_fast_edit'|lang*]</a></li>
                                <li><a href="[*gen_link module='content' act='edit' id=$content.id*]" onclick="close_tooltip();">
                                        [*'content_edit'|lang*]</a></li>
                                    [*/if*]
                                    [*if 'del_comm'|perm:2*]
                                <li><a href="javascript:clear_tcomments('[*$content.id*]');close_tooltip();">
                                    [*'content_del_comments'|lang*]</a></li>[*/if*]
                                    [*if check_owner($content.poster_id, 'del_content')*]
                                <li><a href="javascript:delete_content('[*$content.id*]');close_tooltip();">
                                    [*'content_delete'|lang*]</a></li>[*/if*] 
                        </ul>
                    </div>
                [*/if*]
                <div class='content_rating'>[*display_rating toid=$content.id type='content' owner=$content.poster_id res=$content*]</div>
            </div>
            <!-- Менюшка сверху торрента.Конец -->

            [*include file='blocks/center_block_content.tpl'*]

            <div class="content" id="content_body_[*$content.id*]">
                <div class="tr">
                    <div class="td"><div class="content">
                            [*if "torrents_on"|config && $content.screenshots*]
                                [*$content.screenshots|show_image:true*]
                            [*/if*]
                            [*$content.content|ft:false:true*]
                            [*if "attach"|perm:1:2 && $full_content*]
                                [*display_attachments toid=$content.id type='content'*]
                            [*/if*]
                            [*if "torrents_on"|config && $content.screenshots*]
                                [*assign var='tscreen' value=$content.screenshots|show_image:false*]
                                [*if $full_content && $tscreen*]<fieldset class='dscreenshots'>
                                        <legend>[*'content_torrent_screenshots'|lang*]</legend>
                                        <div align='center'>[*$tscreen*]</div>
                                    </fieldset>
                                [*/if*]
                            [*/if*]
                        </div></div>
                </div>

                <!-- Нижняя часть деталей торрента.Начало -->
                [*include file='content/bottom_index.tpl'*]
                <!-- Нижняя часть деталей торрента.Конец -->

            </div>
            [*include file='blocks/center_block_footer.tpl'*]</div>
        <br>
    [*/foreach*]
    [*if !$from_edit*] 
        [*if !$full_content && $pages*]
            <div align="left" class="cornerText gray_color content_paginator">
                <noscript>
                [*if $page>1*]
                    <a href="[*gen_link module='content' page=$page-1*]">[*'paginator_prev'|lang*]</a>&nbsp;
                [*/if*]
                [*if $page<$maxpage*]
                    <a href="[*gen_link module='content' page=$page+1*]">[*'paginator_next'|lang*]</a>
                [*/if*]
                </noscript>
                [*$pages*]
            </div>
        [*/if*]
    </div>
[*/if*]
[*if !$from_edit && $full_content*]
    [*display_comments type='content' toid=$content.id*]
[*/if*]
[*if $from_ajax*]
    <script type='text/javascript'>
        if (typeof init_sexylightbox != "undefined")
            init_sexylightbox();
    </script>
[*/if*]