<div class="cornerText gray_color gray_border2">
    <script type="text/javascript">
        function show_submenu_user() {
            jQuery("#users_menus").slideToggle(100);
            var src1 = '[*$theme_path|sl*]engine_images/arrow_bottom.png';
            var src2 = '[*$theme_path|sl*]engine_images/arrow_top.png';
            var $pic = jQuery("#users_img_arrow");
            if ($pic.attr("src") == src1)
                $pic.attr("src", src2);
            else
                $pic.attr("src", src1);
        }
        function submenu_user_select(obj, act, first) {
            if (first)
                prehide_ls();
            jQuery.post('index.php?module=user&act=' + act + '&from_ajax=1', {'id': '[*$row.id|sl*]'}, function(data) {
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
    <hr class='gray_border2'>
    <div id="users_menus" style="display: none;">
        <ul>
            [*assign var="first" value="1"*] 
            [*foreach from=$menu item=item*]
                <li [*if ($first && !$act) || $act== $item*] class="selected" [*/if*]>
                [*if !$first*]&nbsp;&bull;&nbsp;[*/if*]
                [*if ($first && !$act) || $act== $item*]
                    <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            submenu_user_select('#users_first_item', 'show_[*$item*]', 1);
                        });
                    </script>
                [*/if*]
                <a href="javascript:void(0);" onclick="submenu_user_select(this, 'show_[*$item*]');"
                   [*if ($first && !$act) || $act== $item*] 
                       id="users_first_item"
                   [*/if*] class="text">[*"users_menu_link_$item"|lang*]</a>
            </li>
            [*assign var="first" value="0"*] 
        [*/foreach*]
    </ul>
<br>
</div>
<div id="users_center_col_body">[*'loading'|lang*]...</div>
</div>