<center>
    <div class="login_notice">
        <div>
            [*if $referer*]
                <font color='red'><b>[*'login_access_none'|lang*]</b></font><br>
            [*/if*]
            [*'login_notice'|lang*]
        </div>
    </div><br>
    <form method="post" id="big_login_form"
          action="javascript:login('index.php?module=login&amp;from_ajax=1', '#big_login_form', '#big_status_icon', '[*$referer|sl*]');">
        <div style="width: 350px;">
            <div class="cornerText styled_color">
                <input class="styled_login autoclear_fields" type="text"
                       name="login" value="[*'login'|lang*]" id="big_login"><br>
                <input class="styled_password autoclear_fields" type="password"
                       name="password" value="[*'password'|lang*]" id="big_password">
                <div class="status_icon" id="big_status_icon"></div>
                <div class="loginbox_undertext">
                    <a href="[*gen_link module='registration'*]" title="[*'registration'|lang*]">[*'registration'|lang*]</a><span>&nbsp;|&nbsp;</span><a
                        href="[*gen_link module='login' act='recover'*]" title="[*'login_recover_password'|lang*]">[*'login_recover_password'|lang*]</a>
                </div>
                <input type="checkbox" value="1" name="short_sess">&nbsp;[*'login_short_sess'|lang*]<br>
                <input type="submit" value="[*'login_page'|lang*]">
            </div>
        </div>
    </form>
</center>