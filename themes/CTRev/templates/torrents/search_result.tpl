<div id="to_update_page">
    <script type="text/javascript">
        function change_spage(to_page) {
            var $post = '[*$post|sl*]';
            var $get = '[*$get|sl*]';
            jQuery.post('[*$baseurl|sl*]index.php?module=search_module&nno=1&from_ajax=1&page='+to_page+
                ($get?'&'+$get:''), $post, function (data) {
                jQuery("#to_update_page").empty();
                jQuery("#to_update_page").append(data);
            });
        }
    </script>
    [*assign var="link" value="search"|genlink*]
    <div class="cornerText white_color gray_border">
        [*if $rows*]
            <i>[*'search_result'|lang*]</i><br>
            <div class='torrents_search_results'>
                [*foreach from=$rows key=num item=row*] 
                    [*if $num*]
                        <br>
                    [*/if*]
                    <div class="cornerText gray_color gray_border">
                        [*$row.category_id|print_cats*]:
                        <a href="[*gen_link module='torrents' title=$row.title id=$row.id*]">
                            <font size="3"><b>[*$row.title*]</b></font>
                        </a>
                        <hr class="short">
                        <div class="content">
                            [*$row.screenshots|show_image:true*]
                            [*$row.content|ft:false:true*]
                        </div>
                        <hr class="short">
                        <b>[*'search_tauthor'|lang*]</b> [*$row.username|gcl:$row.group*], <b>[*'search_added'|lang*]</b>
                        [*date time=$row.posted_time*], <b>[*'search_comments'|lang*]</b> [*$row.comm_count*],
                        <b>[*'search_seedleech'|lang*]</b> [*$row.seeders*]&nbsp;/&nbsp;[*$row.leechers*]
                        <div class="float_right">
                            <a href="[*gen_link module='torrents' title=$row.otitle id=$row.id*]">[*'search_go_to_torrent'|lang*]</a>
                        </div>
                    </div>
                [*/foreach*]
                <br>
                [*$pages*]
                <br>
                <hr class="short">
                <b><i>[*'search_not_this'|pf:$link*]</i></b>
            </div>
        [*else*]
            [*message lang_var="search_nothing_found" type="info" vars=$link*]
        [*/if*]
    </div>
</div>    
[*if $from_ajax*]
    <script type='text/javascript'>        
        if (typeof init_sexylightbox != "undefined")
            init_sexylightbox();
    </script>
[*/if*]