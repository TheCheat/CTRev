[*if $torrents_row*]
    <script type="text/javascript">init_tablesorter();</script>
    <table width="100%" class="tablesorter">
        <thead>
            <tr>
                <th width="70%"><b>[*'users_torrents_title'|lang*]</b></th>
                <th><b>[*'users_torrents_added'|lang*]</b></th>
                [*if $torrents_row[0].username*]
                    <th><b>[*'users_author'|lang*]</b></th>
                [*/if*]
            </tr>
        </thead>
        <tbody>
            [*foreach from=$torrents_row item=thisrow*]
                <tr>
                    <td><a href="[*gen_link module='torrents' id=$thisrow.id title=$thisrow.title*]">[*$thisrow.title*]</a><br>
                        <p>
                            <font size="1"><b>[*'users_torrents_cats'|lang*]: </b>
                                [*$thisrow.category_id|@print_cats*]
                            </font>
                        </p>
                    </td>
                    <td align="center">[*date time=$thisrow.posted_time*]</td>
                    [*if $thisrow.poster_id*]
                        <td align="center"><b>[*$thisrow.username|gcl:$thisrow.group*]</b></td>
                    [*/if*]
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*else*] 
    [*message lang_var='users_no_torrents' type='info'*] 
[*/if*]
