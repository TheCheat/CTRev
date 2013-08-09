<div class='cornerText gray_color2 gray_border2'>
    <fieldset>
        <legend>[*$title*]</legend>
        [*if $type=='tpl'*]
            [*include file=$content*]
        [*elseif $type=='bbcode'*]
            [*$content|ft*]
        [*else*]
            [*$content*]
        [*/if*]
    </fieldset>
</div>