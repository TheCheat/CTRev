[*if !$rating_inited*]
    <script type="text/javascript" src="js/jquery.MetaData.js"></script>
    <script type="text/javascript" src="js/jquery.rating.js"></script>
[*/if*]
<div class="ratingbar">
    [*section name="rating" start=$min loop=$loop step=$per*] 
        <input type="radio"
               [*if $disabled*] 
                   title="[*'rating_total'|pf:$total:$count*]"
               [*/if*]
               class="hidden rating[*$subratingid*][*$rtoid*][*$rtype*][*if $split*] {split:[*$split*]}[*/if*]"
               value="[*$smarty.section.rating.index*]"
               [*if $smarty.section.rating.index==$total*] 
                   checked="checked"
               [*/if*]>
    [*/section*]
</div>
<script type="text/javascript">
    jQuery("input.rating[*$subratingid|sl*][*$rtoid|sl*][*$rtype|sl*]").rating({
        required: true,
        readOnly: [*if $disabled*]true[*else*]false[*/if*],
        callback: function(value){
            set_rating(value, '[*$rtoid|sl*]', '[*$rtype|sl*]', rating_sel, true, null, null, '[*$subratingid|sl*]');
        }
    });
</script>