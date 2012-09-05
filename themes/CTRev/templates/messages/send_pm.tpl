<script type="text/javascript">
    function post_form_pm($name) {
        make_tobbcode();
        var $obj = jQuery('form[name="'+$name+'"]');
        var $post = $obj.serialize();
        var si = 'pm_status_icon';
        status_icon(si, 'loading_white');
        jQuery.post("[*$BASEURL|sl*]index.php?[*fk ajax=1*]module=messages&act=send_ok&from_ajax=1", $post, function (data) {
            if (data != "OK!") {
                status_icon(si, 'error');
                alert(data);
            } else {
                status_icon(si, 'success');
                alert('[*'success'|lang|sl*]');
            }
        });
    }</script>
<form action="javascript:post_form_pm('form_send_pm');"
      name="form_send_pm" method="post">
    <fieldset class="receivers_pm nobordered"><legend>[*'pm_receivers'|lang*]</legend>
        <div class="gray_color cornerText gray_border">
            <div class="receivers_left">
                <textarea cols="35" rows="3" name="to_usernames">[*$to_pm*]</textarea>
            </div>
            [*if 'masspm'|perm*]
                <div class="receivers_right">[*select_groups multi=1 name="to_groups[]"*]</div>
            [*/if*]
            <div class="clear_both"><b><font size="1">[*'pm_receivers_notice'|lang*]</font></b></div>
        </div>
    </fieldset>
    <dl class="info_text">
        <dt>[*'pm_title'|lang*]</dt>
        <dd><input type="text" name="title" style="width: 390px;"
                   value="[*if $row.subject*][*'pm_re'|lang*][*$row.subject*][*/if*]"></dd>
        <dt>[*'pm_body'|lang*]</dt>
        <dd>[*input_form name="body" text=$row.text*]</dd>
        <dt>&nbsp;</dt>
        <dd class="nobordered"><div class="status_icon" id="pm_status_icon"></div>
            <input type="submit" value="[*'pm_send'|lang*]"></dd>
    </dl>
</form>