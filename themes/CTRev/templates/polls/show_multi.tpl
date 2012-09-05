[*if 'polls'|perm:3 && $curuser*]
    <div align="right"><a href="[*gen_link module='polls' act='add'*]"><img
                src="[*$theme_path*]engine_images/add_small.png" alt="[*'add'|lang*]"
                title="[*'add'|lang*]">&nbsp;<b>[*'polls_add_poll'|lang*]</b></a>
    </div>
[*/if*]
<div class="content">
    <div class="tr">
        [*foreach from=$poll_rows item=poll_row key=num*]
            [*if $num % 2 == 0 && $num*]
            </div>
            <div class="tr">
            [*/if*]
            <div class="td[*if $num % 2 == 1*] td_padded_left[*else*] td_padded_right[*/if*]"
                 style="width: 50%;">[*include file='polls/show_single.tpl'*]</div>
        [*/foreach*]
    </div>
</div>