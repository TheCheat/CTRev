<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>[*'site_title'|config*]</title>
        <link rel="stylesheet" href="[*$theme_path*]css/error.css"
              type="text/css">
        <script type="text/javascript" src="[*$theme_path*]js/jquery.js"></script>
        <script type="text/javascript" src="[*$theme_path*]js/jquery.corners.js"></script>
        <script type="text/javascript">init_corners();</script>
        <meta http-equiv="Content-Language" content="ru-en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="[*$BASEURL*]favicon.png"
              type="image/x-icon">
    </head>
    <body>
        <div class="error_container">
            <div class="cornerText white_color gold_border">
                <fieldset><legend class="title"><b>[*$title*]</b></legend>
                    <div class="error_text"><b>[*$message*]</b></div>
                    [*if $backtrace*]
                        <br>
                        <div class="chronology">
                            <div class="error_text"><b>[*'db_chronology'|lang*]</b></div>
                            <br>
                            [*$backtrace*]
                        </div>
                    [*/if*]
                </fieldset>
                <div class="contact_admin">[*'you_can_contact_admin'|lang*]&nbsp;<a
                        href="mailto:[*'contact_email'|config*]">[*'contact_email'|config*]</a></div>
            </div>
        </div>
        <!-- Это копирайт данного продукта. Удалять или изменять его строго запрещается!
        This is the copyright of the product. Delete or modify it is strictly prohibited! -->
        <p class="copyright">[*$copyright*]</p>
    </body>
</html>