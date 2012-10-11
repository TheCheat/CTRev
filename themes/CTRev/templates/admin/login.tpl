<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>[*'site_title'|config*]</title>
        <link rel="stylesheet" href="[*$theme_path*]css/admin/login.css" type="text/css">
        <script type="text/javascript" src="[*$theme_path*]js/jquery.js"></script>
        <script type="text/javascript" src="[*$theme_path*]js/jquery.corners.js"></script>
        <script type="text/javascript" src="[*$theme_path*]js/jquery.mainpage.js"></script>
        <script type="text/javascript">
            init_corners();
            autoclear_fields();
            function admin_login(form) {
                show_ls();
                jQuery.post('admincp.php', jQuery(form).serialize(), function (data) {
                    hide_ls();
                    if (data.substr(0, 3)=='OK!')
                        window.location = data.substr(3);
                    else
                        alert('[*'error'|lang|sl*]: '+data);
                });
            }
        </script>
        <meta http-equiv="Content-Language" content="ru-en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="[*$baseurl*]favicon.png" type="image/x-icon">
    </head>
    <body>
        <div class="login_container">
            <div class="cornerText white_color gray_border">
                <fieldset>
                    <legend class="title"><b>[*'adminlogin_title'|lang*]</b></legend>
                    <form method="post" action='javascript:void(0);' onsubmit='admin_login(this);'>
                        <input class="login_input_login autoclear_fields" type="text"
                               name="login" value="[*'login'|lang*]" size='30'>
                        <div class='br'></div>
                        <input class="login_input_password autoclear_fields" type="password"
                               name="password" value="[*'password'|lang*]" size='30'>
                        <div class='br'></div>
                        <input type="submit" value="[*'adminlogin_login'|lang*]">
                    </form>
                </fieldset>
                <div class="contact_admin">
                    [*'you_can_contact_admin'|lang*]
                    <a href="mailto:[*'contact_email'|config*]">[*'contact_email'|config*]</a>
                </div>
            </div>
        </div>
        <!-- Это копирайт данного продукта. Удалять или изменять его строго запрещается!
        This is the copyright of the product. Delete or modify it is strictly prohibited! -->
        <p class="copyright">[*$copyright*]</p>
        [*include file='loading_container.tpl'*]
    </body>
</html>