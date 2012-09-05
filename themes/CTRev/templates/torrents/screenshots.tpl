<script type='text/javascript'>
    function scr_type(obj,type) {
        var size = !type?'20':'40';
        type = !type?'file':'text';
        var o = jQuery('input', jQuery(obj).parent());
        o.attr('size', size);
        change_input_type(o, type);
    }

    scrtext = function (ronly) {
        return (!ronly?"[*'torrents_screenshot_n'|lang|sl*]"+scnum:"")
            +(scnum>1?"&nbsp;"+jQuery('#remover').html():"");
    }

    function add_iuploader() {
        var o = jQuery('dd.iposter:first').clone();
        var t = jQuery('a:first', o);
        if (t.length)
            scr_type(t, 1);
        var lp = jQuery('#last_scrnum');
        var i = parseInt(lp.text())+1;
        lp.text(i);
        incrase_name_num(jQuery('input', o), i);
        var html = "<"+"dt>"+scrtext()+"<"+"/dt>"+"<"+"dd class='iposter'>"+o.html()+"<"+"/dd>";
        jQuery('dl.screenshots').append(html);
        scnum++;
        if (scnum > [*'max_screenshots'|config|long*])
        jQuery('#iuplaoder_adder').hide();
    }
    function remove_screenshot(obj) {
        var o = jQuery(obj).parent();
        o.next().remove();
        o.remove();
        scnum = 1;
        jQuery('dl.screenshots dt:not(:first)').each(function () {
            jQuery(this).html(scrtext);
            scnum++;
        });
        if (scnum <= [*'max_screenshots'|config|long*])
        jQuery('#iuplaoder_adder').show();
    }
    scnum = 0;
    jQuery(document).ready(function () {
        jQuery('dl.screenshots dt').each(function () {
            jQuery(this).append(scrtext(true));
            scnum++;
        });
    });
</script>
<div class='hidden' id='remover'>
    <a href="javascript:void(0);" onclick='remove_screenshot(this)'>
        <img width='12' src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]">
    </a>
</div>
<dl class='info_text screenshots'>
    [*assign var="p" value=0*]
    [*foreach from=$nrow.screenshots item="i" key="n"*]
        <dt>[*if !$p*][*'torrents_poster'|lang*][*else*][*'torrents_screenshot_n'|lang*][*$p*][*/if*]</dt>
        <dd class='iposter'>
            [*if 'allowed_screenshots'|config == ($smarty.const.ALLOWED_IMG_PC | $smarty.const.ALLOWED_IMG_URL)*]
                <a href='javascript:void(0);' onclick='scr_type(this, 1)'>[*'torrents_from_server'|lang*]</a>&nbsp;|&nbsp;<a
                    href='javascript:void(0);' onclick='scr_type(this, 0)'>[*'torrents_from_pc'|lang*]</a>
                <div class='br'></div>
            [*/if*]
            <input 
                [*if is_array($i) || !'allowed_screenshots'|config|is:$smarty.const.ALLOWED_IMG_URL*]
                    type='file'
                    size='20'
                [*else*]
                    type='text'
                    size='40'
                    value='[*$i*]'
                [*/if*] name='screenshots[[*$p*]]'>
            <!--[*$p++*]-->
        </dd>
    [*/foreach*]
</dl>
<div class='hidden' id='last_scrnum'>[*$p*]</div>

<div align='right'><a href="javascript:void(0);" id='iuplaoder_adder' onclick="add_iuploader();"><img
            src="[*$theme_path*]engine_images/add_small.png" alt="[*'add'|lang*]"
            title="[*'add'|lang*]">&nbsp;[*'add_area'|lang*]</a></div>