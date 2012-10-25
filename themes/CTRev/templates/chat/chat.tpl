[*if !$rows && !$prev*][*'chat_no_messages'|lang*][*/if*]
<div class='hidden chat_deleted_messages'>[*$deleted*]</div>
[*foreach from=$rows key='num' item='row'*]
    [*$row|@chat_mf*]
    <div class='chat_message[*if !$row.text*] hidden[*/if*]' id='chat_mess[*$row.id*]'>
        [*if $row.text*]
            <span class='chat_date'>[[*date time=$row.posted_time format='H:i:s'*]]</span>
            [*if !$row.spec*]
                <span class='chat_author'>[*$row.username|gcl:$row.group*]: </span>
            [*/if*]
            <span class='chat_message'>
                [*$row.text|ft:"SIMPLE":true*]
            </span>
            [*if check_owner($row.poster_id, 'edit_chat') || check_owner($row.poster_id, 'del_chat') || ('chat'|perm:2 && $row.username!='username'|user && $curuser && $row.username)*]
                <span class='chat_edit_row hidden'>[
                    [*if 'chat'|perm:2 && $row.username!='username'|user && $curuser && $row.username*]
                        <a href="javascript:chat_clear('/private([*$row.username|sl*]) ')"><img
                                src="[*$theme_path*]engine_images/key.png" height='12' 
                                alt="[*'chat_private_message'|lang*]" title="[*'chat_private_message'|lang*]">
                        </a>
                    [*/if*]
                    [*if check_owner($row.poster_id, 'edit_chat')*]
                        <a href="javascript:chat_edit('[*$row.id*]')"><img
                                src="[*$theme_path*]engine_images/edit.png" height='12' alt="[*'edit'|lang*]">
                        </a>
                    [*/if*]
                    [*if check_owner($row.poster_id, 'del_chat')*]
                        <a href="javascript:chat_delete('[*$row.id*]')"><img
                                src="[*$theme_path*]engine_images/delete.png" height='12' alt="[*'delete'|lang*]">
                        </a>
                    [*/if*]
                    ]
                </span>
            [*/if*]
        [*/if*]
    </div>
[*/foreach*]