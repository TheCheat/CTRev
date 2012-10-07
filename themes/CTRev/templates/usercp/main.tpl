<script language="javascript" type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".container_ucp").tabs({ fxFade: true, fxSpeed: 'fast', containerClass: 'white_place' });
        $(".container_ucp").show();
    });
    function delete_avatar() {
        jQuery.post("[*$BASEURL|sl*]index.php?"
    [*if $admin_file*]
                +"[*$admin_sid*]&id=[*'id'|user*]"
    [*else*]
                +"[*fk ajax=1*]"
    [*/if*]
                +"&module=usercp&act=clear_avatar&from_ajax=1", function (data) {
                if (data == "OK!")
                    alert("[*'success'|lang|sl*]!");
                else
                    alert("[*'error'|lang|sl*]!" + data);
            });
        }
        function save_ucp_settings(id) {
            make_tobbcode();
            var si = 'usercp_status_icon';
            status_icon(si, 'loading_white');
            var $form = jQuery("#"+id).serialize();
            jQuery.post("[*$BASEURL|sl*]index.php?"
    [*if $admin_file*]
                +"[*$admin_sid*]&id=[*'id'|user*]"
    [*else*]
                +"[*fk ajax=1*]"
    [*/if*]
                +"&module=usercp&act=index_ok&from_ajax=1", $form, function (data) {
                if (data == "OK!") {
                    status_icon(si, 'success');
                    $("#error_box").hide();
                    alert("[*'success'|lang|sl*]!");
                } else {
                    status_icon(si, 'error');
                    $("#error_box").show();
                    $("#error_box #error_message").empty();
                    $("#error_box #error_message").append(data);
                }
            });
        }
</script>
[*if $admin_file*]
    <input type="checkbox" name="item[]" 
           value="[*'id'|user*]" checked='checked'
           class="marked_users hidden">
    [*include file='admin/user/massact.tpl'*]
[*/if*]
<div id="error_box" class="hidden">[*message lang_var="Error!" die=0*]</div>
[*if $admin_file*]
    <div class='padding_left'>
        [*include file='admin/user/actions.tpl'*]
        <br>
    </div>
