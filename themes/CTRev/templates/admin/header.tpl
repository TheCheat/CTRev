<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>[*'site_title'|config*][*$smarty.config.title_separator*][*'admin_cp_page'|lang*]</title>
        <link rel="stylesheet" href="[*$theme_path*]css/admin/style.admin.css"
              type="text/css">
        [*include file="initializer.tpl"*]
    </head>
    <body>
        <div class="full_container">
            <div>
                <div class="top_left"></div>
                <div class="top_center">
                </div>
                <div class="top_right"></div>
            </div>
            <div class="content_main">
                <div class="lt_corner"></div>
                <div class="rt_corner"></div>
                <div class="top_shadow"></div>
                <div class="left_shadow">
                    <div class="right_shadow">
                        <div class="body_div">
                            <div id="top-menu" class="transperent-container">
                                <a href="[*$BASEURL*]" class="top_home_image">
                                    <img src="[*$theme_path*]images/menu/home.png"
                                         alt="[*'to_index_page'|lang*]" title="[*'to_index_page'|lang*]">
                                </a>
                                <ul class="tabs-nav">
                                    [*assign var="after_lang_item" value="_item"*]
                                    [*foreach from=$imods key='item' item='null'*]
                                        <li [*if $item==$selected_item*] class="tabs-selected"[*/if*]>
                                            <a href="[*$eadmin_file|uamp*]&amp;item=[*$item*]"><span>[*"top_menu_$item$after_lang_item"|lang*]</span></a>
                                        </li>
                                    [*/foreach*]
                                </ul>
                                <div class="tabs-container">
                                    <div class="content">
                                        <div class="tr">
                                            <div class="td left_column">
                                                [*if is_array($imods.$selected_item)*]
                                                    <ul class="left_menu">
                                                        [*foreach from=$imods.$selected_item item='cat' key='cname'*]
                                                            <li>
                                                                <span>[*"menu_cats_$cname"|lang*]</span>
                                                                <hr>
                                                                <ul>
                                                                    [*foreach from=$cat item='imod_link' key='imod'*]
                                                                        <li [*if $selected_imod== $imod*] class="selected"[*/if*]>
                                                                            <a href="[*$iadmin_file|uamp*]&amp;[*$imod_link|uamp*]">[*"menu_imods_$imod"|lang*]</a>
                                                                        </li>
                                                                    [*/foreach*]
                                                                </ul>
                                                            </li>
                                                        [*/foreach*]
                                                    </ul>
                                                [*/if*]
                                            </div>
                                            <div class="td center_column">