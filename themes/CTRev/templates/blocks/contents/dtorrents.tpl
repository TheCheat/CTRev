<script type="text/javascript">
    function click_downm_torrents(sticky, obj) {
        jQuery.post("[*$BASEURL*]index.php?module=downm&act=torrents&sticky="+sticky+"&from_ajax=1",
        function (data) {
            var sobj = jQuery(obj).parent("p").parent("div");
            sobj.empty();
            sobj.append(data);
        });
    }
</script>
<p><a href="javascript:void(0);" onclick="click_downm_torrents(1, this)"[*if $sticky*] class="subtabs_selected"[*/if*]>
        <b>[*'downm_torrents_sticky'|lang*]</b>
    </a>|
    <a href="javascript:void(0);" onclick="click_downm_torrents(0, this)"[*if !$sticky*] class="subtabs_selected"[*/if*]>
        <b>[*'downm_torrents_all'|lang*]</b>
    </a>
</p>