<!--[*$content*]-->
[*if 'comment'|perm || 'content'|perm || 'profile'|perm*]
    <script type="text/javascript">
        init_tabs("down_tabs", {
            "remoteClass": "tabs-container-remote-down", "containerClass": 'tabs-container-down', "saveLinks": true});
    </script>
    <div class="cb_block">
        <div class="down_tabs cbb_tabs">
            <ul class="tabs-nav">      
                [*if 'comment'|perm*]
                    <li><a href="index.php?module=downm&amp;act=comments&amp;from_ajax=1"><span>[*'downm_menu_item_comments'|lang*]</span></a></li>
                [*/if*]
                [*if 'content'|perm*]
                    <li><a href="index.php?module=downm&amp;act=content&amp;from_ajax=1"><span>[*'downm_menu_item_content'|lang*]</span></a></li>
                [*/if*]
                [*if 'profile'|perm*]
                    <li class="tabs-selected"><a href="index.php?module=downm&amp;act=online&amp;from_ajax=1"><span>[*'downm_menu_item_online'|lang*]</span></a></li>
                [*/if*]
            </ul>
        </div>
        <div class="cbb_header">
            <div class="cbb_hl"></div>
            <div class="cbb_hc"></div>
            <div class="cbb_hr"></div>
        </div>
        <div class="cbb_content">
            <div class="cbb_cl">
                <div class="cbb_cr">
                    <div class="cbb_cc">
                        <div class="tabs-container-remote-down"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cbb_footer">
            <div class="cbb_fl"></div>
            <div class="cbb_fc"></div>
            <div class="cbb_fr"></div>
        </div>
    </div>
[*/if*]