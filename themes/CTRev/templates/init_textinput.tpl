[*if !$inited_bbcodes*]
    <script type="text/javascript" src="[*$theme_path*]js/jquery.a-tools.js"></script>
    <script type="text/javascript" src="[*$theme_path*]js/jquery.trackbar.js"></script>
    <script type="text/javascript" src="[*$theme_path*]js/jquery.colorpicker.js"></script>
    <script type="text/javascript" src="[*$theme_path*]js/jquery.slideshow.js"></script>
    <script type="text/javascript">
        URL_PATTERN = /[*$URL_PATTERN*]/gi;
        please_enter_link = '[*'bbcode_please_enter_link'|lang|sl*]';
        please_enter_pos = '[*'bbcode_please_enter_img_position'|lang|sl*]';
        bbcode_error = '[*'bbcode_error'|lang|sl*]';
        smilies_array = [*$smilies_array*];
        smilies_src = '[*$baseurl|sl*][*'smilies_folder'|config|sl*]/';
        lang_bbcodes = {
            "code":'[*'bbcode_wysiwyg_code'|lang|sl*]',
            "spoiler":'[*'bbcode_wysiwyg_spoiler'|lang|sl*]',
            "quote":'[*'bbcode_wysiwyg_quote'|lang|sl*]',
            "hide":'[*'bbcode_wysiwyg_hide'|lang|sl*]',
            "quote=":'[*'bbcode_wysiwyg_from'|lang|sl*]',
            "mc":'[*'bbcode_wysiwyg_mc'|lang|sl*]',
            "gi":'[*'bbcode_wysiwyg_gi'|lang|sl*]'
        };
    </script>
    <script type="text/javascript"
    src="[*$theme_path*]js/jquery.bbeditor.js"></script>
[*/if*]
<script type="text/javascript">
    opacity_bbcodes();
    slides_init();
    init_corners();
    init_trackbar("[*$textarea_name|sl*]");
    init_colorpicker('[*$textarea_name|sl*]');
