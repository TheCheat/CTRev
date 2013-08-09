[*if $content_buttons_on*]
    <div class='center_block_header_buttons'>
        <div class='center_block_header_buttons_left'></div>
        <!-- Кнопки -->
        <div class="buttons_inner">
            [*if $curuser*]
                <div class="content_button">
                    <a href="javascript:add_bookmark('[*$content.id*]', 'content');" 
                       [*if $content.bookmark_id*] 
                           class="hidden"
                       [*/if*]><img src="[*$theme_path*]images/content/favourite.png" alt="[*'add'|lang*]"
                          id="bookmark_add_[*$content.id*]" title="[*'add'|lang*]"></a>
                    <a href="javascript:delete_bookmark('[*$content.id*]', 'content');"
                       [*if !$content.bookmark_id*] 
                           class="hidden"
                       [*/if*]><img id="bookmark_del_[*$content.id*]"
                          src="[*$theme_path*]images/content/favourite_del.png"
                          alt="[*'delete'|lang*]" title="[*'delete'|lang*]"></a>
                </div>
            [*/if*]

            [*if check_owner($content.poster_id, 'edit_content') || check_owner($content.poster_id, 'del_content')*]
                <div class="content_button" onclick="tooltip_open(this, 'div.content_actions');">
                    <img src="[*$theme_path*]images/content/settings.png" alt="[*'actions'|lang*]" title="[*'actions'|lang*]">
                </div>
            [*/if*]
            <div class="content_button" onclick="tooltip_open(this, 'div.content_rating');">
                <img src="[*$theme_path*]images/content/rating.png" alt="[*'rating'|lang*]" title="[*'rating'|lang*]">
            </div>
            <div class="content_button" onclick="close_content(this);">
                <img src="[*$theme_path*]images/content/close.png" alt="[*'content_close'|lang*]" title="[*'content_close'|lang*]">
            </div>
        </div>
        <!-- Кнопки -->
        <div class='center_block_header_buttons_right'></div>
    </div>
[*/if*]