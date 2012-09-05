[*if !$from_ajax*]
    <script type="text/javascript">
        init_tablesorter();
    </script>
    [*include file='usercp/scripts.tpl'*]
    <table class="tablesorter" id="ucp_invite_table">
        <thead>
            <tr>
                <th>[*'usercp_invites_code'|lang*]</th>
                <th>[*'usercp_invites_used'|lang*]</th>
                <th>[*'usercp_invites_registered'|lang*]</th>
                <th>[*'usercp_invites_confirmed'|lang*]</th>
                <th class="js_nosort">[*'actions'|lang*]</th>
            </tr>
        </thead>
        <tbody>
        [*/if*]
        [*foreach from=$row item=thisrow*]
            <tr id="invite[*$thisrow.invite_id*]">
                <td>[*$thisrow.invite_id*]</td>
                <td>
                    [*if $thisrow.username*]
                        [*$thisrow.username|gcl:$thisrow.group*]
                    [*else*]<b>-</b>
                    [*/if*]
                </td>
                <td>
                    [*if $thisrow.registered*]
                        [*date time=$thisrow.registered*]
                    [*else*]
                        <b>-</b>
                    [*/if*]
                </td>
                <td class="confirm">
                    [*if $thisrow.registered*]
                        [*if $thisrow.confirmed>=2*]
                            [*'yes'|lang*]
                        [*else*]
                            [*'no'|lang*]
                        [*/if*]
                    [*else*]
                        <b>-</b>
                    [*/if*]
                </td>
                <td><a href="javascript:delete_invite('[*$thisrow.invite_id*]');"><img
                            src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"></a>
                        [*if $thisrow.confirmed<2 && $thisrow.username*]
                        <a href="javascript:confirm_invite('[*$thisrow.invite_id*]');"><img
                                src="[*$theme_path*]engine_images/confirm.png"
                                alt="[*'usercp_invites_confirm'|lang*]"></a>
                        [*/if*]
                </td>
            </tr>
        [*/foreach*]
        [*if !$from_ajax*]
        </tbody>
    </table>
    <input type="button" value="[*'add'|lang*]" onclick="add_invite();">
[*/if*]
