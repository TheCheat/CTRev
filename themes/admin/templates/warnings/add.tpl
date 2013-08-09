<form method="post" name='warnform' action='[*$admin_file|uamp*]&amp;act=save'>
    <div class="cornerText gray_color2">
        <fieldset><legend>[*'warnings_title'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'warnings_warned_user'|lang*]</dt>
                <dd><input type="text" value="" name="username" >
                    [*if 'usearch'|perm*]
                        <br><a href="javascript:open_searchuwind('warnform', 'username');">[*'search_usearch'|lang*]</a>
                    [*/if*]
                </dd>
                <dt>[*'warnings_warn_reason'|lang*]</dt>
                <dd><textarea name="reason" rows='10' cols='50'></textarea></dd>
                <dt>[*'warnings_other'|lang*]</dt>
                <dd><input type="checkbox" name="notify" value="1" checked="checked">&nbsp;[*'warnings_notify_user'|lang*]</dd>
            </dl>
            <center><input type="submit" value="[*'add'|lang*]"></center>
        </fieldset>
    </div>
</form>