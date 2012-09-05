[*if $row.id!='id'|user*]
    [*include file='usercp/scripts.tpl'*]
    <div id='add_to_friend'[*if $row.zebra_id*] class='hidden'[*/if*]>
        <a href="javascript:add_friend('[*$row.username|sl*]')">[*'users_add_to_friends'|lang*]</a>
    </div>
    <div id='remove_from_friend'[*if !$row.zebra_id*] class='hidden'[*/if*]>
        <a id='send_to_block'[*if $row.zebra_type=='f'*] class='hidden'[*/if*]
           href="javascript:change_type_friend('[*$row.zebra_id*]', 'b');">
            [*'usercp_friends_in_f'|lang*]
        </a>
        <a id='send_to_friend'[*if $row.zebra_type=='b'*] class='hidden'[*/if*]
           href="javascript:change_type_friend('[*$row.zebra_id*]', 'f');">
            [*'usercp_friends_in_b'|lang*]
        </a><br>
        <a href="javascript:delete_friend('[*$row.zebra_id*]');">[*'users_remove_from_friends'|lang*]</a>
    </div>
[*/if*]