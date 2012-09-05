<form method="post" action="[*if !$row*][*gen_link module='news' act='add'*][*else*][*gen_link module='news' act='edit' id=$row.id*][*/if*]">
    <input type="hidden" name="confirm" value="1">
    [*fk ajax=0*]
    <div class='cornerText gray_color2 gray_border2'>
        <fieldset>
            <legend>[*'news_add'|lang*]</legend>
            <dl class="info_text">
                <dt>[*'news_title'|lang*]</dt>
                <dd><input type='text' name='title' value='[*$row.title*]' size='45'></dd>
                <dt>[*'news_content'|lang*]</dt>
                <dd>[*input_form name="content" text=$row.content*]</dd>
            </dl>
            <center><input type="submit" value="[*'news_submit'|lang*]"></center>
        </fieldset>
    </div>
</form>