[*if $row*]
    <script type="text/javascript">
        init_tablesorter();
    </script>
    <table class="tablesorter">
        <thead>
            <tr>
                <th>[*'usercp_bookmarks_title'|lang*]</th>
                <th>[*'usercp_bookmarks_added'|lang*]</th>
                <th class="js_nosort">[*'delete'|lang*]</th>
            </tr>
        </thead>
        <tbody>
            [*foreach from=$row item=arr*]
                [*assign var="arrtype" value=$arr.type*]
                <tr id="usercp_bookmark_[*$arr.id*]">
                    <td><b>[*"usercp_bookmarks_type_$arrtype"|lang*]:</b>
                        <a href="[*gen_link module=$arr.type id=$arr.toid title=$arr.res_name*]">[*$arr.res_name*]</a></td>
                    <td>[*date time=$arr.added*]</td>
                    <td><a href="javascript:delete_bookmark('[*$arr.id*]', true)"><img
                                src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a></td>
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*else*]
    [*message lang_var="usercp_bookmarks_nothing" type="info"*]
[*/if*]
