<div class="content">
    <div class="tr">
        <div class="td subcol_profile">
            <div class="col_profile">
                <div class="cornerText gray_color gray_border2">
                    <div class="username">
                        [*$row.username|gcl:$row.group*]
                        [*if $eadmin_file && ('system'|perm || $row.group|gval:'system')*]
                            <a href="[*$eadmin_file|uamp*]&amp;item=users&amp;module=users&amp;act=edit&amp;id=[*$row.id*]">
                                <img src="[*$theme_path*]engine_images/edit.png" alt="[*'edit'|lang*]" 
                                     title='[*'edit'|lang*]' width='12'>
                            </a>
                        [*/if*]
                    </div>
                    <hr class='gray_border2'>
                    <div class="padded_avatar content">
                        <div class="left_col_profile">[*$row.avatar|ua*]
                            [*assign var="uid" value=$row.id*]
                            <div align="center">[*include file="profile/karma.tpl"*]</div>
                        </div>
                        <div align="left">
                            [*if $row.name_surname*]
                                <b>[*$row.name_surname*]</b>
                                [*if $row.gender=="f"*]
                                    <img src="[*$theme_path*]engine_images/female.png" alt="[*'users_female'|lang*]">
                                [*else*]
                                    <img src="[*$theme_path*]engine_images/male.png" alt="[*'users_male'|lang*]">
                                [*/if*]
                                <br>
                            [*/if*]
                            [*if $row.show_age || 'not_allowed'|perm*]
                                <b>[*'users_age'|lang*]</b>[*$row.age*]<br>
                            [*/if*]
                            [*$row.birthday|zodiac_sign*]<br>
                            [*$row.group|gc*]
                            [*if 'viewip'|perm*]
                                <br>
                                ([*'ip'|lang*][*$row.ip|l2ip*])
                            [*/if*]
                            <hr class='gray_border2'>
                            [*if 'pm'|perm*] 
                                <b><a href="[*gen_link module='pm' to=$row.username*]">[*'users_send_pm'|lang*]</a></b><br>
                                [*/if*] 
                                [*if ($row.user_email || ('acp'|perm && $row.admin_email)) || 'not_allowed'|perm*] 
                                <b><a href="mailto:[*$row.email*]">[*'users_send_email'|lang*]</a></b>
                                [*/if*]
                        </div>
                    </div>
                    [*if ''|user*]
                        <div class='clear_both' align='center'>
                            [*include file='profile/addfriend.tpl'*]
                        </div>
                    [*/if*]
                </div>
                <br>
                <div class="cornerText gray_color gray_border2">
                    <div class="username"><b>[*'users_info'|lang*]</b></div>
                    <hr class='gray_border2'>
                    <dl class="info_text" style="width: 350px;">
                        <dt>[*'users_registered'|lang*]</dt>
                        <dd style="width: 100px;">[*date time=$row.registered format="ymdhis"*]</dd>
                        <dt>[*'users_last_visited'|lang*]</dt>
                        <dd style="width: 100px;">[*date time=$row.last_visited format="ymdhis"*]<br>
                            ([*$row.last_visited|ge:'c'*] [*'users_ago'|lang*])</dd>

                        [*if $row.birthday*]
                            <dt>[*'users_birthday'|lang*]</dt>
                            <dd>
                                [*if $row.show_age || 'not_allowed'|perm*]
                                    [*date time=$row.birthday format="ymd"*]
                                [*else*]
                                    [*date time=$row.birthday format="md"*]
                                [*/if*]
                            </dd>
                        [*/if*]
                        [*if $row.country_name*]
                            <dt>[*'users_country'|lang*]</dt>
                            <dd><img src="[*'countries_folder'|config*]/[*$row.country_image*]"
                                     alt="[*$row.country_name*]" title="[*$row.country_name*]"></dd>

                        [*/if*] 
                        [*if $row.town*]
                            <dt>[*'users_town'|lang*]</dt>
                            <dd>[*$row.town*]</dd>
                        [*/if*]
                        [*if $row.website*]
                            <dt>[*'users_website'|lang*]</dt>
                            <dd><a href="[*$row.website*]">[*$row.website*]</a></dd>
                            [*/if*]
                            [*display_userfields type='profile' user=$row*]
                    </dl>
                </div>
            </div>
        </div>
        <div class="td center_col_profile">
            [*include file='profile/usertabs.tpl'*]
            <br>
            <!--<div class='cornerText gray_color gray_border'>
                <b>[*'users_comments_area'|lang*]</b>
                <hr><br>-->
            [*assign var='title4comments' value=$row.username*]
            [*display_comments toid=$row.id type='users'*]
            <!--</div>-->
        </div>
    </div>
</div>