<!-- F*cking huge condition, i'd like to separate it, but smarty so stupid to do it. -->
[*if ((!$poll_row.voted_answers && (!$show_voting || $show_voting == -1)) || ($poll_row.change_votes && $show_voting == -1)) && (!$poll_row.poll_ends || $curtime-$poll_row.posted_time<=$poll_ends) && 'polls'|perm*]
    <table width="100%" class="tablesorter not_auto_odd">
        [*foreach from=$poll_row.answers item=answer key=anum*]
            <tr[*if $anum % 2 !=0*] class="odd"[*/if*]>
                <td><input
                        [*if $poll_row.max_votes==1*]
                            type="radio"
                        [*else*]
                            type="checkbox" onclick="check_max_selected(this, '[*$poll_row.id*]', '[*$poll_row.max_votes*]', '[*if $short_votes*]1[*/if*]');"
                        [*/if*]
                        class="answer_[*$poll_row.id*][*if $short_votes*]_short[*/if*]"
                        name="answers[]" value="[*$anum*]">&nbsp;[*$answer|ft:"simple"*]</td>
            </tr>
        [*/foreach*]
    </table>
    <div align="center"><input type="button" value="[*'polls_vote'|lang*]"
                               onclick="poll_vote('[*$poll_row.id*]', '[*if $short_votes*]1[*/if*]')">&nbsp;<input
                               type="button" value="[*'polls_show_results'|lang*]"
                               onclick="change_voting_type('[*$poll_row.id*]', 0, '[*if $short_votes*]1[*/if*]')">
    </div>
[*else*]
    <table width="100%"
           class="tablesorter not_auto_odd[*if $short_votes*] short_votes_table[*/if*]"
           [*if $short_votes*] 
               cellpadding="0" cellspacing="0"
           [*/if*]>
        [*assign var="curstyle" value="0"*]
        [*foreach from=$poll_row.answers item=answer key=anum*] 
            [*if $curstyle>=$styles_count*]
                [*assign var="curstyle" value="0"*]
            [*/if*]
            <tr class="first[*if $anum % 2 !=0*] odd[*/if*]">
                <td class="first">
                    [*if !$short_votes && $poll_row.show_voted*]
                        <a href="javascript:void(0);" onclick="open_spoiler(this);"
                           class="spoiler_icon"></a>
                    [*/if*]
                    [*if (is_array($poll_row.voted_answers) && in_array($anum,$poll_row.voted_answers))*]
                        <b>[*$answer|ft:"simple"*]</b>
                    [*else*]
                        [*$answer|ft:"simple"*]
                    [*/if*]
                    [*if !$short_votes && $poll_row.usernames.$anum && $poll_row.show_voted*]
                        <div class="spoiler_content hidden"><font size="1">[*'polls_selected'|lang*]
                                [*$poll_row.usernames.$anum*]</font>
                        </div>
                    [*/if*]
                </td>
                [*if $short_votes*]
                    <td></td>
                </tr>
                <tr [*if $anum % 2 !=0*]class="odd"[*/if*]>
                [*/if*]
                [*assign var='cvotes' value=$poll_row.answers_counts.$anum*]
                <td class="votes_column[*if $short_votes*] big_column[*/if*]">
                    <div class="votes [*$votes_styles.$curstyle*]">
                        <span class="hidden">
                            [*$cvotes|polls_votepercent:$poll_row.votes_count*]
                        </span>
                    </div>
                </td>
                <td class="percent_column">
                    [*$cvotes*]([*$cvotes|polls_votepercent:$poll_row.votes_count*]%)
                </td>
            </tr>
            [*assign var="curstyle" value=$curstyle+1*] 
        [*/foreach*]
    </table>
    <div align="left">
        <font size="1">
            [*if $poll_row.poll_ends*]
                [*if $curtime-$poll_row.posted_time<=$poll_ends*]
                    [*'polls_ends'|lang*]
                    <b>[*date time=$poll_row.posted_time+$poll_ends format='ymdhis'*]</b>
                [*else*]
                    [*'polls_ended'|lang*]
                [*/if*]
            [*/if*]
            [*'polls_total_votes'|pf:$poll_row.votes_count_real*]
        </font>
    </div>
    [*if $poll_row.change_votes && $curuser && (!$poll_row.poll_ends || $curtime-$poll_row.posted_time<=$poll_ends)*]
        <div align="center"><input type="button" value="[*'polls_voting'|lang*]" onclick="change_voting_type('[*$poll_row.id*]', true, '[*if $short_votes*]1[*/if*]')"></div>
        [*/if*]
        [*if $short_votes*]
            [*include file='polls/scripts.tpl'*]
        [*/if*]
    <script type="text/javascript">init_votes('[*$poll_row.id*]', '[*if $short_votes*]1[*/if*]');</script>
[*/if*]