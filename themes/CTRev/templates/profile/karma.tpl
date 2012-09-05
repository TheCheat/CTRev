[*if !$karma_type*]
    [*assign var='karma_type' value='torrents'*]
[*/if*]
<span class='karma'>
    <nobr>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/minus.png" alt="-" class="clickable"
                 onclick="set_rating(-1, '[*$uid*]', 'users', '#karma_[*$uid*]', null, '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
        <span style="position: relative;font-weight: bold;[*if 'vote'|perm && 'id'|user!=$uid*] top: -5px;[*/if*]"
              id="karma_[*$uid*][*if $tid*]_[*$tid*][*/if*]">[*$karma*]</span>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/plus.png" alt="+" class="clickable"
                 onclick="set_rating(1, '[*$uid*]', 'users', '#karma_[*$uid*]', null, '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
    </nobr>
</span>