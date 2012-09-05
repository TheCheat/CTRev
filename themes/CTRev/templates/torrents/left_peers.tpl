<script type='text/javascript'>init_tablesorter();</script>
[*if !$trackers*]
    [*'torrents_details_peers_no_stat'|lang*]
[*else*]
    <table class="tablesorter">
        <thead>
            <tr>
                <th>[*'torrents_details_url'|lang*]</th>
                <th>[*'torrents_details_stat'|lang*]</th>
            </tr>
        </thead>
        <tbody>
            [*foreach from=$trackers key="tracker" item="stat"*]
                <tr>
                    <td>[*$tracker|cut_tracker*]</td>
                    <td>
                        [*if is_array($stat)*]
                            [*'torrents_details_seedlech'|pf:$stat[0]:$stat[1]*]
                        [*else*]
                            [*'torrents_details_peers'|pf:$stat*]
                        [*/if*]
                    </td>
                </tr>
            [*/foreach*]
        </tbody>
    </table>
[*/if*]