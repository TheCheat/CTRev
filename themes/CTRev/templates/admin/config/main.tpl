[*include file='admin/sortable.tpl'*]
[*if !$from_ajax*]
    <script type='text/javascript'>
        function save_config(o) {
            jQuery.post('[*$admin_file|sl*]&from_ajax=1', jQuery(o).serialize(), function (data) {
                if (parseInt(data)==data)
                    alert(success_text+'. [*'config_updated_values'|lang|sl*]'+data);
                else
                    alert(error_text+': '+data);
            });
        }
        function change_page(type) {
            jQuery.post('[*$admin_file|sl*]&from_ajax=1&nno=1&type='+type, function (data) {
                jQuery('#config_form').replaceWith(data);
            })
        }
    </script>
[*/if*]
<form action='javascript:save_config("#config_form");' method='post' id='config_form'>
    <div class='cornerText gray_color2'>
        <fieldset>
            [*assign var='type' value=$rows[0].cat*]
            <legend>
                [*if $ptype*]
                    <a href='javascript:change_page("[*$ptype|sl*]");' title='[*"config_type_$ptype"|lang*]'>
                        &nbsp;&laquo;&nbsp;
                    </a>
                [*else*]
                    &nbsp;
                [*/if*]
                [*"config_type_$type"|lang*]
                [*if $ntype*]
                    <a href='javascript:change_page("[*$ntype|sl*]");' title='[*"config_type_$ntype"|lang*]'>
                        &nbsp;&raquo;&nbsp;
                    </a>
                [*else*]
                    &nbsp;
                [*/if*]

            </legend>
            <ul class='sortable'>
                [*foreach from=$rows item="row"*]
                    [*assign var='name' value=$row.name*]
                    <li class='sortable_thin'>
                        <dl class='info_text'>
                            <dt>[*"config_field_$name"|lang*]</dt>
                            <dd>[*$row|@show_ctype*]
                                [*if "config_descr_field_$name"|lang:true*]
                                    <div class='br'></div>
                                    <font size='1'><b>[*"config_descr_field"|lang*]</b>
                                        [*"config_descr_field_$name"|lang:true*]
                                    </font>
                                [*/if*]
                            </dd>
                        </dl>
                    </li>
                [*/foreach*]
            </ul>
            <div align='center'><input type='submit' value='[*'save'|lang*]'></div>
        </fieldset>
    </div>
</form>