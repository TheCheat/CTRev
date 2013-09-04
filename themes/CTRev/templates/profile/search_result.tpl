[*if !$subupdate*]
    [*if $eadmin_file*]        
        [*include file='admin/user/massact.tpl'*]
    [*/if*]
    <br>
    <div class="cornerText gray_color gray_border">
    [*/if*]
    [*if $rows*]
        [*if !$subupdate*]
            <fieldset><legend>[*'search_result'|lang*]</legend>
                <table class="tablesorter">
                    <thead>
                        <tr>
                            <th class="js_remote">[*'usearch_area_username'|lang*]</th>
                            <th class="js_remote">[*'usearch_area_group'|lang*]</th>
                            <th class="js_remote">[*'usearch_area_content_count'|lang*]</th>
                            <!--
                            <th class="js_remote js_nosort">[*'usearch_area_website'|lang*]</th>
                            <th class="js_remote js_nosort">[*'usearch_area_town'|lang*]</th>
                            -->
                            <th class="js_remote">[*'usearch_area_registered'|lang*]</th>
                            <th class="js_remote">[*'usearch_area_last_visited'|lang*]</th>
                                [*if $eadmin_file*]
                                <th class="js_remote js_nosort">
                                    <input
                                        type="checkbox" title="[*'mark_this_all'|lang*]"
                                        onclick="select_all(this, 'input.marked_users')">
                                </th>
                            [*/if*]
                        </tr>
                    </thead>
                    <tbody id="subupdate_rows">
                    [*/if*] 
                    [*foreach from=$rows item=row*]
                        [*assign var='row' value=$row|@decus*]
                        <tr>
                            <td>
                                [*if $parented_window*]
                                    <a href='javascript:insert_intoparent("[*$row.username|sl*]")'>
                                        <img src='[*$theme_path*]engine_images/insert_small.png'
                                             alt='[*'insert'|lang*]' title='[*'insert'|lang*]'>
                                    </a>
                                [*/if*]
                                [*$row.username|gcl:$row.group*]
                                [*if $eadmin_file && ('system'|perm || !($row.group|gval:'system'))*]
                                    <a href='[*$eadmin_file*]&amp;item=users&amp;module=users&amp;act=edit&amp;id=[*$row.id*]'>
                                        <img src='[*$theme_path*]engine_images/edit.png' 
                                             alt='[*'edit'|lang*]' height='12'>
                                    </a>
                                [*/if*]
                            </td>                            
                            <td>[*$row.group|gc*]</td>
                            <td>[*$row.content_count*]</td>
                            <!--
                            <td>
                            [*if $row.website*]
                                <a href="[*$row.website*]">[*$row.website*]</a>
                            [*else*]
                                <b>-</b>
                            [*/if*]
                        </td>
                        <td>
                            [*if $row.town*]
                                [*$row.town*]
                            [*else*]
                                <b>-</b>
                            [*/if*]
                        </td>
                            -->
                            <td>[*date time=$row.registered format='ymdhis'*]</td>
                            <td>[*date time=$row.last_visited format='ymdhis'*]</td>
                            [*if $eadmin_file*]
                                <td><input type="checkbox" name="item[]" value="[*$row.id*]"
                                           title="[*'mark_this'|lang*]"
                                           class="marked_users"></td>
                                [*/if*]
                        </tr>
                    [*/foreach*]
                    [*if !$subupdate*]
                    </tbody>
                </table>
                [*if $eadmin_file*]        
                    [*include file='admin/user/actions.tpl'*]
                [*/if*]
                <br>
                [*$pages*]
            </fieldset>
        [*/if*] 
    [*else*]
        [*gen_link module='search' act='users' assign='link'*]
        [*message type='info' vars=$link lang_var='search_nothing_found'*] 
    [*/if*]
    [*if !$subupdate*]
    </div>
[*/if*]