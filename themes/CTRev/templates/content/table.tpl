[*if $act=='unchecked'*]
    [*assign var='ract' value='check'*]
[*else*]
    [*assign var='ract' value='read'*]
[*/if*]
[*if $rows*]
    <div id="update_table_content">
        <script type="text/javascript">
            init_tablesorter();
            function change_tpage(to_page) {
                jQuery.get('index.php', {'module': 'content', 'act': '[*$act*]', 'from_ajax': 1, 'page': to_page, 'nno': 1}, function(data) {
                    jQuery("#update_table_content").empty();
                    jQuery("#update_table_content").append(data);
                });
            }
            function make_content_action(id) {
                jQuery.post('index.php?module=content&from_ajax=1&act=[*$ract*]', {'id': id}, function(data) {
                    if (is_ok(data)) {
                        //alert('[*'success'|lang|sl*]!');
                        delete_this(id);
                    } else {
                        alert('[*'error'|lang|sl*]: ' + data);
                    }
                });
            }
            function delete_this(id) {
                jQuery('#content_table_id' + id).children("td").each(function() {
                    jQuery(this).fadeOut(1000, function() {
                        jQuery(this).parent().remove();
                    });
                });
            }
            function content_action_all() {
                jQuery.get('index.php', {'module': 'content', 'from_ajax': 1, 'act': '[*$ract*]'}, function(data) {
                    if (is_ok(data)) {
                        //alert('[*'success'|lang|sl*]!');
                        window.location = '';
                    } else {
                        alert('[*'error'|lang|sl*]: ' + data);
                    }
                });
            }
        </script>
        <div class="cornerText gray_color gray_border">
            <fieldset><legend>[*'content_new'|lang*]&nbsp;&bull;&nbsp;<a
                        href="javascript:content_action_all();">[*"content_`$ract`_all"|lang*]</a></legend>
                <table class="tablesorter">
                    <thead>
                        <tr>
                            <th>[*'content_title'|lang*]</th>
                            <th>[*'content_added_table'|lang*]</th>
                            <th>[*'content_author'|lang*]</th>
                            <th class="js_nosort">[*"content_$ract"|lang*]</th>
                        </tr>
                    </thead>
                    <tbody>
                        [*foreach from=$rows item=row*]
                            <tr id="content_table_id[*$row.id*]">
                                <td><b>[*'content_item'|lang*]</b>&nbsp;<a
                                        href="[*gen_link module='content' title=$row.title id=$row.id*]"
                                        onclick="delete_this('[*$row.id|sl*]');">[*$row.title*]</a></td>
                                <td>[*date time=$row.posted_time*]</td>
                                <td>[*$row.username|gcl:$row.group*]</td>
                                <td><a href="javascript:make_content_action('[*$row.id|sl*]');"><img
                                            src="[*$theme_path*]engine_images/confirm.png"></a></td>
                            </tr>
                        [*/foreach*]
                    </tbody>
                </table>
                [*$pages*]
            </fieldset>
        </div>
    </div>
[*else*]
    [*assign var='postfix' value='_none'*]
    [*message lang_var="content_table_$ract$postfix" type='info'*]
[*/if*]