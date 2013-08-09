<script type='text/javascript'>init_tablesorter();</script>
[*if !$trackers*]
    [*'content_torrent_peers_no_stat'|lang*]
[*else*]
    <table class="tablesorter">
        <thead>
            <tr>
                <th>[*'content_torrent_url'|lang*]</th>
                <th>[*'content_torrent_stat'|lang*]</th>
            </tr>
        </thead>
        <tbody>
            [*foreach from=$trackers key="tracker" item="stat"*]
                <tr>
                    <td>[*$tracker|cut_tracker*]</td>
                    <td>
                        [*if is_array($stat)*]
                            [*'content_torrent_seedlech'|pf:$stat[0]:$stat[1]*]
                        [*else*]
                            [*'content_torrent_peers'|pf:$stat*]
                        [*/if*]
                    </td>
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*/if*]