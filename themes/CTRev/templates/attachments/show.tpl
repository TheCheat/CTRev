[*if $attach_rows*]
    <br>
    <script src="js/jquery.jtip.js" type="text/javascript"></script>
    <fieldset class='fattachments'>
        <legend>
            <a href="javascript:void(0);" onclick="open_spoiler(this, jQuery('.spoiler_content', jQuery(this).parents('fieldset.fattachments')));" class="spoiler_icon"></a>
            &nbsp;[*'attachments'|lang*]&nbsp;<abbr title="[*'attachments_help'|lang|he*]">?</abbr>
        </legend>
        <div class='hidden spoiler_content'>
            [*foreach from=$attach_rows key="num" item="attach_row"*]          
                [*if $attach_row.preview*]
                    <div class='hidden' id='apreview_id[*$attach_row.id*]_body'> 
                        <img src="[*'attachpreview_folder'|config*]/[*$attach_row.preview*]" alt="Preview">
                    </div>
                [*/if*]
                <div class="middle_pos_attach">
                    <div class="nobr">
                        [*if $attach_row.ftimage*]
                            <img src="[*'ftypes_folder'|config*]/[*$attach_row.ftimage*]" alt="FType">
                        [*/if*]
                        <a href="[*gen_link module='attach' id=$attach_row.id _filetype=$attach_row.filename*]"
                           [*if $attach_row.preview*] rel="sexylightbox[attach]"
                               title='[*$attach_row.filename*]' class='jTip jTipHover' name='[*$attach_row.filename*]' id='apreview_id[*$attach_row.id*]' 
                           [*/if*]>
                            [*$attach_row.filename*]
                        </a>
                        ([*$attach_row.size|cs*], [*$attach_row.downloaded*] [*'downloads'|lang*])
                    </div>
                </div>
            [*/foreach*]
        </div>
    </fieldset>
[*/if*]