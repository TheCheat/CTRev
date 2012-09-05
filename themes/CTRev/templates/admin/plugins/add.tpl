<form action='[*$admin_file|uamp*]&amp;act=build' method='post'>
    <div class='cornerText gray_color2'>
        <fieldset>
            <legend>[*"plugins_create"|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'plugins_area_name'|lang*]</dt>
                <dd><input type='text' name='plugin' maxlength="30" size='35'><br>
                    <font size='1'>[*'plugins_area_name_descr'|lang*]</font></dd>
                <dt>[*'plugins_area_author'|lang*]</dt>
                <dd><input type='text' name='author' size='25'></dd>
                <dt>[*'plugins_area_version'|lang*]</dt>
                <dd><input type='text' name='version' value='1.00' size='7'></dd>
                <dt>[*'plugins_area_real_name'|lang*]</dt>
                <dd><input type='text' name='name' size='35'></dd>
                <dt>[*'plugins_area_descr'|lang*]</dt>
                <dd><textarea name='descr' rows='5' cols='35'></textarea></dd>
                <dt>[*'plugins_area_compatibility'|lang*]</dt>
                <dd>
                    <input type='text' name='comp_min' value='[*$smarty.const.ENGINE_VERSION*]' size='7'> &lt;=
                    <input type='text' name='comp' value='[*$smarty.const.ENGINE_VERSION*]' size='7'> &gt;=
                    <input type='text' name='comp_max' value='[*$smarty.const.ENGINE_VERSION*]' size='7'>
                </dd>
            </dl>
            [*modsettings_create*]
            <div align='center'><input type='submit' class='styled_button_big' 
                                       value='[*'plugins_make_and_download'|lang*]'></div>
        </fieldset>
    </div>
</form>