<div class='pattern_element'>
    <div class='cornerText gray_color gray_border2'>
        <div class='pattern_delete hidden'>
            <a href='javascript:void(0);' onclick='delete_pelement(this);'>
                <img src="[*$atheme_path*]engine_images/delete.png" alt="[*'delete'|lang*]">
            </a></div>
        <dl class='info_text'>
            <dt>[*'patterns_area_name'|lang*]</dt>
            <dd><input type='text' name='name[]' value='[*$row.name*]'></dd>
            <dt class='pattern_descr'>[*'patterns_area_rname'|lang*]</dt>
            <dd class='pattern_descr'><input type='text' name='rname[]' value='[*$row.rname*]'></dd>
            <dt>[*'patterns_area_type'|lang*]</dt>
            <dd><select name='type[]' onchange="change_ptype(this)">
                    [*foreach from=$pat_types item="type"*]
                        <option value='[*$type*]'
                                [*if $row.type==$type || (!$row.type && $type=='input')*] 
                                    selected='selected'
                                [*/if*]>[*"patterns_types_$type"|lang*]</option>
                    [*/foreach*]
                </select>
                <input type='text' name='size[]' size='10' class='patterns_size' value='[*$row.size*]'>
            </dd>
            <dt class='pattern_values'>[*'patterns_area_values'|lang*]</dt>
            <dd class='pattern_values'><textarea cols="35" rows='5' name='values[]'>[*$row.values*]</textarea></dd>
            <dt class='pattern_html'>[*'patterns_area_html'|lang*]</dt>
            <dd class='pattern_html'><textarea cols="35" rows='7' name='html[]'>[*$row.html|he:false:true*]</textarea></dd>
            <dt class='pattern_descr'>[*'patterns_area_descr'|lang*]</dt>
            <dd class='pattern_descr'><textarea cols="35" rows='5' name='descr[]'>[*$row.descr|he:false:true*]</textarea></dd>
            <dt>[*'patterns_area_formdata'|lang*]</dt>
            <dd><textarea cols="35" rows='5' name='formdata[]'>[*$row.formdata*]</textarea></dd>
        </dl>
    </div>
</div>
<br>