</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="bottom_shadow"></div>
<div class="lb_corner"></div>
<div class="rb_corner"></div>
</div>
[*include file='loading_container.tpl'*]
</div>
<div class="footer"><!-- Это копирайт данного продукта. Удалять или изменять его строго запрещается!
It`s a copyright of this product. You can`t change and delete it! -->
    <hr>
    [*$copyright*]
</div>
<script type='text/javascript'>
    $admin_resizer = function () {
        jQuery('div.body_div').css('min-height', '100%');
        jQuery('div.body_div').css('min-height', (jQuery(window).height()-125)+"px"); // Тут проще так, чем через CSS
    }
    jQuery(document).ready($admin_resizer).resize($admin_resizer);
</script>
</body>
</html>