<script type="text/javascript">
    function delete_answer(object) {
        var $this_dd = jQuery(object).parent("span").parent("dd");
        var $this_dt = $this_dd.prev("dt");
        var $curnum = parseInt($this_dt.children("span").html());
        var $i = 0;
        var $obj = $this_dt.next("dd").next("dt");
        while ($obj.length) {
            $obj.children("span").html($curnum + $i);
            $obj = $obj.next("dd").next("dt");
            $i++;
        }
        $this_dd.fadeOut(1000, function() {
            jQuery(this).remove();
        });
        $this_dt.fadeOut(1000, function() {
            jQuery(this).remove();
        });
    }
    function add_answer(obj) {
        var $parent = jQuery(obj).parent("dd").parent("dl");
        var $oo = jQuery("span", jQuery("dt.answers", $parent).last());
        var $oo2 = jQuery("dd.answers:last", $parent).last();
        var $curnum = parseInt($oo.text());
        var $curobj = $parent.children(".second_answer").clone();
        $curobj.insertAfter($oo2);
        $curobj.eq(0).children("span").html($curnum + 1);
        $curobj.removeClass("second_answer");
        $curobj.eq(1).children("input[type='text']").val('');
        $curobj.eq(1).children("span.hidden").removeClass("hidden");
    }
    function save_polls_form(form) {
        var $form = jQuery(form).serialize();
        jQuery.post('index.php?[*fk ajax=1*]module=polls_manage&act=save&from_ajax=1&id=[*$poll_row.id|sl*]', $form, function(data) {
            //[*if !$from_ajax*]

            var id = '[*$poll_row.id|sl*]';
            id = id ? id : data.substr(ok_message.length);
            var url = "[*gen_link slashes=true module='polls' act='view' id='$1'*]".replace('$1', id);
            if (is_ok(data, true))
                window.location = url;
            //[*else*]

            id = '[*$poll_row.id|sl*]';
            id = id ? id : cut_ok(data);
            if (is_ok(data, true))
                change_voting_type(id);
            //[*/if*]

            else
                alert('[*'error'|lang|sl*]: ' + data);
        });
    }
</script>
[*if $fully_page*]
    <form method="post" action="javascript:save_polls_form('#polls_form');"
          id="polls_form">
        <div class="cornerText gray_color gray_border">
            <fieldset><legend>[*'polls_title_add'|lang*]</legend> 
            [*/if*]
            <dl class="info_text">
                <dt>[*'polls_title_input'|lang*]</dt>
                <dd><input type="text" size="30" name="question" value="[*$poll_row.question*]"></dd>

                [*foreach from=$poll_row.answers item=answer key=num*]
                    <!--[*$num++*]-->
                    <dt class="[*if $num==1*]second_answer [*/if*]answers">[*'polls_answer_num'|lang*]<span>[*$num*]</span></dt>
                    <dd class="[*if $num==1*]second_answer [*/if*]answers">
                        <input type="text" size="30" name="answers[]" value="[*$answer*]">
                        [*if $num>=1*]
                            <span[*if $num<=2*] class="hidden"[*/if*]>
                                <a href="javascript:void(0);" onclick="delete_answer(this);">
                                    <img src="[*$theme_path*]engine_images/delete.png" alt="[*'delete'|lang*]"
                                         title="[*'delete'|lang*]">
                                </a>
                            </span>
                        [*/if*]
                    </dd>
                [*/foreach*]
                <dt>[*'polls_max_vote_count'|lang*]</dt>
                <dd><input type="text"
                           value="[*if $poll_row.max_votes*][*$poll_row.max_votes*][*else*]1[*/if*]"
                           name="max_votes" size="3"></dd>
                <dt>[*'polls_end_poll_after'|lang*]</dt>
                <dd><input type="text"
                           value="[*if $poll_row.poll_ends*][*$poll_row.poll_ends*][*else*]0[*/if*]"
                           name="poll_ends" size="3"> [*'polls_days'|lang*]</dd>
                <dt>[*'polls_options'|lang*]</dt>
                <dd>
                    <div><input type="checkbox" value="1" name="show_voted"
                                [*if $poll_row.show_voted*] 
                                    checked="checked"
                                [*/if*]>[*'polls_show_voted'|lang*]</div>
                    <div><input type="checkbox" value="1" name="change_votes"
                                [*if $poll_row.change_votes*] 
                                    checked="checked"
                                [*/if*]>[*'polls_allow_change_answer'|lang*]</div>
                    <hr class="gray_border">
                    <a href="javascript:void(0);" onclick="add_answer(this);"><img
                            src="[*$theme_path*]engine_images/add_small.png" alt="[*'add'|lang*]"
                            title="[*'add'|lang*]">&nbsp;[*'add_area'|lang*]</a></dd>
            </dl>
            [*if $fully_page*]
                <div align="center"><input type="submit" value="[*'save'|lang*]"></div>
            </fieldset>
        </div>
    </form>
[*/if*]
