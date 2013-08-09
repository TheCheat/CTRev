<script type='text/javascript'>
    function element_delete(id, sid, f) {
        if (!confirm('[*'are_you_sure_to_delete_this'|lang|sl*]'))
            return;
        jQuery.post('[*$admin_file|sl*]&act=delete&from_ajax=1', {'id': id}, function(data) {
            if (is_ok(data)) {
                var o = jQuery("#" + sid + id);
                if (o.is('tr'))
                    o = o.children('td');
                o.fadeOut(1700, function() {
                    var o = jQuery(this);
                    if (o.is('td'))
                        o = o.parent();
                    o.remove();
                    if (f && jQuery.isFunction(f))
                        f();
                });
                //alert(success_text);
            } else
                alert(error_text + (data ? ': ' + data : ""));
        });
    }
    function element_edit(id, sid, save) {
        var act = 'edit';
        var nno = false;
        var form;
        var obj = jQuery("#" + sid + id);
        if (save) {
            form = jQuery('input,textarea,select', obj).serialize() + '&id=' + id;
            act = 'save';
            nno = true;
        } else
            form = {'id': id};
        jQuery.post('[*$admin_file|sl*]&act=' + act + '&from_ajax=1' + (nno ? '&nno=1' : ''), form, function(data) {
            obj.replaceWith(data);
        });
    }
    function switch_element_state(obj, id, c, type) {
        jQuery.post('[*$admin_file|sl*]&act=switch&from_ajax=1', {'id': id, 'type': type}, function(data) {
            if (is_ok(data)) {
                jQuery(obj).parent().children('a' + (c ? '.' + c : '')).toggleClass('hidden');
                //alert(success_text);
            } else
                alert(error_text + (data ? ': ' + data : ""));
        });
    }
</script>