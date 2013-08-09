<br>
<div align="[*if $inusercp*]left[*else*]right[*/if*]">
    <b>[*if $inusercp*][*'actions'|lang*][*else*][*'usearch_actions_with'|lang*][*/if*]:</b>&nbsp; <select
        name="actions" id="actions_with_users">
    [*if $unco*]<option value="confirm">[*'confirm'|lang*]</option>[*/if*]
    <option value="ban">[*'usearch_act_ban'|lang*]</option>
    <option value="unban">[*'usearch_act_unban'|lang*]</option>
    [*if !$inusercp && 'system'|perm*]
        <option value="change_group">[*'usearch_act_change_group'|lang*]</option>
    [*/if*]
    <option value="delete_content">[*'usearch_act_delete_their_content'|lang*]</option>
    <option value="delete">[*'delete'|lang*]</option>
</select>&nbsp;<input type="button" value="[*'run'|lang*]"
                      onclick="do_with_users('.marked_users', '#actions_with_users');"></div>
