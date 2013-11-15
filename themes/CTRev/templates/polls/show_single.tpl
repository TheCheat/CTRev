[*assign var='poll_row' value=$poll_row|@polls_prefilter*]
[*if $poll_row.id*]
    [*assign var='poll_ends' value=$poll_row.poll_ends*60*60*24*]
    <div class="polls_all[*if $single_poll*] single_poll[*/if*]"
         id="question_id[*$poll_row.id*][*if $short_votes*]_short[*/if*]">
        <div class="cornerText gray_color gray_border">
            <form action="javascript:void(0);" method="post">
                <fieldset><legend>
                        [*if !$short_votes*]
                            [*'polls_question'|lang*]
                        [*/if*]
                        <a href="[*gen_link module='polls' act='view' id=$poll_row.id*]">
                            <font class="normal_question">[*$poll_row.question|ft:"simple"*]</font>
                        </a>
                        [*if !$short_votes*]
                            [*if check_owner($poll_row.poster_id, 'edit_polls')*]
                                <a href="javascript:edit_polls('[*$poll_row.id*]', '[*if $short_votes*]1[*/if*]');">
                                    <img src="[*$theme_path*]engine_images/edit.png" height="12"
                                         alt="[*'edit'|lang*]" title="[*'edit'|lang*]">
                                </a>
                            [*/if*]
                            [*if check_owner($poll_row.poster_id, 'del_polls')*]
                                <a href="javascript:delete_polls('[*$poll_row.id*]', '[*if $short_votes*]1[*/if*]');">
                                    <img src="[*$theme_path*]engine_images/delete.png" height="12"
                                         alt="[*'delete'|lang*]" title="[*'delete'|lang*]">
                                </a>
                            [*/if*]
                        [*/if*]
                    </legend>
                    <div class="polls_si">
                        <div class="status_icon" id="poll[*$poll_row.id*][*if $short_votes*]_short[*/if*]_status_icon"></div>
                    </div>
                    [*include file='polls/votes.tpl'*]
                </fieldset>
            </form>
        </div>
    </div>
    <br>
[*else*]
    [*'polls_no_exists'|lang*]
    [*if 'polls'|perm:3 && $curuser*]
        [*gen_link module='polls' act='add' assign='url'*]
        [*'polls_want_add'|pf:$url*]
    [*/if*]
[*/if*]