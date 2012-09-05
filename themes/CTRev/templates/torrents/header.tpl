[*if $torrents_buttons_on*]
    <div class='center_block_header_buttons'>
        <div class='center_block_header_buttons_left'></div>
        <!-- Кнопки -->
        <div class="buttons_inner">
            [*if $curuser*]
                <div class="torrent_button">
                    <a href="javascript:add_bookmark('[*$torrents.id*]', 'torrents');" 
                       [*if $torrents.bookmark_id*] 
                           class="hidden"
                       [*/if*]><img src="[*$theme_path*]images/torrents/favourite.png" alt="[*'add'|lang*]"
                          id="bookmark_add_[*$torrents.id*]" title="[*'add'|lang*]"></a>
                    <a href="javascript:delete_bookmark('[*$torrents.id*]', 'torrents');"
                       [*if !$torrents.bookmark_id*] 
                           class="hidden"
                       [*/if*]><img id="bookmark_del_[*$torrents.id*]"
                          src="[*$theme_path*]images/torrents/favourite_del.png"
                          alt="[*'delete'|lang*]" title="[*'delete'|lang*]"></a>
                </div>
            [*/if*]

            [*if check_owner($torrents.poster_id, 'edit_torrents') || check_owner($torrents.poster_id, 'del_torrents')*]
                <div class="torrent_button" onclick="tooltip_open(this, 'div.torrents_actions');">
                    <img src="[*$theme_path*]images/torrents/settings.png" alt="[*'actions'|lang*]" title="[*'actions'|lang*]">
                </div>
            [*/if*]
            <div class="torrent_button" onclick="tooltip_open(this, 'div.torrents_rating');">
                <img src="[*$theme_path*]images/torrents/rating.png" alt="[*'rating'|lang*]" title="[*'rating'|lang*]">
            </div>
            <div class="torrent_button" onclick="close_torrent(this);">
                <img src="[*$theme_path*]images/torrents/close.png" alt="[*'torrent_close'|lang*]" title="[*'torrent_close'|lang*]">
            </div>
        </div>
        <!-- Кнопки -->
        <div class='center_block_header_buttons_right'></div>
    </div>
[*/if*]