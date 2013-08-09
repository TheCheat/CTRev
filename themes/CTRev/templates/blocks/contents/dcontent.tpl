<script type="text/javascript">
    function click_downm_content(sticky, obj) {
        jQuery.post("index.php?module=downm&act=content&sticky=" + sticky + "&from_ajax=1",
                function(data) {
                    var sobj = jQuery(obj).parent("p").parent("div");
                    sobj.empty();
                    sobj.append(data);
                });
    }
</script>
<p><a href="javascript:void(0);" onclick="click_downm_content(1, this)"[*if $sticky*] class="subtabs_selected"[*/if*]>
        <b>[*'downm_content_sticky'|lang*]</b>
    </a>|
    <a href="javascript:void(0);" onclick="click_downm_content(0, this)"[*if !$sticky*] class="subtabs_selected"[*/if*]>
        <b>[*'downm_content_all'|lang*]</b>
    </a>
</p>