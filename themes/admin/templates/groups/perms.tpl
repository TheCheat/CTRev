<script type="text/javascript" src="js/jquery.dimensions.js"></script>
<script type="text/javascript" src="js/jquery.accordion.js"></script>
<script type='text/javascript'>
    jQuery(document).ready(function () {
        jQuery('#accordion_rules').accordion( {
            "autoheight" : false
        });
    });
</script>
<center><hr class='gray_border' width='75%'><br>
    <div class="accordion accordion_sw" id="accordion_rules">
        [*foreach from=$types item='type'*]
            <a class="accordion_header">[*"groups_type_$type"|lang*]</a>
            <div align='left'><dl class='info_text'>
                    [*foreach from=$perms.$type item='perm'*]
                        [*assign var='rule' value="can_`$perm.perm`"*]
                        <dt class='accordion_rdt'>[*"groups_rule_`$perm.perm`"|lang*]</dt>
                        <dd>[*$perm|@show_selector:$row.$rule*]</dd>
                    [*/foreach*]
                </dl>
            </div>
        [*/foreach*]
    </div><br>
</center>