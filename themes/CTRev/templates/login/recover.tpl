<script type="text/javascript">
    function recover(id) {
        var si ='recover_status_icon';
        status_icon(si, 'loading');
        var $form = jQuery("form#"+id).serialize();
        jQuery.post('index.php?module=login&act=recover&from_ajax=1', $form, function (data) {
            if (is_ok(data)) {
                status_icon(si, 'success');
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
          action="javascript:recover('recover_form');">
        <div style="width: 350px;">
            <div class="login_notice">
                [*message lang_var='recover_notice_for' type='info' title=0*]
            </div><br>
            <div class="cornerText styled_color">
                <dl class="info_text">
                    <dt class="white_text">[*'login'|lang*]:</dt>
                    <dd><input type="text" name="login"></dd>
                    <dt class="white_text">[*'recover_email'|lang*]:</dt>
                    <dd><input type="text" name="email"></dd>
                </dl>
                <input type="submit" value="[*'run'|lang*]"></div>
            <div class="si_upper">
                <div id="recover_status_icon" class="status_icon"></div>
            </div>
        </div>
    </form>
</center>