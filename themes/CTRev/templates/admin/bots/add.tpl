<form action='[*$admin_file|uamp*]&amp;act=save' method='post'>
    <input type='hidden' name='id' value='[*$id*]'>
    <div class='cornerText gray_color2'>
        <fieldset>
            [*if $id*]
                [*assign var='a' value="edit"*]
            [*else*]
                [*assign var='a' value="add"*]
            [*/if*]
            <legend>[*"bots_bot_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'bots_area_name'|lang*]</dt>
                <dd><input type='text' name='name' value='[*$row.name*]' size='35'></dd>
                <dt>[*'bots_area_firstip'|lang*]</dt>
                <dd><input type='text' name='firstip' value='[*$row.firstip|l2ip*]'></dd>
                <dt>[*'bots_area_lastip'|lang*]</dt>
                <dd><input type='text' name='lastip' value='[*$row.lastip|l2ip*]'></dd>
                <dt>[*'bots_area_agent'|lang*]</dt>
                <dd><input type='text' name='agent' value='[*$row.agent|he*]' size='55'></dd>
            </dl>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>