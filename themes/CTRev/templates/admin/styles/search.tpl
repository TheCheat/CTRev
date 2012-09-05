<form action='[*$admin_file|uamp*]&amp;act=search&amp;results=1' method='post'>
    <div class='cornerText gray_border gray_color'>
        <fieldset>
            <legend>[*'styles_search'|lang*]</legend>
            <dl class='info_text'>
                <dt>[*'styles_search_id'|lang*]</dt>
                <dd>[*select_folder name='id' folder=$smarty.const.THEMES_PATH current='default_style'|config*]</dd>
                <dt>[*'styles_search_what'|lang*]</dt>
                <dd><input type='text' size='55' name='search'><br>
                    <input type='checkbox' name='regexp' value='1'>[*'styles_search_regexp'|lang*]
                </dd>
                <dt>[*'styles_search_where'|lang*]</dt>
                <dd>[*simple_selector name='where' values=$apaths keyed=true current=$smarty.const.TEMPLATES_PATH size='3'*]</dd>
            </dl>
            <div align='center'>
                <input type='submit' value='[*'search'|lang*]'>
            </div>
        </fieldset>
    </div>
</form>