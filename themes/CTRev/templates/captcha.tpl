<script type="text/javascript">
    $num = 0;
    $no_click = false;
    function reload_captcha() {
        if ($no_click)
            return;
        $num ++;
        jQuery("img#captcha").attr("src", '[*$BASEURL|sl*]index.php?module=registration&step=captcha&'+$num);
        $no_click=true;
        setTimeout("$no_click=false;", 1000);
    }
</script>
<div align="left"><b>[*'for_continue_please_enter_code'|lang*]</b></div>
<div align="left"><img src="[*$BASEURL*]index.php?module=registration&step=captcha"
                       alt="[*'captcha'|lang*]" title="[*'captcha'|lang*]"
                       onclick="reload_captcha();" id="captcha" class="clickable"><br>
    <input type="text" name="captcha_code"><br>
    <font size="1">[*'click_on_image_to_update'|lang*]</font>
</div>