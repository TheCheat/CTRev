[*if $res*]
    <script type="text/javascript">init_tablesorter();
        function do_with_pms($pmcbox, $actions) {
            var $post = jQuery($pmcbox).serialize();
            if (!$post) {
                alert("[*'nothing_selected'|lang|sl*]");
                return;
            }
            var $act = jQuery($actions).val();
            if ($act == "delete") {
                if (!confirm("[*'pm_are_you_sure_to_delete_many'|lang|sl*]"))
                return;
            }
            jQuery.post("[*$BASEURL|sl*]index.php?[*fk ajax=1*]module=messages&from_ajax=1&act="+$act, $post, function (data) {
                if (data=="OK!") {
                    jQuery($pmcbox).each(function () {
                        if (!jQuery(this).attr("checked"))
                            return;
                        var $id = jQuery(this).val();
                        if ($act=="delete") {
                            jQuery("tr.item_"+$id).children("td").each(function () {
                                jQuery(this).fadeOut(2000, function (){
                                    jQuery(this).parent("tr").remove();
                                });
                            });
                        } else {
                            jQuery("td.item_"+$id+" a").css({"text-decoration": "none",
                                "font-weight": "normal"});
                            jQuery("td.item_"+$id+" img").remove();
                        }
                    });
                    alert("[*'success'|lang|sl*]!");
                } else
                    alert("[*'error'|lang|sl*]! " + data);
            });
        }</script>
    <div class="body_messages">
        <table width="100%" class="tablesorter">
            <thead>
                <tr>
                    <th width="55%"><b>[*'pm_title_small'|lang*]</b></th>
                    <th width="15%"><b>[*if
			!$out*][*'pm_poster'|lang*][*else*][*'pm_receiver'|lang*][*/if*]</b></th>
                    <th width="15%"><b>[*'pm_added'|lang*]</b></th>
                    <th width="15%" class="js_nosort"><b>[*'pm_actions'|lang*]&nbsp;</b><input
                            type="checkbox" title="[*'mark_this_all'|lang*]"
                            onclick="select_all(this, 'input.marked_pms')"></th>
                </tr>
            </thead>
            <tbody>
                [*foreach from=$res item=row*]
                    <tr class="item_[*$row.id*]">
                        <td class="item_[*$row.id*]">
                            [*if $row.unread && !$out*]
                                <img src='[*$theme_path*]engine_images/new.png' alt="[*'new'|lang*]">
                            [*/if*]
                            <a href="javascript:void(0);" onclick="read_msg('[*$row.id*]');"
                               [*if $row.unread && !$out*] 
                                   style="text-decoration: underline; font-weight: bold;"
                               [*/if*]>
                                [*$row.subject*]</a></td>
                        <td>[*$row.username|gcl:$row.group*]</td>
                        <td>[*date time=$row.time*]</td>
                        <td><a href="javascript:void(0);"><img
                                    src="[*$theme_path*]engine_images/delete.png"
                                    onclick="remove_message('[*$row.id*]');" title="[*'delete'|lang*]"
                                    alt="[*'delete'|lang*]"></a>
                            <input type="checkbox" name="item[]" value="[*$row.id*]" 
                                   title="[*'mark_this'|lang*]" class="marked_pms"></td>
                    </tr>
                [*/foreach*]
            </tbody>
        </table>
        <div align="right"><b>[*'pm_actions_with'|lang*]</b>&nbsp; <select
                name="actions" id="actions_with_pm">
                <option value="delete">[*'delete'|lang*]</option>
                [*if !$out*]
                    <option value="s_read">[*'pm_read'|lang*]</option>
                [*/if*]
            </select>&nbsp;<input type="button" value="[*'run'|lang*]"
                                  onclick="do_with_pms('.marked_pms', '#actions_with_pm');">
        </div>
    [*else*] 
        [*message lang_var='pm_no_rows' type='info'*] 
    [*/if*]
</div>