[*if !$content_in_block && !$full_content*]
    <div class="cat_content content_catlist">
        <div class="cornerText gray_color">
            <div class='content_catlist_header'>  
                <span class='content_catlist_title'>  
                    [*if $cat_rows && $curuser && "mailer_on"|config*]
                        <a href="javascript:make_mailer('[*$cat_rows[3]*]', 'category');"><img
                                src="[*$theme_path*]engine_images/email_add_small.png"
                                title="[*'content_add_cat_to_mailer'|lang*]" alt="[*'content_add_cat_to_mailer'|lang*]">
                        </a>
                    [*/if*]
                    <a href="[*gen_link module='content'*]" class='content_catlist_ah'>[*'content_categories'|lang*]</a>
                    [*if $cat_rows*]                        
                        &rightarrow;
                        [*if $content_catparents*]                            
                            [*foreach from=$content_catparents item="cat"*]
                                <a href="[*gen_link module='content' cat=$cat.transl_name*]">[*$cat.name*]</a> &rightarrow; 
                            [*/foreach*]
                        [*/if*]
                        <a href="[*gen_link module='content' cat=$cat_rows[2]*]">[*$cat_rows[0]*]</a>
                    [*/if*]
                </span>
                <div class='content_cat_icons'> 
                    <a href="[*gen_link module='content' cat=$cat_rows[2] act='rss'*]"><img
                            src="[*$theme_path*]engine_images/rss-feed.png"
                            title="[*'content_rss'|lang*]" alt="[*'content_rss'|lang*]">
                    </a>     
                    [*if 'content'|perm:2*]
                        <a href="[*gen_link module='content' cat=$cat_rows[2] act='add'*]"><img
                                src="[*$theme_path*]engine_images/add_small.png"
                                title="[*'content_add'|lang*]" alt="[*'content_add'|lang*]">
                        </a>
                    [*/if*]
                </div>
            </div>
            <div class='content_catlist_body'>
                [*if $content_cats*]
                    [*foreach from=$content_cats item="cat"*]
                        <span class='content_catlist_item'>
                            <a href="[*gen_link module='content' cat=$cat.transl_name*]">[*$cat.name*]</a>
                        </span>
                    [*/foreach*]
                    <span class='content_catlist_item'>&nbsp;</span>
                    <div class="br"></div>
                [*/if*]
                [*if $cat_rows*]
                    <div class='cat_content_descr'>
                        [*if $cat_rows[1]*]
                            [*$cat_rows[1]*]
                        [*elseif !$content_cats*]
                            [*'content_categories_no_descr_yet'|lang*]
                        [*/if*]
                    </div>
                [*/if*]
            </div>
        </div>
    </div>
[*/if*]

[*if !$content_row*]
    <div class="cat_content">
        <div class="cornerText gray_color">
            <div class="cat_content_name">[*'content_nothing'|lang*]</div>
            <div class="cat_content_descr">[*'content_nothing_descr'|lang*]
                [*if 'content'|perm:2*]
                    [*'content_nothing_descr_want'|lang*]
                    <a href="[*gen_link module='content' act='add'*]">
                        [*'content_nothing_descr_add'|lang*]</a>
                    [*/if*]
            </div>
        </div>
    </div>
[*/if*]