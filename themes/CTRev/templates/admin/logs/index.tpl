[*if !$res*]
    [*message lang_var='logs_no_one' type='info' die=1*]
[*/if*]
[*if !$from_ajax*]
    <script type='text/javascript'>
        var admin_location_sort_page = '';
        function remote_ts_location(post) {
            admin_location_sort_page = '&sort='+post
            switch_logs_page('');
            reset_paginator();
        }
        init_tablesorter();
        function clear_logs(type) {
            jQuery.post('[*$admin_file|sl*]&from_ajax=1&act=clear', {'type': type}, function (data) {
                if (data=='OK!')
                    window.location = '[*$admin_file|sl*]';
                else
                    alert(error_text);
            });
        }
        function switch_logs_page(page) {
            var add = '&from_ajax=1&nno=1&type=[*$curtype|sl*]'+admin_location_sort_page;
            jQuery.post('[*$admin_file|sl*]'+add+'&page='+page, function (data) {
                jQuery('#logstable').replaceWith(data);
            });
        }
    </script>
[*/if*]
[*if !$from_ajax*]
    <div class='cornerText gray_color gray_border'>
        <fieldset><legend>
                [*'logs_title'|lang*]
                [*if $curtype*]
                    ([*"logs_type_$curtype"|lang*])
                [*/if*]
            </legend>
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th class='js_remote'>[*'logs_area_subject'|lang*]</th>
                        <th class='js_remote'>[*'logs_area_type'|lang*]</th>
                        <th class='js_remote'>[*'logs_area_time'|lang*]</th>
                        <th class='js_remote'>[*'logs_area_byuid'|lang*]</th>
                        <th class='js_remote'>[*'logs_area_touid'|lang*]</th>
                    </tr>
                </thead>
            [*/if*]
            <tbody id='logstable'>
                [*foreach from=$res item=row*]
                    [*assign var='type' value=$row.type*]
                    <tr>
                        <td>[*$row.subject*][*if $row.descr*] ([*$row.descr*])[*/if*]</td>
                        <td>[*"logs_type_$type"|lang*]</td>
                        <td>[*date time=$row.time format='ymdhis'*]</td>
                        <td>[*$row.username|gcl:$row.group*]
                            ([*$row.byip|l2ip*])</td>
                        <td>
                            [*if $row.touid*]
                                [*$row.tusername|gcl:$row.tgroup*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                    </tr>
                [*/foreach*]
            </tbody>
            [*if !$from_ajax*]
            </table>
            <div align='right'>
                <b>[*'logs_clear'|lang*]</b>:
                <select name='clear' onchange="clear_logs(this.value);">
                    <option value='' selected='selected'>[*'logs_select_type'|lang*]</option>
                    <option value=''>[*'logs_type_all'|lang*]</option>
                    [*foreach from=$log_types item='type'*]
                        <option value='[*$type*]'>[*"logs_type_$type"|lang*]</option>
                    [*/foreach*]
                </select>
            </div>
            [*$pages*]
        </fieldset>
    </div>
[*/if*]