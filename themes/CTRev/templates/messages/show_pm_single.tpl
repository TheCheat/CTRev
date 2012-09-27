[*if !$row*] 
    [*message lang_var='pm_no_this' die=0*] 
[*else*]
    [*assign var="title" value=$row.subject*]
    <br>
    <div id="item_[*$row.id*]">
        [*include file="blocks/center_block_header.tpl"*]
        [*include file="blocks/center_block_content.tpl"*]
        <div class='pm_content_single'>
            [*$row.text|ft*]
        </div>
        <div class='pm_content_underline'>
            <hr>
            <div class="float_left">[*if $row.sender != 'id'|user*]<input
                    type="button" value="[*'pm_reply'|lang*]"
                    onclick="window.location = '[*gen_link module='pm' act='send' to=$row.username slashes=true*]'">&nbsp;<input
                    type="button" value="[*'pm_resent'|lang*]"
                    onclick="window.location = '[*gen_link module='pm' act='resend' id=$row.id slashes=true*]'">&nbsp;[*/if*]<input
                    type="button" value="[*'delete'|lang*]"
                    onclick="remove_message('[*$row.id*]', true)"></div>
            <div align="right" class="pm_subinfo">[*'pm_sended'|lang*][*date time=$row.time format="ymdhis"*],
                [*'pm_from'|lang*][*$row.username|gcl:$row.group*]</div>
        </div>
        [*include file="blocks/center_block_footer.tpl"*]
    </div>
[*/if*]
<script type='text/javascript'>init_corners();</script>
