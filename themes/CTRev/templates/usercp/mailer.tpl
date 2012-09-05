[*if !$mailer_res*]
    [*message type="info" lang_var="usercp_mailer_no"*]
[*else*]
    <script type="text/javascript">
        init_tablesorter();
        select_mailer = '[*select_mailer|sl*]';
    </script>
    [*include file='usercp/scripts.tpl'*]
    <table class="tablesorter">
        <thead>
            <tr>
                <th>[*'usercp_mailer_resource'|lang*]</th>
                <th>[*'usercp_mailer_interval'|lang*]</th>
                <th class="js_nosort">[*'actions'|lang*]</th>
            </tr>
        </thead>
        <tbody>
            [*foreach from=$mailer_res item=row*]
                [*assign var="slv" value=$intervals[$row.interval]*]
                <tr>
                    <td>[*$row.id|get_mailer_title:$row.type*]</td>
                    <td><span class="clickable" ondblclick="mchange_type(this, '[*$row.id*]', '[*$row.type|sl*]');">
                            [*"usercp_mailer_interval_every_$slv"|lang*]
                        </span></td>
                    <td><a href="javascript:void(0);" onclick="delete_mailer(this, '[*$row.id*]', '[*$row.type|sl*]');">
                            <img src="[*$theme_path*]engine_images/delete.png"
                                 alt="[*'delete'|lang*]" title="[*'delete'|lang*]"></a></td>
                </tr>
            [*/foreach*]
        </tbody>
    </table>
    <div align="left"><font size="1">[*'usercp_mailer_notice'|lang*]</font></div>
[*/if*]