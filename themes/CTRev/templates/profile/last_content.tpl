[*if $content_row*]
    <script type="text/javascript">init_tablesorter();</script>
    <table width="100%" class="tablesorter">
        <thead>
            <tr>
                <th width="70%"><b>[*'users_content_title'|lang*]</b></th>
                <th><b>[*'users_content_added'|lang*]</b></th>
                        [*if isset($content_row[0].poster_id)*]
                    <th><b>[*'users_author'|lang*]</b></th>
                        [*/if*]
            </tr>
        </thead>
        <tbody>
            [*foreach from=$content_row item=thisrow*]
                <tr>
                    <td><a href="[*gen_link module='content' id=$thisrow.id title=$thisrow.title*]">[*$thisrow.title*]</a><br>
                        <p>
                            <font size="1"><b>[*'users_content_cats'|lang*]: </b>
                            [*$thisrow.category_id|@print_cats*]
                            </font>
                        </p>
                    </td>
                    <td align="center">[*date time=$thisrow.posted_time*]</td>
                    [*if $thisrow.poster_id*]
                        <td align="center">
                            <b>[*$thisrow.username|gcl:$thisrow.group*]</b>
                        </td>
                    [*/if*]
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*else*] 
    [*assign var='lv' value='users_no_content'*]
    [*if 'downm_no_content'|islang*]
        [*assign var='lv' value='downm_no_content'*]
    [*/if*]
    [*message lang_var=$lv type='info'*] 
[*/if*]
