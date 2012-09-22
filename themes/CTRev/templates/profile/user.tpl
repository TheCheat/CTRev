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
                    <hr>
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
                            <hr>
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
                    <hr>
                    <dl class="info_text" style="width: 350px;">
                        <dt>[*'users_registered'|lang*]</dt>
                        <dd style="width: 100px;">[*date time=$row.registered format="ymdhis"*]</dd>
                        <dt>[*'users_last_visited'|lang*]</dt>
                        <dd style="width: 100px;">[*date time=$row.last_visited format="ymdhis"*]<br>
                            ([*$row.last_visited|ge:'c'*] [*'users_ago'|lang*])</dd>
                        <dt>[*'users_birthday'|lang*]</dt>
                        <dd>
                            [*if $row.show_age*]
                                [*date time=$row.birthday format="ymd"*]
                            [*else*]
                                [*date time=$row.birthday format="md"*]
                            [*/if*]
                        </dd>
                        [*if $row.country_name*]
                            <dt>[*'users_country'|lang*]</dt>
                            <dd><img src="[*$BASEURL*][*'countries_folder'|config*]/[*$row.country_image*]"
                                     alt="[*$row.country_name*]" title="[*$row.country_name*]"></dd>

                        [*/if*] 
                        [*if $row.town*]
                            <dt>[*'users_town'|lang*]</dt>
                            <dd>[*$row.town*]</dd>
                        [*/if*] 
                        [*if $row.icq*]
                            <dt>[*'users_icq'|lang*]</dt>
                            <dd>[*$row.icq*]</dd>
                        [*/if*] 
                        [*if $row.skype*]
                            <dt>[*'users_skype'|lang*]</dt>
                            <dd>[*$row.skype*]</dd>
                        [*/if*] 
                        [*if $row.website*]
                            <dt>[*'users_website'|lang*]</dt>
                            <dd><a href="[*$row.website*]">[*$row.website*]</a></dd>
                        [*/if*]
                    </dl>
                </div>
            </div>
        </div>
        <div class="td center_col_profile">
            <div class="cornerText gray_color gray_border2">
                <script type="text/javascript">
                    function show_submenu_user() {
                        jQuery("#users_menus").slideToggle(100);
                        var src1 = '[*$theme_path|sl*]engine_images/arrow_bottom.png';
                        var src2 = '[*$theme_path|sl*]engine_images/arrow_top.png';
                        var $pic = jQuery("#users_img_arrow");
                        if ($pic.attr("src")==src1)
                            $pic.attr("src", src2);
                        else
                            $pic.attr("src", src1);
                    }
                    function submenu_user_select(obj, act, first) {
                        if (first)
                            prehide_ls();
                        jQuery.post('[*$BASEURL|sl*]index.php?module=user&act='+act+'&from_ajax=1', {'id':'[*$row.id|sl*]'}, function (data) {
                            jQuery("#users_title_of_menu").empty();
                            jQuery("#users_title_of_menu").append(jQuery(obj).text());
                            jQuery("#users_menus ul li").removeClass("selected");
                            jQuery(obj).parent("li").addClass("selected");
                            jQuery("#users_center_col_body").empty();
                            jQuery("#users_center_col_body").append(data);
                            onhovered_dd();
                        });
                    }
                </script>
                <div class="username"><span id="users_title_of_menu">[*'loading'|lang*]...</span>&nbsp;<a
                        href="javascript:show_submenu_user();"><img
                            src="[*$theme_path*]engine_images/arrow_bottom.png"
                            alt="[*'other'|lang*]" title="[*'other'|lang*]" id="users_img_arrow"></a></div>
                <hr>
                <div id="users_menus" style="display: none;">
                    <ul>
                        [*assign var="first" value="1"*] 
                        [*foreach from=$menu item=item*]
                            <li [*if ($first && !$act) || $act== $item*] class="selected"[*/if*]>
                            [*if !$first*]&nbsp;&bull;&nbsp;[*/if*]
                            [*if ($first && !$act) || $act== $item*]
                                <script type="text/javascript">
                                    jQuery(document).ready(function ($) {
                                        submenu_user_select('#users_first_item', 'show_[*$item*]', 1);
                                    });
                                </script>
                            [*/if*]
                            <a href="javascript:void(0);" onclick="submenu_user_select(this, 'show_[*$item*]');"
                               [*if ($first && !$act) || $act== $item*] 
                                   id="users_first_item"
                               [*/if*] class="text">[*"users_menu_link_$item"|lang*]</a></li>
                            [*assign var="first" value="0"*] 
                        [*/foreach*]
                </ul>
            </div>
            <div id="users_center_col_body">[*'loading'|lang*]...</div>
        </div>
        <br>
        <div class='cornerText gray_color gray_border'>
            <b>[*'users_comments_area'|lang*]</b>
            <hr><br>
            [*assign var='title4comments' value=$row.username*]
            [*display_comments resid=$row.id type='users'*]
        </div>
    </div>
</div>
</div>