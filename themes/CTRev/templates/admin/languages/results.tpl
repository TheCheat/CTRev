[*if !$row*]
    [*message lang_var='languages_search_nothing_found' type='info' die=1*]                    
[*/if*]
<script type='text/javascript'>
    function replace_langvars(form) {
        var post = jQuery(form).serialize();
        var wth = prompt('[*'languages_search_replace_with'|lang*]');
        post += '&with='+urlencode(wth);
        post = '[*$postdata|sl*]&id=[*$id|sl*]&'+post;
        jQuery.post('[*$admin_file|sl*]&from_ajax=1&act=replace', post, function (data) {
            if (data=='OK!')
                window.location = '[*$admin_file|sl*]';
            else
                alert(error_text+':'+data);
        });
    }
</script>
<form action='javascript:void(0);' onsubmit="replace_langvars(this)">
    <div class='cornerText gray_color gray_border'>
        <fieldset>
            <legend>
                <input type='checkbox' checked='checked' 
                       onclick='select_all(this, "input.files_check2replace")'
                       title='[*'languages_search_replace_selected'|lang*]'>
                [*'languages_search_results'|lang*]&nbsp;&nbsp;&bull;&nbsp;&nbsp;
                <a href='javascript:history.go(-1);'>[*'back'|lang*]</a></legend>
            <ul class='sortable'>
                [*foreach from=$row item='r' key='file'*]
                    <li class='sortable_disabled'>
                        <input type='checkbox' name='files[]' value='[*$file*]' checked='checked'
                               class='files_check2replace' title='[*'languages_search_replace_selected'|lang*]'>
                        [*'languages_search_file'|pf:$file:$id*]                    
                        <a href='[*$admin_file|uamp*]&amp;act=edit&amp;id=[*$id*]&amp;file=[*$file|ue*]'>
                            <img src="[*$theme_path*]engine_images/edit.png" width='12'
                                 alt="[*'edit'|lang*]" title="[*'edit'|lang*]" align='right'>
                        </a>
                    </li>
                    [*foreach from=$r key='key' item='value'*]
                        <li class='sortable_thin'>
                            <dl class='info_text'>
                                <dt>[*$key*]</dt>
                                <dd><code class='search_result'>[*$value*]</code></dd>
                            </dl>
                        </li>
                    [*/foreach*]
                [*/foreach*]
            </ul>
            <div align='center'>
                <input type='submit' value='[*'languages_search_replace_with'|lang*]'>
            </div>
        </fieldset>
    </div>
</form>