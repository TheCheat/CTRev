<div class="cornerText import_window">
    <div id='import_data'>
    </div>
</div>
<div class='import_loading'></div>
<script type='text/javascript'>
    jQuery(document).ready(function () {
        switch_buttons(true);
        continue_convert(0, 0);
    });
    function continue_convert(toffset, loffset, finish) {
        jQuery.post('convert.php?page=convert&convert=1'+(finish?"&finish=1":''), {'toffset':toffset,'loffset':loffset},
        function (data) {
            jQuery('#import_data').append(data);
            jQuery('div.import_window').scrollTop(jQuery('#import_data').height());
        });
    }
    function stop_loading() {
        jQuery('div.import_loading').hide();
        switch_buttons();
    }
</script>