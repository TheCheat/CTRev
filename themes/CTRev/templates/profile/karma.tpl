[*if !$karma_type*]
    [*assign var='karma_type' value='content'*]
[*/if*]
<span class='karma'>
    <nobr>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/minus.png" alt="-" class='minus'
                 onclick="set_rating(-1, '[*$uid*]', 'users', '#karma_[*$uid*]', null, '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
        <span id="karma_[*$uid*][*if $tid*]_[*$tid*][*/if*]">[*$karma*]</span>
        [*if 'vote'|perm && 'id'|user!=$uid*]
            <img src="[*$theme_path*]engine_images/plus.png" class='plus' alt="+"
                 onclick="set_rating(1, '[*$uid*]', 'users', '#karma_[*$uid*]', null, '[*$tid*]', '[*$karma_type|sl*]')">
        [*/if*]
    </nobr>
</span>