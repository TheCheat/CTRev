<!DOCTYPE HTML PUBLIC  "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?= lang::o()->v('install_title') ?>&nbsp;&bull;&nbsp;<?= lang::o()->v('install_page_' . INSTALL_PAGE) ?></title>
        <link rel="stylesheet" href="install/style/content/style.css"
              type="text/css">
        <script type="text/javascript" src="<?= CONTENT_PATH ?>jquery.js"></script>
        <script type="text/javascript" src="<?= CONTENT_PATH ?>jquery.corners.js"></script>
        <script type="text/javascript">
            init_corners();
            current_page = -1;
            max_page = <?= count($data['pages']) ?> - 1;
            pages = <?= JS_PAGES ?>;
            function onhovered_dd() {
                jQuery(document).ready(function() {
                    jQuery("dl.info_text dd:not(.inited_onhover)")
                            .addClass('inited_onhover').hover(function() {
                        jQuery(this).prev().removeClass("hovered");
                        jQuery(this).prev().addClass("hovered");
                    }, function() {
                        jQuery(this).prev().removeClass("hovered");
                    });
                });
            }
            function status_icon(act, data) {
                var $si = jQuery('#status_icon_install');
                $si.empty();
                $si.attr("class", "status_icon");
                if (act) {
                    $si.addClass("status_icon_" + act);
                    $si.show();
                    $si.append(data ? data : "");
                } else
                    $si.hide();
            }
            function confirm_next(form) {
                if (form)
                    form = jQuery(form).serialize();
                else
                    form = '';
                status_icon('loading');
                show_buttons(true); // disable until not loaded
                jQuery.post('<?= INSTALL_FILE ?>.php?page=' + pages[current_page] + '&check=1', form, function(data) {
                    if (data == '<?= OK_MESSAGE ?>') {
                        current_page++;
                        jQuery.post('<?= INSTALL_FILE ?>.php?page=' + pages[current_page], function(data) {
                            status_icon('success');
                            show_buttons();
                            jQuery('#install_contents').empty().append(data);
                            init_corners();
                            onhovered_dd();
                        });
                    } else {
                        status_icon('error');
                        jQuery('#error_message').empty().append(data);
                        jQuery('#error_box').show();
                    }
                });
            }
            function confirm_back() {
                status_icon('loading');
                show_buttons(true); // disable until not loaded
                current_page--;
                jQuery.post('<?= INSTALL_FILE ?>.php?page=' + pages[current_page], function(data) {
                    show_buttons();
                    status_icon('success');
                    jQuery('#install_contents').empty().append(data);
                    init_corners();
                    onhovered_dd();
                });

            }
            function switch_buttons(disable) {
                if (disable) {
                    jQuery('#button_next').attr('disabled', 'disabled');
                    jQuery('#button_back').attr('disabled', 'disabled');
                } else {
                    jQuery('#button_next').removeAttr('disabled');
                    jQuery('#button_back').removeAttr('disabled');
                }
            }
            function show_buttons() {
                var e = jQuery('div.left_column ul li span');
                e.replaceWith(e.html());
                var cp = jQuery('div.left_column ul li').eq(current_page);
                jQuery('#page_title').empty().append(cp.text());
                cp.wrapInner('<span/>');
                jQuery('#error_box').hide();
                if (current_page > 0)
                    jQuery('#button_back').removeAttr('disabled');
                else
                    jQuery('#button_back').attr('disabled', 'disabled');
                if (current_page < max_page)
                    jQuery('#button_next').removeAttr('disabled');
                else
                    jQuery('#button_next').attr('disabled', 'disabled');
                var p = max_page ? current_page / max_page : 1;
                var percent = parseInt(p * 100) + '%';
                jQuery('div.progress_bar div.progress').css('width', percent);
                jQuery('div.progress_bar div.percent').empty().append(percent);
            }
            function lang_selector() {
                jQuery(document).ready(function() {
                    jQuery('#lang_selector select').change(function() {
                        window.location = '?install_lang=' + jQuery(this).val();
                    });
                });
            }
            lang_selector();
        </script>
        <meta http-equiv="Content-Language" content="ru-en">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    </head>
    <body>
        <div class="cornerText install_container gray_border">
            <fieldset>
                <legend class='title'><div class='status_icon' id="status_icon_install"></div>
                    <span id="page_title"></span></legend>
                <div class='content install_body'>
                    <div class='tr'>
                        <div class='td left_column'>
                            <ul>
                                <?php
                                foreach ($data['pages'] as $page)
                                    print("<li>" . lang::o()->v('install_page_' . $page) . "</li>");
                                ?>
                            </ul>
                        </div>
                        <div class='td'>
                            <form method='post' action="javascript:void(0);" onsubmit="confirm_next(this);">
                                <center>
                                    <div id="error_box" class='hidden'>
                                        <div align="left" class='m_message'>
                                            <div class="error_title" align="left"><?= lang::o()->v('error') ?></div>
                                            <div class="error m_message_table">
                                                <div align="left" class='content'>
                                                    <div class='tr'>
                                                        <div class="m_message_image error_image td"></div>
                                                        <div class='td'>
                                                            <div class='m_message_content' id="error_message"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <b><?= lang::o()->v('install_language_selector') ?>: </b>
                                    <span id='lang_selector'><?= $data['input']->scurrent($data['clang'])->select_folder("lang", LANGUAGES_PATH) ?></span><br><br>
                                    <div class="cornerText progress_bar" align="left">
                                        <div class="progress" style="width: 0%;">
                                            <div class="percent">0%</div>
                                        </div>
                                    </div>
                                </center>
                                <br>
                                <div id="install_contents"></div>
                                <center>
                                    <input type='button' id="button_back" 
                                           disabled='disabled'
                                           value="<?= lang::o()->v('install_back') ?>" 
                                           onclick='confirm_back();'>
                                    <input type='submit' id="button_next" 
                                           disabled='disabled'
                                           value="<?= lang::o()->v('install_continue') ?>">
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <!-- Это копирайт данного продукта. Удалять или изменять его строго запрещается!
        This is the copyright of the product. Delete or modify it is strictly prohibited! -->
        <p class="copyright"><?= COPYRIGHT ?></p>
        <script type='text/javascript'>
            $resizer = function() {
                jQuery('div.install_body').css('min-height', '100%');
                jQuery('div.install_body').css('min-height', (jQuery(window).height() - 150) + "px"); // Тут проще так, чем через CSS
            }
            jQuery(document).ready($resizer).resize($resizer);
            confirm_next();
        </script>
    </body>
</html>