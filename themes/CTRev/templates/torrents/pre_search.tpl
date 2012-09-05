<font size="1">
    [*if $res*]
        <hr>
        [*foreach from=$res item=arr*] 
            <a href="[*gen_link module='torrents' id=$arr.id title=$arr.title*]"
               class="white_link">[*$arr.title*]</a>
            <hr>
        [*/foreach*] 
    [*else*]
        [*'search_pre_nothing_found'|lang*]
    [*/if*] 
</font>