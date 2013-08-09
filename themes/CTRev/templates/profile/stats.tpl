<dl class="info_text">
    [*if "torrents_on"|config*]
        <dt>[*'users_bonus_count'|lang*]</dt>
        <dd>[*$row.bonus_count*]</dd>
    [*/if*]
    <dt>[*'users_added_content'|lang*]<br>
    <font size="1">
    <a href="[*gen_link module='search' author=$row.username auto=true*]">[*'users_all_added_content'|lang*]</a>
    </font>
    </dt>
    <dd>[*$row.content_count*]<br>&nbsp;
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
