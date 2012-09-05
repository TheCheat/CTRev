[*assign var="users" value=0*]
[*assign var="hidden" value=0*]
[*assign var="guests" value=0*]
[*assign var="be" value=0*]
[*assign var="intrvl" value='online_interval'|config|ge*]
<img src="[*$theme_path*]engine_images/online.png" alt="">&nbsp;[*'downm_whos_online'|pf:$intrvl*]
<p>
    [*foreach from=$res item="row"*]
        [*assign var='row' value=$row.userdata|unserialize*]
        [*if $row.username && (!$row.hidden || 'hiddenu'|perm || ($row.id=='id'|user && 'id'|user>0 && $row.username))*]
            [*if $be*]
                ,
            [*/if*]
            [*if $row.hidden*]
                <i>
                [*/if*]
                [*$row.username|gcl:$row.group*]
                [*if $row.hidden*]
                </i>
            [*/if*]
            [*assign var="be" value=1*]
        [*/if*]
        [*if $row.hidden*]
            <!--[*$hidden++*]-->
        [*/if*]
        [*if $row.username*]
            <!--[*$users++*]-->
        [*else*]
            <!--[*$guests++*]-->
        [*/if*]
    [*/foreach*]
    <br>
    [*'downm_at_all'|pf:$hidden+$users+$guests:$users:$hidden:$guests*]
    [*if $record_total*]
        <br><br>
        <i>[*'downm_record'|pf:$record_total*][*date time=$record_time*]</i>
    [*/if*]
</p>
<hr style="border-style: dashed; margin:0 15px;"><br>
<img src="[*$theme_path*]engine_images/birthday.png" alt="">&nbsp;<b>[*'downm_bd_title'|lang*]</b>
<p>
    [*if $bdl*]
        [*assign var="be" value=0*]
        [*foreach from=$bdl item=row*]
            [*if $be*]
                ,
            [*/if*]
            [*if $row.gender=="f"*]
                <img src="[*$theme_path*]engine_images/female.png"
                     alt="[*'users_female'|lang*]">
            [*else*]
                <img src="[*$theme_path*]engine_images/male.png"
                     alt="[*'users_male'|lang*]">
            [*/if*]
            [*$row.username|gcl:$row.group*]([*$row.birthday|gau*])
            [*assign var="be" value=1*]
        [*/foreach*]
    [*else*]
        [*'downm_bd_nobody_celebrates'|lang*]
    [*/if*]
</p>