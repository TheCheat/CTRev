<script type='text/javascript'>    
    function change_cattype(val) {
        window.location = '[*$oldadmin_file|sl*]&type='+val;
    }
</script>
<div class='padding_left'><b>[*'cats_choose_type'|lang*]</b>
    <select name='cat_type' onchange="change_cattype(this.value);">
        [*foreach from=$cat_types item='type'*]
            <option value='[*$type*]'[*if $type==$cat_type*] 
                    selected='selected'[*/if*]>[*"cats_types_$type"|lang*]</option>
        [*/foreach*]
    </select>
</div>
<br>