<div class="cornerText import_window">
    <div id='import_data'>
    </div>
</div>
<div class='import_loading'></div>
<script type='text/javascript'>
    jQuery(document).ready(function () {
        switch_buttons(true);
        run_query(0);
    });
    function run_query(offset) {
        jQuery.get('install.php', {'page':'import','import':true,'offset':offset},
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