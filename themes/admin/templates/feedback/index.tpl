[*if !$res*]
    [*message lang_var='feedback_no_one' type='info' die=1*]
[*/if*]
[*if !$from_ajax*]
    <script type='text/javascript'>
        var admin_location_sort_page = '';
        var admin_feedback_type = '&type=[*$type|sl*]';
        function remote_ts_location(post) {
            admin_location_sort_page = '&sort=' + post;
            switch_feedback_page('');
            reset_paginator();
        }
        init_tablesorter();
        function delete_feedback(obj, id) {
            if (!confirm('[*'are_you_sure_to_delete_this'|lang|sl*]'))
                return;
            jQuery.post('[*$admin_file|sl*]&from_ajax=1&act=delete', {'id': id}, function(data) {
                if (is_ok(data)) {
                    jQuery('td', jQuery(obj).parents('tr')).fadeOut(1000, function() {
                        jQuery(this).parent().remove();
                    });
                } else
                    alert(error_text);
            });
        }
        function clear_feedback() {
            jQuery.post('[*$admin_file|sl*]&from_ajax=1&act=clear' + admin_feedback_type, function(data) {
                if (is_ok(data))
                    window.location = '[*$admin_file|sl*]';
                else
                    alert(error_text);
            });
        }
        function switch_feedback_page(page) {
            var add = '&from_ajax=1&nno=1' + admin_feedback_type + admin_location_sort_page;
            jQuery.post('[*$admin_file|sl*]' + add + '&page=' + page, function(data) {
                jQuery('#feedbacktable').replaceWith(data);
            });
        }
        function expand_feedback_content(obj) {
            obj = jQuery(obj);
            obj.hide();
            obj.prevAll('div.br').hide();
            obj.prevAll('span').hide();
            obj.prevAll('div.hidden').show();
        }
    </script>
[*/if*]
[*if !$from_ajax*]
    <div class='cornerText gray_color gray_border'>
        <fieldset><legend>
                [*'feedback_title'|lang*]
            </legend>
            <table class="tablesorter">
                <thead>
                    <tr>
                        <th class='js_remote'>[*'feedback_area_subject'|lang*]</th>
                        <th class='js_remote'>[*'feedback_area_time'|lang*]</th>
                        <th class='js_remote'>[*'feedback_area_uid'|lang*]</th>
                        <th class='js_remote'>[*'feedback_area_ip'|lang*]</th>
                        <th class='js_remote js_nosort'>[*'feedback_area_content'|lang*]</th>
                    </tr>
                </thead>
            [*/if*]
            <tbody id='feedbacktable'>
                [*foreach from=$res item=row*]
                    <tr>
                        <td>[*$row.subject*]
                            <a href='javascript:void(0);' onclick="delete_feedback(this, '[*$row.id*]');">
                                <img src='[*$atheme_path*]engine_images/delete.png' alt='[*"delete"|lang*]'>
                            </a>
                        </td>
                        <td>[*date time=$row.time format='ymdhis'*]</td>
                        <td>
                            [*if $row.uid*]
                                [*$row.username|gcl:$row.group*]
                            [*else*]
                                [*'no'|lang*]
                            [*/if*]
                        </td>
                        <td>[*$row.ip|l2ip*]</td>
                        [*assign var='content_cut' value=$row.content|cut:30*]
                        <td><span>[*$content_cut|nl2br*]</span>
                            <div class='hidden'>[*$row.content|nl2br*]</div>
                            [*if mb_strlen($row.content)>30*]
                                <div class='br'></div>
                                <a href='javascript:void(0);' onclick='expand_feedback_content(this);'>
                                    [*'feedback_area_content_expand'|lang*]
                                </a>
                            [*/if*]
                        </td>
                    </tr>
                [*/foreach*]
            </tbody>
            [*if !$from_ajax*]
            </table>
            <div align='right'>
                <input type='button' onclick="clear_feedback(this.value);" value='[*'feedback_clear'|lang*]'>
            </div>
            [*$pages*]
        </fieldset>
    </div>
[*/if*]