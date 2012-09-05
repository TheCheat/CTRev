<dl class="info_text">
    <dt>[*'users_bonus_count'|lang*]</dt>
    <dd>[*$row.bonus_count*]</dd>
    <dt>[*'users_added_torrents'|lang*]<br>
        <font size="1">
            <a href="[*gen_link module='search' author=$row.username auto=true*]">[*'users_all_added_torrents'|lang*]</a>
        </font>
    </dt>
    <dd>[*$row.torrents_count*]<br>&nbsp;
    </dd>
    <dt>[*'users_added_comments'|lang*]</dt>
    <dd>[*$row.comm_count*]</dd>
</dl>
[*if $row.signature*]
    <dl>
        <dt class="top_bordered"><b>[*'users_signature'|lang*]</b></dt>
        <dd>[*$row.signature|ft*]</dd>
    </dl>
[*/if*]
