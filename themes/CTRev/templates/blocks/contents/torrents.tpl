[*if !$res*]
    [*message lang_var='btorrents_no_torrents_here' type='info'*]
[*else*]
    <div class='content'>
        <div class='tr'>
            [*foreach from=$res item='row' key='num'*]
                [*if $num % 2 == 0*]
                </div><div class='tr'>
                [*/if*]
                <div class='td torrent_element_container' align='center'>
                    <div class='torrent_element cornerText js_tablerow_height'>
                        <div class='torrent_element_content content'>
                            <div class='tr'>
                                <div class='td torrent_element_poster'>
                                    [*$row.screenshots*]
                                </div>
                                <div class='td torrent_element_descr'>
                                    <b>[*'btorrents_orig_name'|lang*]</b>
                                    [*if $row.orig_name*]
                                        [*$row.orig_name*]
                                    [*else*]
                                        [*'btorrents_unknown'|lang*]
                                    [*/if*]<br>   
                                    <b>[*'btorrents_year'|lang*]</b>
                                    [*if $row.year*]
                                        [*$row.year*]
                                    [*else*]
                                        [*'btorrents_unknown'|lang*]
                                    [*/if*]<br>
                                    <b>[*'btorrents_size'|lang*]</b> [*$row.size|cs*]<br>
                                    <b>[*'btorrents_seeders'|lang*]</b> [*$row.seeders*]<br>
                                    <b>[*'btorrents_leechers'|lang*]</b> [*$row.leechers*]<br>
                                    <b>[*'btorrents_author'|lang*]</b> [*$row.username|gcl:$row.group*]<br>
                                    <b>[*'btorrents_added'|lang*]</b> [*date time=$row.posted_time*]
                                </div>
                            </div>
                        </div>
                        <div class='torrent_element_name'>
                            <a href='[*gen_link module='torrents' id=$row.id title=$row.title*]'>
                                [*$row.name*]
                            </a>
                        </div>
                    </div>
                </div>
            [*/foreach*]
            [*if $num % 2 == 0*]
                <div class='td torrent_element_container'>&nbsp;</div>
            [*/if*]
        </div>
    </div>
[*/if*]
<script type='text/javascript'>
    init_corners();
    element_tablerow_height();
</script>
[*include file='sexy_lightbox.tpl'*]