<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <base href="[*$baseurl*]">
        <title>[*'site_title'|config*]
            [*if $overall_title*]
                [*$smarty.config.title_separator*]
                [*$overall_title*]
            [*/if*]
        </title>
        <link rel="stylesheet" href="[*$theme_path*]css/style.css"
              type="text/css">
        [*if $color_path*]
            <link rel="stylesheet" href="[*$theme_path*]css/[*$color_path*]color.css"
                  type="text/css">
        [*/if*]
        <link rel="alternate" type="application/rss+xml" title="RSS Feed"
              href="[*gen_link module='content' act='rss'*]">
        <link rel="alternate" type="application/atom+xml" title="Atom Feed"
              href="[*gen_link module='content' act='atom'*]">
        [*include file="initializer.tpl"*]
        <script type="text/javascript" src="js/jquery.easing.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var time = 60; // 60 sec. to refresh.
                get_index_msgs();
                setInterval('get_index_msgs();', time * 1000);
                pre_search(jQuery("input[name='main_search']"));
            });
            function search_redirect() {
                var str = "[*gen_link slashes=1 module='search' query='$1'*]";
                var t = jQuery('input[name="main_search"]');
                if (t.val() == t.attr('data-acvalue'))
                    t.val('');
                if (!t.val())
                    return;
                str = str.replace('$1', t.val());
                window.location = str;
            }</script>
            [*'my_meta'|config*]

        [*if $overall_keywords*]
            <meta name="keywords" content="[*$overall_keywords*]">
        [*/if*]
        [*if $overall_descr*]
            <meta name="description" content="[*$overall_descr*]">
        [*/if*]
    </head>
    <body>
        <div class='content_main'>
            <!-- Шапка -->
            <div class='header_all'>
                <div class='header_main'>
                    <div class='logo_image'></div>
                    <div class='logo_ctrev'></div>
                    <div class='header_left'></div>
                    <div class='header_loginbox'>
                        <!-- Логинбокс -->
                        [*if !$curuser*]
                            <div class='loginbox_unlogged'>
                                <form method="post" id="small_login_form"
                                      action="javascript:login('index.php?module=login&amp;from_ajax=1', '#small_login_form', '#small_status_icon');">
                                    <input type='text' class='styled_login autoclear_fields' 
                                           name="login" value="[*'login'|lang*]" id="small_login">
                                    <input type='password' class='styled_password autoclear_fields' 
                                           name="password" value="[*'password'|lang*]" id="small_password">
                                    <a href="javascript:void(0);" onclick='jQuery(this).parent().submit();' class="styled_lbutton">&nbsp;</a>
                                    <div class="status_icon" id="small_status_icon"></div>
                                    <div class="loginbox_undertext">
                                        <a href="[*gen_link module='registration'*]" title="[*'registration'|lang*]">[*'registration'|lang*]</a><span>&nbsp;|&nbsp;</span><a
                                            href="[*gen_link module='login' act='recover'*]" title="[*'login_recover_password'|lang*]">[*'login_recover_password'|lang*]</a>
                                    </div>
                                </form>
                            </div>
                        [*else*]
                            <div class="loginbox_logged">
                                <div align="right">[*'logged_hello_user'|lang*][*'curuser'|gcl*]</div>
                                <div style="clear: both;">
                                    <div style="position: relative; float: left;" align="left"><a
                                            class="white_link" href="[*gen_link module='usercp'*]">[*'links_loginbox_cp'|lang*]</a><br>
                                        [*if 'pm'|perm && 'messages'|mstate*]
                                            <a class="white_link"
                                               href="[*gen_link module='pm'*]">[*'links_loginbox_pm'|lang*]</a><span
                                               id="ajax_index_msgs"></span><br>
                                            [*/if*] 
                                        <a class="white_link"
                                           href="[*gen_link module='users' user='username'|user act='stats'*]">[*'links_loginbox_stats'|lang*]</a><br>
                                        [*if 'mailer_on'|config*]
                                            <a class="white_link" href="[*gen_link module='usercp' act='mailer'*]">[*'links_loginbox_mailer'|lang*]</a><br>
                                        [*/if*]
                                    </div>
                                    [*if 'content'|perm*]
                                        <div align="right">
                                            <a class="white_link"
                                               href="[*gen_link module='users' user='username'|user act='content'*]">[*'links_loginbox_mycontent'|lang*]</a><br>
                                            <a class="white_link"
                                               href="[*gen_link module='usercp' act='bookmarks'*]">[*'links_loginbox_mybookmarks'|lang*]</a><br>
                                            <a class="white_link" href="[*gen_link module='content' act='new'*]">[*'links_loginbox_newcontent'|lang*]</a><br>
                                            [*if 'content'|perm:2*]
                                                <a class="white_link"
                                                   href="[*gen_link module='content' act='add'*]">[*'links_loginbox_addcontent'|lang*]</a><br>
                                            [*/if*]
                                        </div>
                                    [*/if*]
                                </div>
                                <a href="[*gen_link module='login' act='out'*]" class="styled_lobutton">&nbsp;</a>
                            </div>
                        [*/if*]
                    </div>
                </div>
                <div class='header_menu'>
                    <ul class='header_menu_buttons'>
                        <li><a href="index.php"><img src="[*$theme_path*]images/menu/home.png" alt="[*'links_index'|lang*]">&nbsp;[*'links_index'|lang*]</a></li>
                                [*if 'acp'|perm*]
                            <li><a href="admincp.php"><img
                                        src="[*$theme_path*]images/menu/acp.png" alt="[*'links_admincp'|lang*]">&nbsp;[*'links_admincp'|lang*]</a></li>
                                [*/if*] 
                                [*if 'content'|perm*]
                            <li><a href="[*gen_link module='content'*]"><img
                                        src="[*$theme_path*]images/menu/content.png" alt="[*'links_content'|lang*]">&nbsp;[*'links_content'|lang*]</a></li>
                            <li><a href="[*gen_link module='content' act='rss'*]"><img
                                        src="[*$theme_path*]engine_images/rss-feed.png"
                                        alt="[*'links_rss_content'|lang*]">&nbsp;[*'links_rss_content'|lang*]</a></li>
                                    [*if 'search_module'|mstate*]
                                <li><a href="[*gen_link module='search'*]"><img
                                            src="[*$theme_path*]images/menu/search.png" alt="[*'search'|lang*]">&nbsp;[*'search'|lang*]</a></li>
                                    [*/if*] 
                                [*/if*] 
                                [*if $curuser*]
                            <li><a href="[*gen_link module='login' act='out'*]"><img
                                        src="[*$theme_path*]images/menu/logout.png"
                                        alt="[*'links_logout'|lang*]">&nbsp;[*'links_logout'|lang*]</a></li>
                                [*else*]
                            <li><a href="[*gen_link module='login'*]"><img
                                        src="[*$theme_path*]images/menu/login.png" alt="[*'links_login'|lang*]">&nbsp;[*'links_login'|lang*]</a></li>
                            <li><a href="[*gen_link module='registration'*]"><img
                                        src="[*$theme_path*]images/menu/register.png"
                                        alt="[*'links_register'|lang*]">&nbsp;[*'links_register'|lang*]</a></li>
                                [*/if*]
                    </ul>
                    <input type='text' class='header_search autoclear_fields' 
                           name="main_search" value="[*'search'|lang*]">
                    <a href="javascript:search_redirect();" class="search_button" title="[*'search'|lang*]">&nbsp;</a>
                </div>
            </div>
            <!-- Блоки -->
            <div class='content_container'>
                <div class='content_blocks'>
                    <div class='content_blocks_tr'>
                        <div class='content_left'>
                            <div class='content_binner'>
                                <!-- Левый блок -->
                                [*display_blocks pos="left"*]
                            </div>
                        </div>
                        <div class='content_center'>
                            <div class='content_binner'>
                                <!-- Центральный блок -->
                                [*if !'site_online'|config*] 
                                    [*message lang_var="now_site_offline" type="info"*] 
                                [*/if*]
                                <noscript>
                                    <div class="JS_notice">[*'please_switch_on_javascript'|lang*]</div>
                                </noscript>
                                [*if $unconfirmed_user*] 
                                    [*message lang_var="sorry_but_you_unconfirmed" type="info"*] 
                                [*/if*]
                                [*display_blocks pos="top"*]