</script>
<div class="textinput_box">
    <div class="slidesContainer">
        <div class="slide bbcodes_slides">
            <div class="bbcodes">
                <a title="[*'bbcode_wysiwyg_editor'|lang*]"
                   href="javascript:void(0);" onclick="editor_type('[*$textarea_name|sl*]', 1);"
                   class="wysiwyg_type bbcodes"></a> <a title="[*'bbcode_bbcode_editor'|lang*]"
                   href="javascript:void(0);" onclick="editor_type('[*$textarea_name|sl*]', 0);"
                   class="bbcode_type bbcodes bbcodes_border"></a> <a
                   title='[*'bbcode_code_left'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'left');"
                   class="bbcode_left bbcodes"></a> <a
                   title='[*'bbcode_code_right'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'center');"
                   class="bbcode_center bbcodes"></a> <a
                   title='[*'bbcode_code_center'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'right');"
                   class="bbcode_right bbcodes"></a> <a
                   title='[*'bbcode_code_justify'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'justify');"
                   class="bbcode_justify bbcodes bbcodes_border"></a> <a
                   title='[*'bbcode_code_p'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'p');"
                   class="bbcode_paragraf bbcodes bbcodes_border"></a> <a
                   title='[*'bbcode_code_b'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'b');" class="bbcode_b bbcodes"></a>
                <a href="javascript:bbcode('[*$textarea_name|sl*]', 'i');"
                   title='[*'bbcode_code_i'|lang*]'
                   class="bbcode_i bbcodes"></a> <a
                   title='[*'bbcode_code_s'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 's');" class="bbcode_s bbcodes"></a>
                <a href="javascript:bbcode('[*$textarea_name|sl*]', 'u');"
                   title='[*'bbcode_code_u'|lang*]'
                   class="bbcode_u bbcodes bbcodes_border"></a> <a
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'sup');"
                   title='[*'bbcode_code_sup'|lang*]'
                   class="bbcode_xd bbcodes"></a>  <a
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'sub');"
                   title='[*'bbcode_code_sub'|lang*]'
                   class="bbcode_xt bbcodes bbcodes_border"></a><a
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'list');"
                   title='[*'bbcode_code_list'|lang*]'
                   class="bbcode_lin bbcodes"></a> <a
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'nlist');"
                   title='[*'bbcode_code_nlist'|lang*]'
                   class="bbcode_lil bbcodes bbcodes_border"></a> <a
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'quote');"
                   title='[*'bbcode_code_quote'|lang*]'
                   class="bbcode_quote bbcodes bbcodes_border"></a>
                <div class="toggleSubMenu bbcodeMenu">
                    <a href="javascript:void(0);" title='[*'bbcode_code_size'|lang*]'
                       onclick="toggle_menu(this, true);" class="bbcode_size bbcodes">
                    </a>
                    <div class="menu" style="width: 250px;">
                        <div class="cornerText">
                            <div id="ss_[*$textarea_name*]"></div>
                            <center>
                                <a href="javascript:void(0);" onclick="bbcode('[*$textarea_name|sl*]', 'size', jQuery('font',this).css('fontSize'));">
                                    <font class="preview_[*$textarea_name*]">[*'bbcode_size'|lang*]</font>
                                </a>
                            </center>
                        </div>
                    </div>
                </div>
                <div class="toggleSubMenu bbcodeMenu">
                    <a href="javascript:void(0);" onclick="toggle_menu(this, true);"
                       title='[*'bbcode_code_color'|lang*]' class="bbcode_color bbcodes"></a>
                    <div class="menu colorpicker" id="colorpicker_[*$textarea_name*]"></div>
                </div>
                <div class="toggleSubMenu bbcodeMenu">
                    <a href="javascript:void(0);" title='[*'bbcode_code_smile'|lang*]'
                       onclick="toggle_menu(this, true);" 
                       class="bbcode_smilie bbcodes bbcodes_border"></a>
                    <div class="menu fixed_height_menu" style="width: 120px;">
                        <div class="cornerText">
                            [*foreach from=$smilies item=res key=num*]
                                <img src="[*$baseurl*][*'smilies_folder'|config*]/[*$res.image*]"
                                     alt="[*$res.name*]" title="[*$res.name*]"
                                     onclick="insert_smilie('[*$textarea_name|sl*]', '[*$res.code|sl*]');">
                                <!--&nbsp;
                                [*if ($num + 1) % 4 == 0*]
                                    <br>
                                [*/if*]
                                -->
                            [*/foreach*]
                        </div>
                    </div>
                </div>
                <a href="javascript:bbcode('[*$textarea_name|sl*]', 'img');"
                   title='[*'bbcode_code_img'|lang*]'
                   class="bbcode_img bbcodes"></a> <a
                   title='[*'bbcode_code_url'|lang*]'
                   href="javascript:bbcode('[*$textarea_name|sl*]', 'url');"
                   class="bbcode_url bbcodes bbcodes_border"></a>
            </div>
        </div>
        <div class="slide bbcodes_slides">
            <div class="bbcodes"> <a
                    title='[*'bbcode_code_hide'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'hide');"
                    class="bbcode_hide bbcodes"></a> <a
                    title='[*'bbcode_code_spoiler'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'spoiler');"
                    class="bbcode_spoiler bbcodes bbcodes_border"></a> <a
                    title='[*'bbcode_code_code'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'code');"
                    class="bbcode_code bbcodes"></a> <a
                    title='[*'bbcode_code_php'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'code', 'php');"
                    class="bbcode_code_php bbcodes"></a> <a
                    title='[*'bbcode_code_html'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'code', 'html');"
                    class="bbcode_code_html bbcodes"></a> <a
                    title='[*'bbcode_code_css'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'code', 'css');"
                    class="bbcode_code_css bbcodes"></a> <a
                    title='[*'bbcode_code_js'|lang*]'
                    href="javascript:bbcode('[*$textarea_name|sl*]', 'code', 'js');"
                    class="bbcode_code_js bbcodes"></a></div>
        </div>
    </div>
    <div class='wysiwyg_editor hidden'>
        <iframe src="javascript:void(0);" id="wysiwyg_[*$textarea_name*]"></iframe>
    </div>
    <div class="bbcode_editor">
        <textarea rows="10" cols="58" name="[*$textarea_rname*]" id='textarea_[*$textarea_name*]'>[*$textarea_text*]</textarea>
    </div>
</div>