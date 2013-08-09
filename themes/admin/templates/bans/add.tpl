<form method="post" name='banform' action='[*$admin_file|uamp*]&amp;act=save'>
    <div class="cornerText gray_color2">
        <fieldset><legend>[*'bans_title'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'bans_blocked_user'|lang*]</dt>
                <dd><input type="text" value="" name="username" >
                    [*if 'usearch'|perm*]
                        <br><a href="javascript:open_searchuwind('banform', 'username');">[*'search_usearch'|lang*]</a>
                    [*/if*]
                </dd>
                <dt>[*'bans_blocked_email'|lang*]</dt>
                <dd><input type="text" value="" name="email" ></dd>
                <dt>[*'bans_blocked_ip_f'|lang*]</dt>
                <dd><input type="text" value="" name="ip_f" ></dd>
                <dt>[*'bans_blocked_ip_t'|lang*]</dt>
                <dd><input type="text" value="" name="ip_t" ></dd>
                <dt>[*'bans_blocked_period'|lang*]</dt>
                <dd>[*select_periods*]</dd>
                <dt>[*'bans_block_reason'|lang*]</dt>
                <dd><textarea name="reason" rows='10' cols='50'></textarea></dd>
            </dl>
            <center><font size="1">[*'bans_email_patterns'|lang*]</font></center>
            <center><input type="submit" value="[*'add'|lang*]"></center>
        </fieldset>
    </div>
</form>