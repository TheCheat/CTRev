<form action='[*$admin_file|uamp*]&amp;act=search&amp;results=1' method='post'>
    <div class='cornerText gray_border gray_color'>
        <fieldset>
            <legend>[*'languages_search'|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'languages_search_id'|lang*]</dt>
                <dd>[*select_folder name='id' folder=$smarty.const.LANGUAGES_PATH current='default_lang'|config*]</dd>
                <dt>[*'languages_search_what'|lang*]</dt>
                <dd><input type='text' size='50' name='search'><br>
                    <input type='checkbox' name='regexp' value='1'>[*'languages_search_regexp'|lang*]
                </dd>
                <dt>[*'languages_search_where'|lang*]</dt>
                <dd>
                    <input type='radio' value='0' name='where' checked='checked'>[*'languages_search_in_values'|lang*]
                    <input type='radio' value='1' name='where'>[*'languages_search_in_keys'|lang*]
                    <input type='radio' value='2' name='where'>[*'languages_search_in_all'|lang*]
                </dd>
            </dl>
            <div align='center'>
                <input type='submit' value='[*'search'|lang*]'>
            </div>
        </fieldset>
    </div>
</form>