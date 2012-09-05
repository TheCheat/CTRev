[*if !$from_profile && !$from_add*]
    [*include file='usercp/scripts.tpl'*]
    <div class="cornerText white_place" id="add_place_friends">
        <div align='center'>
            <div class='br'></div>
            <b>[*'add'|lang*]:&nbsp;&nbsp;</b>
            <input type="text" name="username" value="[*'search'|lang*]..." class='autoclear_fields' size="40">
            <a href="javascript:void(0);" onclick="add_friend(jQuery(this).prev().val());">
                <img src="[*$theme_path*]engine_images/confirm.png" alt="[*'add'|lang*]"></a>
        </div>
    [*/if*]
    [*if $row*]
        <div class='br'></div><br>
        [*foreach from=$row key=num item=arr*]
            <div class="content usercp_friends_content[*if $num*] usercp_friends_content_bordered[*/if*]" id="ucp_friend_[*$arr.id*]">
                <div class="float_left" style="padding-right: 5px;">[*$arr.avatar|ua*]</div>
                <b>[*'usercp_friends_username'|lang*]</b>[*$arr.username|gcl:$arr.group*]
                - <span id="ucp_friend_type_[*$arr.id*]">
                    [*if $arr.type == "f"*]
                        <font color="green"><b>[*'usercp_friends_f'|lang*]</b></font>
                    [*else*]
                        <font color="red"><b>[*'usercp_friends_b'|lang*]</b></font>
                    [*/if*]
                </span><br>
                <b>[*'usercp_friends_group'|lang*]</b>[*$arr.group|gc*]<br>
                <b>[*'usercp_friends_gender'|lang*]</b>
                [*if $row.gender=="f"*]
                    <img src="[*$theme_path*]engine_images/female.png" alt="[*'usercp_friends_female'|lang*]">
                [*else*]
                    <img src="[*$theme_path*]engine_images/male.png" alt="[*'usercp_friends_male'|lang*]">
                [*/if*]
                <br>
                <b>[*'usercp_friends_registered'|lang*]</b>[*date time=$arr.registered*]
                <br>
                <a href="[*gen_link module='users' act='friends' user=$arr.username*]">[*'usercp_friends_user'|lang*]
                    [*$arr.group|gc:$arr.username*]</a>
                    [*if !$from_profile*]
                    <br>
                    <a href="javascript:change_type_friend('[*$arr.id*]', '[*$arr.type*]');">
                        [*if $arr.type == "b"*]
                            [*'usercp_friends_in_f'|lang*]
                        [*else*]
                            [*'usercp_friends_in_b'|lang*]
                        [*/if*]
                    </a> 
                    | 
                    <a href="javascript:delete_friend('[*$arr.id*]');">
                        [*if $arr.type == "f"*]
                            [*'usercp_friends_delete_f'|lang*]
                        [*else*]
                            [*'usercp_friends_delete_b'|lang*]
                        [*/if*]
                    </a>
                [*/if*]
            </div>
        [*/foreach*]
    [*else*]
        <div id="error_place_friends">[*message lang_var="usercp_no_friends" type="info"*]</div>
    [*/if*] 
    [*if !$from_profile && !$from_add*]
    </div>
[*/if*]
