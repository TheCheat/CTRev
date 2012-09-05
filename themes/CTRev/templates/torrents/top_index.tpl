<div class='info_torrent'>

    [*display_polls toid=$torrents.id*]<br>

    <div class='cornerText gray_color2 gray_border2'>
        <fieldset><legend>[*'torrents_details'|lang*] "[*$torrents.title*]"</legend>
            <center><b><font size='3'>
                        [*include file='torrents/status.tpl'*]
                    </font></b></center>
            <dl class='info_text'>
                [*if $curuser && 'torrents'|perm && !$torrents.banned*]
                    <dt>[*'torrents_details_download'|lang*]</dt>
                    <dd><a href="[*gen_link module='download' id=$torrents.id
                               no_end=true*].torrent"><b>[*'torrents_details_download_butt'|lang*]&nbsp;([*$torrents.size|cs*])</b></a><br>
                        [*assign var='gfree' value='0'*]
                        [*if 'free'|perm:2*]
                            [*assign var='gfree' value='100'*]
                        [*elseif 'free'|perm:1*]
                            [*assign var='gfree' value='50'*]
                        [*/if*]
                        [*assign var='bcount' value='bonus_count'|user*]
                        [*'torrents_details_price'|pf:$torrents.price:$gfree:$bcount*]<br><br>
                        [*assign var='link' value='usercp'|genlink*]
                        <font size='1'>[*'torrents_details_pk'|pf:$link*]</font>
                    </dd>
                [*/if*]
                <dt>[*'torrents_details_infohash'|lang*]</dt>
                <dd>[*$torrents.info_hash*]</dd>
                <dt>[*'torrents_details_sld'|lang*]</dt>
                <dd><a href="javascript:void(0);" onclick="open_spoiler(this);" class="spoiler_icon"></a>&nbsp;&nbsp;[*$torrents.seeders*]&nbsp;/&nbsp;[*$torrents.leechers*]&nbsp;/&nbsp;[*$torrents.downloaded*]<br>
                    <div class="spoiler_content hidden">
                        <b>[*'torrents_details_seeders'|lang*]</b>
                        [*if $torrents.seeders_t*][*$torrents.seeders_t*]
                        [*else*][*'torrents_details_nobody'|lang*]
                        [*/if*]<br>
                        <b>[*'torrents_details_leechers'|lang*]</b>
                        [*if $torrents.leechers_t*][*$torrents.leechers_t*]
                        [*else*][*'torrents_details_nobody'|lang*]
                        [*/if*]<br>
                        <b>[*'torrents_details_downloaders'|lang*]</b>
                        [*if $torrents.downloaders_t*][*$torrents.downloaders_t*]
                        [*else*][*'torrents_details_nobody'|lang*]
                        [*/if*]<br>
                    </div>
                </dd>
                <dt>[*'torrents_details_filelist'|lang*]</dt>
                <dd><a href="javascript:void(0);" onclick="open_spoiler(this);" class="spoiler_icon"></a><br>
                    <div class="spoiler_content hidden">
                        <table class="tablesorter">
                            <thead>
                                <tr>
                                    <th>[*'torrents_file_name'|lang*]</th>
                                    <th>[*'torrents_file_size'|lang*]</th>
                                </tr>
                            </thead>
                            <tbody>
                                [*foreach from=$torrents.filelist item="file"*]
                                    <tr>
                                        <td>[*$file[0]*]</td>
                                        <td>[*$file[1]|cs*]</td>
                                    </tr>
                                [*/foreach*]
                            </tbody>
                        </table>
                    </div>
                </dd>
                [*if $torrents.tags*]
                    <dt>[*'torrents_details_tags'|lang*]</dt>
                    <dd>[*$torrents.tags*]</dd>
                [*/if*]
                <dt>[*'torrents_details_multitrack'|lang*]</dt>
                <dd><a href="javascript:void(0);" onclick="open_spoiler(this);load_peers(this, '[*$torrents.id*]');"
                       class="spoiler_icon"></a><br>
                    <div class="spoiler_content hidden"></div>
                </dd>
            </dl>
        </fieldset>
    </div>
</div>
<br>