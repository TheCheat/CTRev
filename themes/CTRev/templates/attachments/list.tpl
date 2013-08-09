[*if !$from_ajax*]
    <script type='text/javascript'>
        function remove_attach_splitter() {
            jQuery('.attachlist:first span').remove();
        }
        function attach_add(data, postfix, fileObj) {
            if (is_ok(data, true)) {
                var $nwobj = jQuery(".attachments_" + postfix + ">.attachment0").clone();
                $nwobj.removeClass("attachment0").removeClass("hidden");
                var v = parseInt(cut_ok(data));
                $nwobj.addClass('attachlist');
                jQuery('input[type="hidden"]', $nwobj).val(v);
                $nwobj.prepend(" <span>|</span> " + fileObj.name);
                $nwobj.appendTo(".attachments_" + postfix);
                remove_attach_splitter();
            } else {
                alert(error_text + ': ' + data);
            }
        }
        function attach_delete(obj) {
            var $obj = jQuery(obj).parents("span");
            var $attach_class = "attachment";
            var $class = $obj.attr("class");
            var $id = parseInt(jQuery('input[type="hidden"]', $obj).val());
            jQuery.post("index.php?module=attach_manage&act=delete&from_ajax=1", {
                "id": $id
            }, function(data) {
                if (is_ok(data)) {
                    $obj.remove();
                    remove_attach_splitter();
                    //alert(success_text + "!");
                } else
                    alert(error_text + "!");
            });
        }
    </script>
[*/if*]
<div class="attachments_[*$postfix_att*]">
    <span class="attachment0 hidden">
        <nobr>
            <input type="hidden" name="attachments[]" value="0">
            <a href="javascript:void(0);" onclick="attach_delete(this);">
                <img src="[*$theme_path*]engine_images/delete.png" height='12' alt="[*"delete"|lang*]">
            </a>
        </nobr>
    </span>
    [*if $attachments*]
        [*foreach from=$attachments item=row key=num*] 
            <span class="attachlist">
                <nobr>
                    [*if $num*] 
                        <span>|</span> 
                    [*/if*] 
                    <input type="hidden" name="attachments[]" value="[*$row.id*]">
                    [*$row.filename*]
                    <a href="javascript:void(0);" onclick="attach_delete(this);">
                        <img src="[*$theme_path*]engine_images/delete.png" height='12' alt="[*"delete"|lang*]">
                    </a>
                </nobr>
            </span>
        [*/foreach*]
    [*/if*]
</div>
<br>