[*/if*]
<form action="javascript:save_ucp_settings('ucp_post_form');"
      id="ucp_post_form" method="post">
    <div class="container_ucp hidden" style="width: 700px;">
        <ul class="tabs-nav">
            <li class='tabs-selected'><a href="#fragment-ucp-1"><span><b>[*'usercp_tabs_subinfo'|lang*]</b></span></a></li>
            <li><a href="#fragment-ucp-2"><span><b>[*'usercp_tabs_persinfo'|lang*]</b></span></a></li>
            <li><a href="#fragment-ucp-3"><span><b>[*'usercp_tabs_settings'|lang*]</b></span></a></li>
            [*if $admin_file && 'system'|perm*]
                <li><a href="#fragment-ucp-5"><span><b>[*'usercp_tabs_perms'|lang*]</b></span></a></li>
            [*/if*]
            <li><a href="#fragment-ucp-4"><span><b>[*'usercp_tabs_avatar'|lang*]</b></span></a></li>
        </ul>
        <div id="fragment-ucp-1">
            <dl class="info_text">
                [*if !$admin_file*]
                    <dt>[*'usercp_area_your_passkey'|lang*]</dt>
                    <dd><b>[*'passkey'|user*]</b></dd>
                    <dt>[*'usercp_area_oldpassword'|lang*]</dt>
                    <dd><input type="password" name="oldpass" value=""></dd>
                    [*else*]
                    <dt>[*'register_area_username'|lang*]</dt>
                    <dd><input type="text" name="username" value="[*'username'|user*]"></dd>

                    [*if 'system'|perm*]
                        <dt>[*'usearch_group'|lang*]:</dt>
                        <dd>[*select_groups not_null=true current='group'|user*]</dd>
                    [*/if*]
                [*/if*]
                <dt>[*'usercp_area_newpassword'|lang*]</dt>
                <dd><input type="password" name="password" value="">[*passgen*]</dd>
                <dt>[*'usercp_area_passagain'|lang*]</dt>
                <dd><input type="password" name="passagain" value=""></dd>
                <dt>[*'register_area_email'|lang*]</dt>
                <dd><input type="text" name="email" value="[*'email'|user*]">
                    [*if 'confirm_email'|config*]
                        <br>
                        <font size="1">[*'usercp_after_changing_email'|lang*]</font>
                    [*/if*]
                </dd>
                <dt>[*'register_area_website'|lang*]</dt>
                <dd><input type="text" name="website" value="[*'website'|user*]"></dd>
                <dt>[*'register_area_icq'|lang*]</dt>
                <dd><input type="text" name="icq" value="[*'icq'|user*]"></dd>
                <dt>[*'register_area_skype'|lang*]</dt>
                <dd><input type="text" name="skype" value="[*'skype'|user*]"></dd>
                <dt>[*'usercp_area_sign'|lang*]</dt>
                <dd>[*input_form name="signature" text='signature'|user*]</dd>
            </dl>
        </div>
        <div id="fragment-ucp-2">
            <dl class="info_text">
                <dt>[*'register_area_snan'|lang*]</dt>
                <dd><input type="text" name="name_surname"
                           value="[*'name_surname'|user*]"></dd>
                <dt>[*'register_area_gender'|lang*]</dt>
                <dd><input type="radio" name="gender" value="m" 
                           [*if 'gender'|user=="m"*] 
                               checked="checked"
                           [*/if*]>[*'register_area_gender_m'|lang*]&nbsp;<input
                           type="radio" name="gender" value="f" 
                           [*if 'gender'|user=="f"*] 
                               checked="checked"
                           [*/if*]>[*'register_area_gender_f'|lang*]</dd>
                <dt>[*'register_area_birthday'|lang*]</dt>
                <dd>[*select_date name="birthday" fromnull=true time='birthday'|user*]</dd>
                <dt>[*'register_area_country'|lang*]</dt>
                <dd>[*select_countries current='country'|user*]</dd>
                <dt>[*'register_area_town'|lang*]</dt>
                <dd><input type="text" name="town" value="[*'town'|user*]"></dd>
            </dl>
        </div>
        <div id="fragment-ucp-3">
            <dl class="info_text">
                [*foreach from=$user_pk item='i'*]
                    [*assign var="host" value=$i[0]*]
                    [*if $i[2]*]
                        [*assign var="end" value=$i[2]*]
                    [*else*]
                        [*assign var="end" value='usercp_area_passkey_end'|lang*]
                    [*/if*]
                    <dt>[*'usercp_area_passkey'|pf:$i[0]:$i[1]:$end*]</dt>
                    [*assign var='apk' value='announce_pk'|user*]
                    <dd><input type='text' size='40' name='passkey[[*$i[0]|he*]]' value='[*$apk.$host*]'></dd>

                [*/foreach*]
                [*if !$admin_file*]
                    <dt>[*'usercp_area_theme'|lang*]</dt>
                    <dd>[*select_folder folder=$smarty.const.THEMES_PATH name='theme' current=$curtheme*]</dd>
                    <dt>[*'usercp_area_lang'|lang*]</dt>
                    <dd>[*select_folder folder=$smarty.const.LANGUAGES_PATH current=$curlang*]</dd>
                [*/if*]
                <dt>[*'usercp_area_mailer_interval'|lang*]</dt>
                <dd>[*select_mailer current='mailer_interval'|user*]</dd>
                <dt>[*'register_area_admin_email'|lang*]</dt>
                <dd><input type="checkbox" name="admin_email" value="1"
                           [*if 'admin_email'|user*] 
                               checked="checked"
                           [*/if*]></dd>
                <dt>[*'register_area_user_email'|lang*]</dt>
                <dd><input type="checkbox" name="user_email" value="1"
                           [*if 'user_email'|user*] 
                               checked="checked"
                           [*/if*]></dd>
                <dt>[*'register_area_show_age'|lang*]</dt>
                <dd><input type="checkbox" name="show_age" value="1"
                           [*if 'show_age'|user*] 
                               checked="checked"
                           [*/if*]></dd>
                    [*if 'behidden'|perm*]
                    <dt>[*'usercp_area_hidden'|lang*]</dt>
                    <dd><input type="checkbox" name="hidden" value="1"
                               [*if 'hidden'|user*] 
                                   checked="checked"
                               [*/if*]></dd>
                    [*/if*]
                <dt>[*'register_area_use_dst'|lang*]</dt>
                <dd><input type="checkbox" name="use_dst" value="1"
                           [*if 'dst'|user*] 
                               checked="checked"
                           [*/if*]></dd>
                <dt>[*'register_area_timezone'|lang*]</dt>
                <dd>[*select_gmt current='timezone'|user*]</dd>
            </dl>
        </div>
        <div id="fragment-ucp-4">
            <dl class="info_text">
                <dt>[*'usercp_area_nowava'|lang*]</dt>
                <dd>[*'avatar'|user|ua*]</dd>
                [*if 'allowed_avatar'|config|is:$smarty.const.ALLOWED_AVATAR_PC*]
                    [*if !$admin_file*]
                        <dt>[*'usercp_area_avafrompc'|lang*]</dt>
                        <dd>
                            <div id="fileQueue_ava"></div>
                            <input type="file" name="uploadify" id="uploadify_ava"><br>
                            <font size="1">[*'usercp_avatars_auto_save'|lang*]</font>
                        </dd>
                    [*/if*]
                [*/if*] 
                [*if 'allowed_avatar'|config|is:$smarty.const.ALLOWED_AVATAR_URL*]
                    <dt>[*'usercp_area_avafromurl'|lang*]</dt>
                    <dd><input type="text" name="avatar_url" size="60"
                               value="[*$current_avatar*]"></dd>
                    <dt>&nbsp;</dt>
                    <dd class='nobordered'>
                        [*if 'avatar'|user*]
                            <input type="button"onclick="delete_avatar();" value="[*'delete'|lang*]">
                        [*/if*]
                    </dd>
                [*/if*]
            </dl>
        </div>
        [*if $admin_file && 'system'|perm*]
            <div id="fragment-ucp-5">[*$perms*]</div>
        [*/if*]
        <center><div id="usercp_status_icon"></div>
            <input type="submit" value="[*'save'|lang*]">
        </center>
    </div>
</form>