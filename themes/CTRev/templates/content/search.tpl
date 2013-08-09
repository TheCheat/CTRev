<form method="post" action="[*gen_link module='search'*]" name='search_form'>
    <input type="hidden" name="auto" value="1">
    <div class="cornerText gray_color gray_border">
        <fieldset><legend>[*'search_main_data'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'search_keywords'|lang*]</dt>
                <dd><input type="text" name="query" value="[*$search*]"></dd>
                <dt>[*'search_author'|lang*]</dt>
                <dd><input type="text" name="author" value="[*$author*]"><br>                    
                    [*if 'usearch'|perm*]
                        <a href="javascript:open_searchuwind('search_form', 'author');">[*'search_usearch'|lang*]</a>
                    [*/if*]
                </dd>
            </dl>
            <center><font size="1">[*'search_keywords_notice'|lang*]</font></center>
        </fieldset>
    </div>
    <br>
    <div class="cornerText gray_color gray_border">
        <fieldset><legend>[*'search_addin_params'|lang*]</legend>
            <dl class="info_text">
                [*if "torrents_on"|config*]
                    <dt>[*'search_status'|lang*]</dt>
                    <dd><select name='status'>
                            <option value='0'>---[*'nothing'|lang*]---</option>
                            <option value='unchecked'>[*'torrent_status_unchecked'|lang*]</option>
                            [*foreach from=$statuses item='null' key='status'*]
                                <option value='[*$status*]'>[*"torrent_status_$status"|lang*]</option>
                            [*/foreach*]
                        </select>
                    </dd>
                [*/if*]
                <dt>[*'search_category'|lang*]</dt>
                <dd class='content_search_categories'>
                    [*select_categories null=true*]<br>
                </dd>
                <dt>[*'search_in'|lang*]</dt>
                <dd><input type="radio" name="search_in" value="0" checked="checked">&nbsp;[*'search_in_title_body'|lang*]<br>
                    <input type="radio" name="search_in" value="1">&nbsp;[*'search_in_title'|lang*]<br></dd>
                <dt>[*'search_add_date'|lang*]</dt>
                <dd>[*'search_from'|lang*]&nbsp;&nbsp;[*select_date name="from" null=true*]<br>
                    [*'search_to'|lang*]&nbsp;&nbsp;[*select_date name="to" null=true*]</dd>
                <dt>[*'search_orderby'|lang*]</dt>
                <dd><select name="orderby">
                        [*foreach from=$orderby_types item=type*]
                            <option value="[*$type*]">[*"search_orderby_$type"|lang*]</option>
                        [*/foreach*]
                    </select><input type="radio" value="asc" name="orderby_type"
                                    checked="checked">&nbsp;[*'search_asc'|lang*] <input type="radio"
                                    value="desc" name="orderby_type">&nbsp;[*'search_desc'|lang*]</dd>
            </dl>
            <hr class="gray_border">
            <center><input type="submit" value="[*'search'|lang*]!"></center>
        </fieldset>
    </div>
</form>