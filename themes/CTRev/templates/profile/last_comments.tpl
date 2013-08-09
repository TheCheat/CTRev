[*if $comm_row*]
    <script type="text/javascript">init_tablesorter();</script>
    <table width="100%" class="tablesorter">
        <thead>
            <tr>
                <th width="50%"><b>[*'users_comments_added'|lang*]</b></th>
                <th><b>[*'users_comments_where'|lang*]</b></th>

                [*if isset($comm_row[0].poster_id)*]
                    <th><b>[*'users_author'|lang*]</b></th>

                [*/if*]
            </tr>
        </thead>
        <tbody>
            [*assign var='c' value=0*]
            [*foreach from=$comm_row item=thisrow*]
                <!--[*$c++*]-->
                <tr>
                    <td><a href="[*gen_link module=$thisrow.type id=$thisrow.toid title=$thisrow.title cid=$thisrow.id*]">
                            [*date time=$thisrow.posted_time*]
                        </a>
                    </td>
                    <td>
                        <b>[*"users_comments_`$thisrow.type`"|lang*]: </b>
                        <a href="[*gen_link module=$thisrow.type id=$thisrow.toid title=$thisrow.title*]">[*$thisrow.title*]</a>
                    </td>
                    [*if isset($thisrow.poster_id)*]
                        <td align="center"><b>[*$thisrow.username|gcl:$thisrow.group*]</b></td>

                    [*/if*]
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*else*] 
    [*assign var='lv' value='users_no_comments'*]
    [*if 'downm_no_comments'|islang*]
        [*assign var='lv' value='downm_no_comments'*]
    [*/if*]
    [*message lang_var=$lv type='info'*] 
[*/if*]
