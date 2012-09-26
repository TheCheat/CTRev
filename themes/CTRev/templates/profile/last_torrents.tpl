[*if $torrents_row*]
    <script type="text/javascript">init_tablesorter();</script>
    <table width="100%" class="tablesorter">
        <thead>
            <tr>
                <th width="70%"><b>[*'users_torrents_title'|lang*]</b></th>
                <th><b>[*'users_torrents_added'|lang*]</b></th>
                [*if isset($torrents_row[0].poster_id)*]
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
                    [*if $thisrow.username*]
                        <td align="center"><b>[*$thisrow.username|gcl:$thisrow.group*]</b></td>
                    [*/if*]
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*else*] 
    [*assign var='lv' value='users_no_torrents'*]
    [*if !isset($torrents_row[0].username)*]
        [*assign var='lv' value='downm_no_torrents'*]
    [*/if*]
    [*message lang_var=$lv type='info'*] 
[*/if*]
