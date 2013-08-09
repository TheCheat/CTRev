<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <base href="[*$baseurl*]">
        <title>[*'site_title'|config*]</title>
        <link rel="stylesheet" href="[*$theme_path*]css/error.css"
              type="text/css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.corners.js"></script>
        <script type="text/javascript">init_corners();</script>
        <meta http-equiv="Content-Language" content="ru-en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>
        <div class="error_container">
            <div class="cornerText white_color gold_border">
                <fieldset><legend class="title"><b>[*'error'|lang*]!</b></legend> <br>
                    [*'soff_now_site_offline'|lang*] 
                    [*if 'siteoffline_reason'|config*]
                        [*'soff_for_reason'|lang*] 
                        <b>[*'siteoffline_reason'|config*]</b>
                    [*/if*]
                    <br>
                    [*if 'site_autoon'|config*]
                        [*assign var="autoon" value='site_autoon'|config|date_time:"ymdhis"*]
                        [*'soff_autonon_at'|pf:$autoon*]<br>
                    [*/if*] 
                    [*if $curuser*]
                        [*'soff_you_logged_as'|lang*]
                        [*$curgroup|gc:$curuser*] ([*$curgroup|gc*]).
                        [*'soff_for_logout_press'|lang*] <a href="[*gen_link module='login' act='out'*]">[*'this_link'|lang*]</a>.
                    [*else*]
                        [*'soff_you_not_logged'|lang*]
                        <a href="[*gen_link module='login'*]">[*'this_link'|lang*]</a>.
                    [*/if*]
                </fieldset>
            </div>
        </div>
    </body>
</html>