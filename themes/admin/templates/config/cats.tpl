<div class='cornerText gray_color2'>
    <fieldset><legend>[*'config_title'|lang*]</legend>
        [*foreach from=$rows item="row"*]
            [*assign var='cat' value=$row.cat*]
            <div class='config_element'><b><a href='[*$admin_file|uamp*]&amp;type=[*$cat*]'>
                        [*"config_type_$cat"|lang*]</a></b>&nbsp;([*$row.c*] [*'config_settings'|lang*])</div>
                    [*/foreach*]
    </fieldset>
</div>