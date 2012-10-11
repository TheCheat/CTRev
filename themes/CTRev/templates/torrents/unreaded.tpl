[*if $rows*]
    <div id="update_unreaded_torrents">
        <script type="text/javascript">
            init_tablesorter();
            function change_tpage(to_page) {
                jQuery.get('[*$baseurl|sl*]index.php', {'module': 'torrents', 'act':'unreaded', 'from_ajax':1, 'page':to_page, 'nno':1}, function (data) {
                    jQuery("#update_unreaded_torrents").empty();
                    jQuery("#update_unreaded_torrents").append(data);
                });
            }
            function make_readed(id) {
                jQuery.post('[*$baseurl|sl*]index.php?module=torrents&from_ajax=1&act=read', {'id':id}, function (data) {
                    if (data=="OK!") {
                        alert('[*'success'|lang|sl*]!');
                        delete_this(id);
                    } else {
                        alert('[*'error'|lang|sl*]: '+data);
                    }
                });
            }
            function delete_this(id) {
                jQuery('#unreaded_id'+id).children("td").each(function () {
                    jQuery(this).fadeOut(1000, function () {
                        jQuery(this).parent().remove();
                    });
                });
            }
            function read_all() {
                jQuery.get('[*$baseurl|sl*]index.php', {'module': 'torrents', 'from_ajax': 1, 'act': 'read'}, function (data) {
                    if (data=="OK!") {
                        alert('[*'success'|lang|sl*]!');
                        window.location = '[*$baseurl|sl*]';
                    } else {
                        alert('[*'error'|lang|sl*]: '+data);
                    }
                });
            }
        </script>
        <div class="cornerText gray_color gray_border">
            <fieldset><legend>[*'torrents_new_torrents'|lang*]&nbsp;&bull;&nbsp;<a
                        href="javascript:read_all();">[*'torrents_read_all'|lang*]</a></legend>
                <table class="tablesorter">
                    <thead>
                        <tr>
                            <th>[*'torrents_title'|lang*]</th>
                            <th>[*'torrents_added_unreaded'|lang*]</th>
                            <th>[*'torrents_author'|lang*]</th>
                            <th class="js_nosort">[*'torrents_read'|lang*]</th>
                        </tr>
                    </thead>
                    <tbody>
                        [*foreach from=$rows item=row*]
                            <tr id="unreaded_id[*$row.id*]">
                                <td><b>[*'torrents_torrent'|lang*]</b>&nbsp;<a
                                        href="[*gen_link module='torrents' title=$row.title id=$row.id*]"
                                        onclick="delete_this('[*$row.id|sl*]');">[*$row.title*]</a></td>
                                <td>[*date time=$row.posted_time*]</td>
                                <td>[*$row.username|gcl:$row.group*]</td>
                                <td><a href="javascript:make_readed('[*$row.id|sl*]');"><img
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
    [*message lang_var='torrents_unreaded_none' type='info'*]
[*/if*]
