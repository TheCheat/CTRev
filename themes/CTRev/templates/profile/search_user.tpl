<script type="text/javascript">
    search_user_subpost = '';
    search_user_subid = '';
    function remote_ts_location($post) {
        search_user_subpost = $post+"&subupdate=1";
        search_user_subid = 'subupdate_rows';
        submit_search_form('');
        reset_paginator();
    }
    init_tablesorter();
    function submit_search_form(page) {
        var $form_data = jQuery("#search_users_form").serialize();
        if (search_user_subpost)
            $form_data += "&orderby="+search_user_subpost;
        var si = 'search_status_icon';
        status_icon(si, 'loading_white');
        jQuery.post("[*$baseurl|sl*]index.php?"
    [*if $admin_file*]
                + '[*$admin_sid*]&'
        [*if $s_unco*]
                    + 'unco=1&'
        [*/if*]
    [*/if*]
                + "module=search_module&act=user&from_ajax=1"+
                (page?"&page="+page:"")

    [*if $parent_form && $parent_el*]
                + '&parent=1'
    [*/if*], $form_data, function (data) {
            var obj = "#"+(search_user_subid?search_user_subid:'ajax_search_body');
            jQuery(obj).empty();
            jQuery(obj).append(data);
            init_tablesorter();
            status_icon(si, 'success');
        });
    }
    [*if $parent_form && $parent_el*]
    function insert_intoparent(username) {
        jQuery("form[name=[*$parent_form|sl*]] input[name=[*$parent_el|sl*]]", window.opener.document).val(username);
        window.close();
    }
    [*/if*]
    [*if $s_nosearch*]
    jQuery(document).ready(function () {
        submit_search_form();
    });
    [*/if*]
</script>
[*if !$s_nosearch*]
    <form method="post" action="[*gen_link module='search'*]"
          id="search_users_form">
        <div class="cornerText gray_color gray_border">
            <fieldset><legend>[*'search_main_data'|lang*]</legend>
                <div class="content">
                    <div class="tr">
                        <div class="td">
                            <dl class="info_text">
                                <dt>[*'usearch_nickname'|lang*]</dt>
                                <dd><input type="text" name="user" value="[*$uname*]"></dd>
                                <dt>[*'usearch_email'|lang*]</dt>
                                <dd><input type="text" name="email" value="[*$email*]"></dd>
                                <dt>[*'usearch_icq'|lang*]</dt>
                                <dd><input type="text" name="icq" value=""></dd>
                                <dt>[*'usearch_skype'|lang*]</dt>
                                <dd><input type="text" name="skype" value=""></dd>
                                <dt>[*'usearch_name_surname'|lang*]</dt>
                                <dd><input type="text" name="name_surname" value=""></dd>
                            </dl>
                        </div>
                        <div class="td">
                            <dl class="info_text">
                                <dt>[*'usearch_ip'|lang*]</dt>
                                <dd><input type="text" name="ip" value="[*$ip*]"></dd>
                                <dt>[*'usearch_country'|lang*]</dt>
                                <dd>[*select_countries*]</dd>
                                <dt>[*'usearch_group'|lang*]</dt>
                                <dd>[*select_groups*]</dd>
                                <dt>[*'usearch_registered'|lang*]</dt>
                                <dd><select name='reg_type'>
                                        <option value="0">=</option>
                                        <option value="1">&gt;</option>
                                        <option value="2">&lt;</option>
                                    </select>&nbsp;[*select_date name="reg"*]</dd>
                                <dt>[*'usearch_last_visit'|lang*]</dt>
                                <dd><select name='lv_type'>
                                        <option value="0">=</option>
                                        <option value="1">&gt;</option>
                                        <option value="2">&lt;</option>
                                    </select>&nbsp;[*select_date name="lv"*]</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="float_left">
                    <div class="si_downer">
                        <div class="status_icon" id="search_status_icon"></div>
                    </div>
                </div>
                <center><font size="1">[*'search_keywords_notice'|lang*]</font><br>
                    <input type="button" value="[*'search'|lang*]!"
                           onclick="submit_search_form();"></center>
            </fieldset>
        </div>
    </form>
[*/if*]
<div id="ajax_search_body"></div>