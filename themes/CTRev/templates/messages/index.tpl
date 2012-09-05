<script type="text/javascript">
    init_tabs("messajax_tabs", {"remoteClass": "tabs-container-remote", "containerClass": 'tabs_container', "saveLinks": true, "onShow": function (el) {
            if (jQuery(el).text() == '[*'pm_new_msgs'|lang|sl*]') {
                jQuery("#title_pmbox").empty();
                jQuery("#title_pmbox").append('[*'pm_sending_msg'|lang|sl*]');
            } else {
                jQuery("#title_pmbox").empty();
                jQuery("#title_pmbox").append('[*'pm_msgs'|lang|sl*]');
            }
        }
    });
</script>
[*include file="messages/main_funct.tpl"*]
<div class="send_pm tabs_container" align="left">
    <div class="white_color cornerText gray_border">
        <div class="title_module"><span id="title_pmbox">[*if $send*][*'pm_sending_msg'|lang*][*else*][*'pm_msgs'|lang*][*/if*]</span>
            ([*'pm_max'|lang*][*if 'pm_count'|perm*][*'pm_count'|perm*][*else*][*$smarty.const.unended*][*/if*])
            <div class="subtitle_module">
                <div class="messajax_tabs">
                    <ul class="tabs-nav">
                        <li [*if !$out && !$send*] class="tabs-selected"[*/if*]><a
                                href="[*$BASEURL*]/index.php?module=messages&amp;from_ajax=1"><span>[*'pm_input_msgs'|lang*]</span></a></li>
                        <li [*if $out && !$sended && !$send*] class="tabs-selected"[*/if*]><a
                                href="[*$BASEURL*]/index.php?module=messages&amp;from_ajax=1&out=1"><span>[*'pm_output_msgs'|lang*]</span></a></li>
                        <li [*if $out && $sended && !$send*] class="tabs-selected"[*/if*]><a
                                href="[*$BASEURL*]/index.php?module=messages&from_ajax=1&out=1&sended=1"><span>[*'pm_sended_msgs'|lang*]</span></a></li>
                        <li [*if $send*] class="tabs-selected"[*/if*]><a
                                href="[*$BASEURL*]/index.php?module=messages&amp;from_ajax=1&act=send&to=[*$to_pm*]&id=[*$resend_id*]"><span>[*'pm_new_msgs'|lang*]</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="tabs-container-remote"></div>
    </div>
</div>