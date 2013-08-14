[*if !$karma_type*]
    [*assign var='karma_type' value='content'*]
[*/if*]
<div class="nobr">
    <span class='karma'>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/minus.png" class='rating_button minus' alt="-"
                 onclick="set_rating(-1, '[*$uid*]', 'users', '#karma_[*$uid*][*if $tid*]_[*$tid*][*/if*]', '', '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
        <span id="karma_[*$uid*][*if $tid*]_[*$tid*][*/if*]">[*$karma*]</span>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/plus.png" class='rating_button plus' alt="+"
                 onclick="set_rating(1, '[*$uid*]', 'users', '#karma_[*$uid*][*if $tid*]_[*$tid*][*/if*]', '', '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
    </span>
</div>