<script type="text/javascript">
    function remove_message($id, $div, $param, $read) {
        if (!$read) {
            if (!confirm("[*'pm_are_you_sure_to_delete'|lang|sl*]"))
            return;
        }
        jQuery.post("[*$BASEURL|sl*]index.php?[*fk ajax=1*]module=messages&from_ajax=1&act="+($read?"s_read":"delete"), {"item":$id}, function (data) {
            if (data=="OK!") {
                if (!jQuery.isFunction($div)) {
                    if (!$div) {
                        jQuery("tr.item_"+$id).children("td").each(function () {
                            jQuery(this).fadeOut(2000, function (){
                                jQuery(this).parent("tr").remove();
                            });
                        });
                    } else {
                        jQuery("#item_"+$id).fadeOut(2000, function (){
                            jQuery("#item_"+$id).remove();
                            window.location = '[*gen_link module="pm" slashes=true*]';
                        });
                    }
                } else
                    $div($param);
                alert("[*'success'|lang|sl*]!");
            } else
                alert("[*'error'|lang|sl*]: "+data);
        });
    }
    function read_msg($id) {
        jQuery.post("[*$BASEURL|sl*]index.php?module=messages&from_ajax=1&act=read", {"id": $id}, function (data) {
            jQuery("div.body_messages").empty();
            jQuery("div.body_messages").append(data);
        });
    }
</script>