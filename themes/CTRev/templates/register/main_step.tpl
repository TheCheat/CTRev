[*if !'allowed_register'|config && !'allowed_invite'|config*] 
    [*message lang_var="register_disabled" die=0*] 
[*else*]
    [*if !'allowed_register'|config && 'allowed_invite'|config*] 
        [*message lang_var="register_only_invite" type="info"*] 
    [*/if*]
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $next_register_tab = $("div.register_form div.register_tabs").length - 1;
        });
    function check_from(form, type) {
        $("#error_box").hide();
        var si = "register_status_icon";
        status_icon(si, 'loading_white');
        var $data = jQuery(form).serialize();
        $data = $data+"&from_ajax=1&step=";
        if (type == 'next')
            jQuery.post('[*gen_link slashes=true module="registration" act="main"*]', $data+($cur_tab+1), function (data) {
                if (data == "OK!") {
                    status_icon(si, 'success');
                    tabs_sets(type);
                } else {
                    status_icon(si, 'error');
                    $("#error_box").show();
                    $("#error_box #error_message").empty();
                    $("#error_box #error_message").append(data);
                }
        });
        else if (type == 'back') {
            status_icon(si);
            tabs_sets(type);
        } else if (type == 'end') {
            jQuery('#register_next').attr("disabled", "disabled");
            //jQuery('#register_back').attr("disabled", "disabled");
            jQuery('#register_end').attr("disabled", "disabled");
            jQuery('.progress_bar').hide();
            jQuery.post('[*gen_link slashes=true module="registration" act="main"*]', $data+"last", function (data) {
                if (data == "OK!") {
                    status_icon(si, 'success');
                    var $obj = jQuery('.register_tabs');
                    $obj.hide();
                    $obj.eq($next_register_tab).show();
                    setTimeout("window.location = baseurl", 5000);
                } else {
                    status_icon(si, 'error');
                    $("#error_box").show();
                    $("#error_box #error_message").empty();
                    $("#error_box #error_message").append(data);
                }
            });
        }
    }
    function tabs_sets(type) {
        var $obj = jQuery('.register_tabs');
        jQuery('#register_end').attr("disabled", "disabled");
        jQuery('.progress_bar').show();
        if (type == 'back') {
            if ($obj.eq($cur_tab - 1).length) {
                $obj.hide();
                $cur_tab = $cur_tab - 1;
                $obj.eq($cur_tab).show();
            }
        } else {
            if ($obj.eq($cur_tab + 1).length) {
                $obj.hide();
                $cur_tab = $cur_tab + 1;
                $obj.eq($cur_tab).show();
            }
        }
        if ($obj.eq($cur_tab - 1).length && ($cur_tab - 1) >= 0) {
            jQuery('#register_back').removeAttr("disabled");
        } else {
            jQuery('#register_back').attr("disabled", "disabled");
        }
        if ($obj.eq($cur_tab + 1).length && ($cur_tab + 1) <= $next_register_tab - 1) {
            jQuery('#register_next').removeAttr("disabled");
        } else {
            jQuery('#register_next').attr("disabled", "disabled");
        }
        if ($cur_tab == $next_register_tab - 1) {
            jQuery('#register_next').attr("disabled", "disabled");
            jQuery('#register_end').removeAttr("disabled");
        }
        var $progress = jQuery(".progress_bar .progress");
        var $percent = parseInt(($cur_tab / ($next_register_tab - 1)) * 100) + "%";
        $progress.width($percent);
        $progress.children(".percent").empty();
        $progress.children(".percent").append($percent);
        onhovered_dd();
    }
    jQuery(document).ready(function () {
        $cur_tab = -1;
        tabs_sets('next');
    });
    </script>
    <div class="cornerText gray_color" id="register_form">
        <fieldset><legend>[*'register_step_by_step'|lang*]</legend>
            <center>
                <div id="error_box" class="hidden">[*message lang_var="Error!" die=0*]</div>
                <div class="progress_bar" align="left">
                    <div class="progress" style="width: 0%;">
                        <div class="percent">0%</div>
                    </div>
                </div>
                <br>
                <div class="white_place register_form" align="left">
                    <form action="[*gen_link module='registration' act='main'*]"
                          method="post" id="main_form"><input type="hidden" name="num" value="0"><input
                            type="hidden" name="to_check" value="1"> <input type="hidden"
                            name="next_act"
                            value="[*gen_link module='registration' act='contact'*]">
                        <div class="register_tabs">
                            <dl class="info_text">
                                <dt>[*'register_area_username'|lang*]</dt>
                                <dd><input type="text" value="" name="username"></dd>
                                <dt>[*'register_area_password'|lang*]</dt>
                                <dd><input type="password" value="" name="password">[*passgen*]</dd>
                                <dt>[*'register_area_passagain'|lang*]</dt>
                                <dd><input type="password" value="" name="passagain"></dd>
                                <dt>[*'register_area_email'|lang*]</dt>
                                <dd><input type="text" value="" name="email"></dd>
                                <dt>[*'register_area_captcha'|lang*]</dt>
                                <dd>[*include file="captcha.tpl"*]</dd>
                            </dl>
                        </div>
                        <div class="register_tabs">
                            <dl class="info_text">
                                <dt>[*'register_area_snan'|lang*]</dt>
                                <dd><input type="text" value="" name="name"></dd>
                                <dt>[*'register_area_birthday'|lang*]</dt>
                                <dd>[*select_date name="birthday" fromnull=true*]</dd>
                                <dt>[*'register_area_gender'|lang*]</dt>
                                <dd><input type="radio" value="m" name="gender" checked="checked">[*'register_area_gender_m'|lang*]&nbsp;
                                    <input type="radio" value="f" name="gender">[*'register_area_gender_f'|lang*]</dd>
                            </dl>
                        </div>
                        <div class="register_tabs">
                            <dl class="info_text">
                                <dt>[*'register_area_country'|lang*]</dt>
                                <dd>[*select_countries*]</dd>
                                <dt>[*'register_area_town'|lang*]</dt>
                                <dd><input type="text" value="" name="town"></dd>
                                <dt>[*'register_area_website'|lang*]</dt>
                                <dd><input type="text" value="" name="website"></dd>
                                <dt>[*'register_area_icq'|lang*]</dt>
                                <dd><input type="text" value="" name="icq"></dd>
                                <dt>[*'register_area_skype'|lang*]</dt>
                                <dd><input type="text" value="" name="skype"></dd>
                                <dt>[*'register_area_use_dst'|lang*]</dt>
                                <dd><input type="checkbox" name="use_dst" value="1"></dd>
                                <dt>[*'register_area_timezone'|lang*]</dt>
                                <dd>[*select_gmt*]</dd>
                            </dl>
                        </div>
                        <div class="register_tabs">
                            <dl class="info_text">
                                [*if 'allowed_invite'|config*]
                                    <dt>[*'register_area_invite_code'|lang*][*if !'allowed_register'|config && 'allowed_invite'|config*]*[*/if*]:</dt>
                                    <dd><input type="text" value="" name="invite" size="32" maxlength="32"></dd>

                                [*/if*]
                                <dt>[*'register_area_show_age'|lang*]</dt>
                                <dd><input type="checkbox" value="1" name="show_age" checked="checked"></dd>
                                <dt>[*'register_area_admin_email'|lang*]</dt>
                                <dd><input type="checkbox" value="1" name="admin_email"
                                           checked="checked"></dd>
                                <dt>[*'register_area_user_email'|lang*]</dt>
                                <dd><input type="checkbox" value="1" name="user_email"></dd>
                            </dl>
                        </div>
                        <div class="register_tabs">
                            <center><b>[*'register_successfull_data'|lang*]</b></center>
                        </div>
                        <div class="register_tabs">
                            <center><b>[*'register_successfull'|lang*]
                                    [*if 'confirm_email'|config*]
                                        [*'register_successfull_email_confirm'|lang*]
                                    [*elseif 'confirm_admin'|config*]
                                        [*'register_successfull_admin_confirm'|lang*]
                                    [*/if*]</b></center>
                        </div>
                        <div align="right">
                            <div class='si_downer'>
                                <div class="status_icon" id="register_status_icon"></div>
                            </div>
                            <input type="button" id="register_back" value="[*'register_back'|lang*]"
                                   onclick="check_from('#main_form', 'back');" disabled="disabled">&nbsp;<input
                                   type="button" id="register_next" value="[*'register_next'|lang*]"
                                   onclick="check_from('#main_form', 'next');" disabled="disabled">&nbsp;<input
                                   type="button" id="register_end" class="styled_button_big"
                                   value="[*'register_end'|lang*]"
                                   onclick="check_from('#main_form', 'end');" disabled="disabled"></div>
                    </form>
                </div>
            </center>
        </fieldset>
    </div>
[*/if*]
