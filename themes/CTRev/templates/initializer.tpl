<!-- Общая инициализация, в т.ч. и в АЦ, посему не советую сильно изменять -->
<link rel="shortcut icon" href="favicon.png" type="image/png">
<meta http-equiv="Content-Language" content="ru-en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript">
    theme_path = '[*$theme_path|sl*]';
    loading_text = '[*'loading'|lang|sl*]';
    success_text = '[*'success'|lang|sl*]';
    error_text = '[*'error'|lang|sl*]';
    yes_text = '[*'yes'|lang|sl*]';
    no_text = '[*'no'|lang|sl*]';
    ok_message = '[*$smarty.const.OK_MESSAGE|sl*]';
    are_you_sure_to_do_this = '[*'are_you_sure_to_do_this'|lang|sl*]'
    are_you_sure_to_delete_this = '[*'are_you_sure_to_delete_this'|lang|sl*]';
    are_you_sure_to_delete_this_bookmark = '[*'are_you_sure_to_delete_this_bookmark'|lang|sl*]';
</script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.cookie.js"></script>
<script type="text/javascript" src="js/jquery.resizer.js"></script>
<script type="text/javascript" src="js/jquery.corners.js"></script>
<script type="text/javascript" src="js/jquery.mainpage.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="js/jquery.tabs.js"></script>
<script type="text/javascript">
    ajax_complete();
    jQuery('html').ajaxSuccess(function(e, xhr, settings) {
        //ajax_complete();
        if (settings.async) {
            hide_ls();
        }
    });
    jQuery('html').ajaxSend(function(e, xhr, settings) {
        if (settings.async) {
            show_ls();
        }
    });
</script>