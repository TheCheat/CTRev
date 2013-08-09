[*foreach from=$allowed_colors item='color' key='num'*]
    <div class='theme_color_selector [*if $curtheme_color==$color*] theme_color_selected[*/if*]'
         style='background-color: [*if $display_colors[$num]*][*$display_colors[$num]*][*else*][*$color*][*/if*];' onclick='change_theme_color(this);'>
        [*$color*]
    </div>
[*/foreach*]
<input type='hidden' name='theme_color' value='[*$curtheme_color*]'>
<script type='text/javascript'>
             function change_theme_color(obj) {
                 obj = jQuery(obj);
                 var color = trim(obj.text());
                 jQuery('input[name=theme_color]').val(color);
                 jQuery('.theme_color_selector').removeClass('theme_color_selected');
                 obj.addClass('theme_color_selected');
             }
</script>