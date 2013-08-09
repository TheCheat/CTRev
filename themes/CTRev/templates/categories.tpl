[*assign var="next_button" value='<b>&nbsp;=>&nbsp;</b>'*]
[*if !$from_ajax*]
    <script type='text/javascript'>
        function select_init() {
            jQuery(document).ready(function () {
                jQuery("#add_cats select").unbind("change");
                jQuery("#add_cats select").bind("change", function () {
                    if (typeof cats_was_selected != 'undefined')
                        cats_was_selected = false;
                    jQuery('#fill_cattpattern').show();
                    var $this = jQuery(this);
                    var $num = $this.attr("name").substr(5).substr(0, $this.attr("name").length - 8);
                    var $i = 0;
                    jQuery("#add_cats select").each(function () {
                        if ($i > $num) {
                            jQuery("div[rel='"+$(this).attr("name")+"']").remove();
                        }
                        $i++;
                    });
                    $("#cat_next").remove();
                    if ($this.val().length == 1) {
                        if ($this.children("option[value='"+$this.val()[0]+"']").attr("data-nosel")) {
                            post_data($this.val()[0], $num);
                        } else {
                            if (typeof cats_was_selected != 'undefined')
                                cats_was_selected = true;
                            $this.after("<"+"span id='cat_next'>[*$next_button|sl*]</span>");
                            $("#cat_next").click(function () {
                                $(this).remove();
                                post_data($this.val()[0], $num);
                            });
                        }
                    } else {
                        $this.children().each(function () {
                            if (jQuery(this).attr("data-nosel"))
                                $this.val('');
                            else if (typeof cats_was_selected != 'undefined')
                                cats_was_selected = true;
                        });
                    }
                });
            });
        }
        function post_data($id, $num) {
            jQuery.post("index.php?module=ajax_index&act=children_cat&from_ajax=1", {
                'id':$id,
                'num':$num,
                'type':'[*$cattype|sl*]'}, function (data) {
                $("#add_cats").append(data);
            });
        }
        function fill_pattern(obj) {
            var obj = jQuery(obj);
            var i = obj.length - 1;
            var pid = 0;
            for (;i >= 0 && !pid;i--)
                jQuery('option:selected', obj.eq(i)).each(function () {
                    if (pid)
                        return;
                    pid = jQuery(this).attr('data-pattid');
                });
            if (!pid) {
                alert('[*'no_patterns'|lang|sl*]');
                return;
            }
            default_windopen('module=ajax_index&act=build_pattern&id='+pid);
        }
        select_init();
    </script>
    <div id="add_cats">
    [*/if*]
    [*if $cats*]
        [*if ($from_ajax && $cats) || !$from_ajax*]
            <div rel="cats[[*$cnum+0*]][]" style="float: left;">
                [*if $from_ajax*]
                    [*$next_button*]
                [*/if*]
                <select name="cats[[*$cnum+0*]][]" size="5" multiple="multiple">
                    [*foreach from=$cats item=crow*]
                        <option value="[*$crow.id*]" 
                                [*if $crow.pattern*] 
                                    data-pattid="[*$crow.pattern*]"
                                [*/if*]
                                [*if !$crow.post_allow*] 
                                    data-nosel="true"
                                [*/if*]>[*$crow.name*]</option>
                    [*/foreach*]
                </select>
            </div>
            <script type="text/javascript">
                select_init();
            </script>
        [*/if*]
    [*elseif $categories*] 
        [*foreach from=$categories key=cnum item=cats*]
            <div rel="cats[[*$cnum+0*]][]" style="float: left;">
                [*if $cnum*]
                    [*$next_button*]
                [*/if*]
                <select name="cats[[*$cnum+0*]][]" size="5" multiple="multiple">
                    [*foreach from=$cats item=crow*]
                        <option value="[*$crow.id*]" 
                                [*if $crow.pattern*] 
                                    data-pattid="[*$crow.pattern*]"
                                [*/if*]
                                [*if !$crow.post_allow*] 
                                    data-nosel="true"
                                [*/if*] 
                                [*if $cids.$cnum==$crow.id || (is_array( $row_cats ) && in_array( $crow.id , $row_cats ))*] 
                                    selected="selected"
                                [*/if*]>[*$crow.name*]</option>
                    [*/foreach*]
                </select>
            </div>
        [*/foreach*] 
    [*/if*]
    [*if !$from_ajax*]
    </div>
    [*if !$no_cpattern*]
        <div class='padding_left clear_both hidden' id='fill_cattpattern'>
            <a href='javascript:fill_pattern("#add_cats select")'>
                <b>[*'fill_pattern'|lang*]</b>
            </a>
        </div>
    [*/if*]
[*/if*]