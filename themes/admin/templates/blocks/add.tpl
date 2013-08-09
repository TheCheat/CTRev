<form action='[*$admin_file|uamp*]&amp;act=save' method='post'>
    <input type='hidden' name='id' value='[*$id*]'>
    <div class='cornerText gray_color2'>
        <fieldset>
            [*if $id*]
                [*assign var='a' value="edit"*]
            [*else*]
                [*assign var='a' value="add"*]
            [*/if*]
            <legend>[*"blocks_block_$a"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'blocks_area_name'|lang*]</dt>
                <dd><input type='text' name='title' value='[*$row.title*]' size='35'></dd>
                <dt>[*'blocks_area_file'|lang*]</dt>
                <dd>[*$row.file|files_selector:"file"*]</dd>
                <dt>[*'blocks_area_type'|lang*]</dt>
                <dd>[*simple_selector name='type' current=$row.type values=$types lang_prefix='blocks_block_type_'*]</dd>
                <dt>[*'blocks_area_tpl'|lang*]</dt>
                <dd>[*$row.tpl|files_selector:"tpl"*]</dd>
                <dt>[*'blocks_area_module'|lang*]</dt>
                <dd>[*$modules_selector*]</dd>
                <dt>[*'blocks_area_group_allowed'|lang*]</dt>
                <dd>[*select_groups name='group_allowed' guest=true size=4 null=true current=$row.group_allowed*]</dd>
                <dt>[*'blocks_area_enabled'|lang*]</dt>
                <dd><input type='checkbox' name='enabled' value='1'[*if $row.enabled || !$row*] checked='checked'[*/if*]></dd>
            </dl>
            [*if $bsetting_manager*]                
                <fieldset><legend>[*'blocks_parameters'|lang*]</legend>
                    [*$bsetting_manager*]
                </fieldset>
                <br>
            [*/if*]
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>