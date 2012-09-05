<div class='cornerText gray_color gray_border2'>
    <fieldset>
        <legend>[*'patterns_filling_by_pattern'|lang*] "[*$row.name*]"</legend>
        <form action="javascript:void(0);" onsubmit="save_pattern('[*$row.id|sl*]', this)">
            <div align='center'><font size='1'><b>[*'patterns_necessary_fields'|lang*]</b></font></div>
            <dl class="info_text">
                [*foreach from=$row.pattern item='el'*]
                    <dt>[*$el.name*]</dt>
                    <dd>[*$el|@patternfield_compile*]
                        [*if $el.descr*]
                            <br>
                            <font size='1'>[*$el.descr*]</font>
                        [*/if*]
                    </dd>
                [*/foreach*]
            </dl>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </form>
    </fieldset>
</div>