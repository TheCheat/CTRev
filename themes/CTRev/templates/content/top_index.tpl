<div class='info_content'>

    [*display_polls type='content' toid=$content.id*]<br>

    [*if "torrents_on"|config && $content.info_hash*]
        <div class='cornerText gray_color2 gray_border2'>
            <fieldset><legend>[*'content_item'|lang*] "[*$content.title*]"</legend>
                <center>
                    <b><font size='3'>
                        [*include file='content/status.tpl'*]
                        </font></b>
                </center>
                <dl class='info_text'>
                    [*if $curuser && 'content'|perm && !$content.banned*]
                        <dt>[*'content_torrent_download'|lang*]</dt>
                        <dd><a href="[*gen_link module='download' id=$content.id
                               no_end=true*].torrent"><b>[*'content_torrent_download_button'|lang*]&nbsp;([*$content.size|cs*])</b></a><br>
                            [*assign var='gfree' value='0'*]
                            [*if 'free'|perm:2*]
                                [*assign var='gfree' value='100'*]
                            [*elseif 'free'|perm:1*]
                                [*assign var='gfree' value='50'*]
                            [*/if*]
                            [*assign var='bcount' value='bonus_count'|user*]
                            [*'content_torrent_price_descr'|pf:$content.price:$gfree:$bcount*]<br><br>
                            [*assign var='link' value='usercp'|genlink*]
                            <font size='1'>[*'content_torrent_pk'|pf:$link*]</font>
                        </dd>
                    [*/if*]
                    <dt>[*'content_torrent_infohash'|lang*]</dt>
                    <dd>[*$content.info_hash*]</dd>
                    <dt>[*'content_torrent_sld'|lang*]</dt>
                    <dd><a href="javascript:void(0);" onclick="open_spoiler(this);" class="spoiler_icon"></a>&nbsp;&nbsp;[*$content.seeders*]&nbsp;/&nbsp;[*$content.leechers*]&nbsp;/&nbsp;[*$content.downloaded*]<br>
                        <div class="spoiler_content hidden">
                            <b>[*'content_torrent_seeders'|lang*]</b>
                            [*if $content.seeders_t*][*$content.seeders_t*]
                            [*else*][*'content_torrent_nobody'|lang*]
                            [*/if*]<br>
                            <b>[*'content_torrent_leechers'|lang*]</b>
                            [*if $content.leechers_t*][*$content.leechers_t*]
                            [*else*][*'content_torrent_nobody'|lang*]
                            [*/if*]<br>
                            <b>[*'content_torrent_downloaders'|lang*]</b>
                            [*if $content.downloaders_t*][*$content.downloaders_t*]
                            [*else*][*'content_torrent_nobody'|lang*]
                            [*/if*]<br>
                        </div>
                    </dd>
                    <dt>[*'content_torrent_filelist'|lang*]</dt>
                    <dd><a href="javascript:void(0);" onclick="open_spoiler(this);" class="spoiler_icon"></a><br>
                        <div class="spoiler_content hidden">
                            <table class="tablesorter">
                                <thead>
                                    <tr>
                                        <th>[*'content_torrent_file_name'|lang*]</th>
                                        <th>[*'content_torrent_file_size'|lang*]</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    [*foreach from=$content.filelist item="file"*]
                                        <tr>
                                            <td>[*$file[0]*]</td>
                                            <td>[*$file[1]|cs*]</td>
                                        </tr>
                                    [*/foreach*]
                                </tbody>
                            </table>
                        </div>
                    </dd>
                    [*if $content.tags*]
                        <dt>[*'content_tags_details'|lang*]</dt>
                        <dd>[*$content.tags*]</dd>
                    [*/if*]
                    <dt>[*'content_torrent_multitrack'|lang*]</dt>
                    <dd><a href="javascript:void(0);" onclick="open_spoiler(this);
                            load_peers(this, '[*$content.id*]');"
                           class="spoiler_icon"></a><br>
                        <div class="spoiler_content hidden"></div>
                    </dd>
                </dl>
            </fieldset>
        </div>
    [*/if*]
</div>
<br>