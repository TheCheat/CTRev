<script type="text/javascript"
src="js/jquery.jCal.animate.js"></script>
<script type="text/javascript" src="js/jquery.jCal.js"></script>
<script type="text/javascript">
    var $content_per_dates = [*$content_count*];
    function init_date() {
        $system_time = new Date();
        $system_time.setSeconds($system_time.getSeconds());
        setInterval(function() {
            $system_time.setTime($system_time.getTime() + 1000);
            jQuery("#system_time").empty();
            jQuery("#system_time").append(add_zero($system_time.getHours()) + ":" + add_zero($system_time.getMinutes()) + ":" + add_zero($system_time.getSeconds()));
        }, 1000);
    }
    function add_zero($digit) {
        if ($digit < 10)
            $digit = "0" + $digit;
        return $digit;
    }
    jQuery(document).ready(function($) {
        //calendar_inited = false;
        $('#block_calendar').jCal({
            days: 1,
            showMonths: 1,
            changeMY: function(days, jCalData) {
                //if (!calendar_inited) {
                prehide_ls();
                //    calendar_inited = true;
                //}
                jQuery('#calendar_loader').show();
                jQuery.post("index.php?module=ajax_index&from_ajax=1&act=calendar_content", {"year": days.getFullYear(), "month": days.getMonth() + 1}, function(data) {
                    jQuery("#to_append_new_days").empty();
                    jQuery("#to_append_new_days").append(data);
                    $('#block_calendar').jCal(jCalData);
                    $content_per_dates = new Array();
                    jQuery('#calendar_loader').hide();
                });
                return true;
            },
            callback: function(date) {
                var $link = '[*gen_link module="content" year="\$1" month="\$2" day="\$3" slashes=true*]';
                var $slink = $link.replace("$1", date.getFullYear()).replace("$2", parseInt(date.getMonth() + 1)).replace("$3", date.getDate());
                window.location = $slink;
            },
            monthSelect: true,
            sDate: new Date(),
            dayOffset: 1,
            dow: [[*$day_of_week*]],
            ml: [[*$months*]],
            dCheck: function(day) {
                var $curdate = new Date();
                var $ret = '';
                if (day.getFullYear() == $curdate.getFullYear() && day.getMonth() == $curdate.getMonth() && day.getDate() == $curdate.getDate())
                    $ret = 'curday';
                else if (day.getDay() != 6 && day.getDay() != 0)
                    $ret = 'day';
                else
                    $ret = 'weekend';
                if ($content_per_dates[day.getDate()]) {
                    return new Array("<" + "span title=\"" + $content_per_dates[day.getDate()] + " [*'calendar_content_added_at_day'|lang|sl*]\"" + ">", $ret + " content_in_day", "<" + "/span" + ">");
                } else {
                    return $ret;
                }
                //	return 'invday';
            }
        });
        init_date();
    });</script>
<div align='center' class='cornerText gray_color gray_border'>
    <div id="to_append_new_days"></div>
    <div id="block_calendar" class="calendar">[*'loading'|lang*]...</div>
    <br>    
    <span class='hidden' id='calendar_loader'>
        <img src="[*$theme_path*]images/[*$color_path*]loading/white_loading.gif"
             alt="[*'loading'|lang*]" title="[*'loading'|lang*]" height='12'></span>
    [*'time'|lang*]<span id="system_time">[*'loading'|lang*]...</span>
</div>