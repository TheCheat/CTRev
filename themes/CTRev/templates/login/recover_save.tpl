<script type="text/javascript">
    function recover_save(id) {
        var si ='recover_status_icon';
        status_icon(si, 'loading');
        var $form = jQuery("form#"+id).serialize();
        jQuery.post('index.php?module=login&act=recover_save&from_ajax=1', $form, function (data) {
            if (is_ok(data)) {
                status_icon(si, 'success');
                setTimeout("window.location = ''", 1000);
                //alert('[*'success'|lang|sl*]!');
            } else {
                status_icon(si, 'error');
                alert('[*'error'|lang|sl*]: '+data);
            }
        });
    }
</script>
<center>
    <form method="post" id="recover_form"
          action="javascript:recover_save('recover_form');">
        <input type="hidden" name="key" value="[*$key*]">
        <input type="hidden" name="email" value="[*$email*]">
        <div style="width: 350px;">
            <div class="login_notice">
                [*message lang_var='recover_notice_for_save' type='info' title=0*]
            </div><br>
            <div class="cornerText styled_color">
                <dl class="info_text">
                    <dt class="white_text">[*'recover_password'|lang*]:</dt>
                    <dd><input type="password" name="password"></dd>
                    <dt class="white_text">[*'recover_passagain'|lang*]:</dt>
                    <dd><input type="password" name="passagain"></dd>
                </dl>
                <input type="submit" value="[*'run'|lang*]"></div>
            <div class="si_upper">
                <div id="recover_status_icon" class="status_icon"></div>
            </div>
        </div>
    </form>
</center>