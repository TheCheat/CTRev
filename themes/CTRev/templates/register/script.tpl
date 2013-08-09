<script type="text/javascript">
    jQuery(document).ready(function($) {
        $next_register_tab = $("div.register_form div.register_tabs").length - 1;
    });
    function check_from(form, type) {
        $("#error_box").hide();
        var si = "register_status_icon";
        status_icon(si, 'loading_white');
        var $data = jQuery(form).serialize();
        $data = $data + "&from_ajax=1&step=";
        if (type == 'next')
            jQuery.post('[*gen_link slashes=true module="registration" act="main"*]', $data + ($cur_tab + 1), function(data) {
                if (is_ok(data)) {
                    status_icon(si, 'success');
                    tabs_sets(type);
                } else {
                    status_icon(si, 'error');
                    $("#error_box").show();
                    $("#error_box #error_message").empty();
                    $("#error_box #error_message").append(data);
                }
            });
        else if (type == 'back') {
            status_icon(si);
            tabs_sets(type);
        } else if (type == 'end') {
            jQuery('#register_next').attr("disabled", "disabled");
            //jQuery('#register_back').attr("disabled", "disabled");
            jQuery('#register_end').attr("disabled", "disabled");
            jQuery('.progress_bar').hide();
            jQuery.post('[*gen_link slashes=true module="registration" act="main"*]', $data + "last", function(data) {
                if (is_ok(data)) {
                    status_icon(si, 'success');
                    var $obj = jQuery('.register_tabs');
                    $obj.hide();
                    $obj.eq($next_register_tab).show();
                    setTimeout("window.location = '';", 5000);
                } else {
                    status_icon(si, 'error');
                    $("#error_box").show();
                    $("#error_box #error_message").empty();
                    $("#error_box #error_message").append(data);
                }
            });
        }
    }
    function tabs_sets(type) {
        var $obj = jQuery('.register_tabs');
        jQuery('#register_end').attr("disabled", "disabled");
        jQuery('.progress_bar').show();
        if (type == 'back') {
            if ($obj.eq($cur_tab - 1).length) {
                $obj.hide();
                $cur_tab = $cur_tab - 1;
                $obj.eq($cur_tab).show();
            }
        } else {
            if ($obj.eq($cur_tab + 1).length) {
                $obj.hide();
                $cur_tab = $cur_tab + 1;
                $obj.eq($cur_tab).show();
            }
        }
        if ($obj.eq($cur_tab - 1).length && ($cur_tab - 1) >= 0) {
            jQuery('#register_back').removeAttr("disabled");
        } else {
            jQuery('#register_back').attr("disabled", "disabled");
        }
        if ($obj.eq($cur_tab + 1).length && ($cur_tab + 1) <= $next_register_tab - 1) {
            jQuery('#register_next').removeAttr("disabled");
        } else {
            jQuery('#register_next').attr("disabled", "disabled");
        }
        if ($cur_tab == $next_register_tab - 1) {
            jQuery('#register_next').attr("disabled", "disabled");
            jQuery('#register_end').removeAttr("disabled");
        }
        var $progress = jQuery(".progress_bar .progress");
        var $percent = parseInt(($cur_tab / ($next_register_tab - 1)) * 100) + "%";
        $progress.width($percent);
        $progress.children(".percent").empty();
        $progress.children(".percent").append($percent);
        onhovered_dd();
    }
    jQuery(document).ready(function() {
        $cur_tab = -1;
        tabs_sets('next');
    });
</script>