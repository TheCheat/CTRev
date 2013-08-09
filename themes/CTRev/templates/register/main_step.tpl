[*if !'allowed_register'|config && !'allowed_invite'|config*] 
    [*message lang_var="register_disabled" type='error'*] 
[*else*]
    [*if !'allowed_register'|config && 'allowed_invite'|config*] 
        [*message lang_var="register_only_invite" type="info"*] 
    [*/if*]
    [*include file='register/script.tpl'*]
    <div class="cornerText gray_color" id="register_form">
        <fieldset><legend>[*'register_step_by_step'|lang*]</legend>
            <center>
                <div id="error_box" class="hidden">[*message lang_var="Error!" type='error'*]</div>
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
                                <dd>[*select_date name="birthday"*]</dd>
                                <dt>[*'register_area_gender'|lang*]</dt>
                                <dd><input type="radio" value="m" name="gender" checked="checked">[*'register_area_gender_m'|lang*]&nbsp;
                                    <input type="radio" value="f" name="gender">[*'register_area_gender_f'|lang*]</dd>
                            </dl>
                        </div>
                        <div class="register_tabs">
                            <dl class="info_text">
                                [*input_userfields type='register'*]
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
