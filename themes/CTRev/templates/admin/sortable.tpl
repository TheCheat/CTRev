<script type='text/javascript' src='[*$theme_path*]js/ui/jquery.ui.core.js'></script>
<script type='text/javascript' src='[*$theme_path*]js/ui/jquery.ui.widget.js'></script>
<script type='text/javascript' src='[*$theme_path*]js/ui/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='[*$theme_path*]js/ui/jquery.ui.sortable.js'></script>
[*if $nestedsortable*]
    <script type='text/javascript' src='[*$theme_path*]js/ui/jquery.ui.nestedSortable.js'></script>
[*/if*]
<script type='text/javascript'>
    $(document).ready(function(){
    [*if $nestedsortable*]
            $('.sortable').nestedSortable({
                placeholder: 'sortable_placeholder',
                forcePlaceholderSize: true,
                handle: 'div',
                items: 'li',
                distance: '20',
                toleranceElement: '> div'
            });
    [*else*]
            $('.sortable').sortable({
                placeholder: 'sortable_placeholder',
                forcePlaceholderSize: true,
                connectWith: '.sortable',
                distance: '20',
                cancel: "li.sortable_disabled,input,button,select,textarea"
            });
    [*/if*]
        });
        function save_order(o) {
            var sort = "";
            var tmp = [];
            var c = 0;
            jQuery(o).each(function () {
                tmp[c++] = jQuery(this).[*if $nestedsortable*]nestedSortable[*else*]sortable[*/if*]("serialize");
            });
            if (tmp.length == 1)
                sort = tmp[0];
            else {
                for (var i = 0; i < c; i++) {
                    if (!tmp[i])
                        continue;
                    sort += (sort?"&":"")+tmp[i].replace(new RegExp('(\[[0-9]*\]\=)', "g"), "["+i+"]$1");
                }
            }
            jQuery.post('[*$admin_file|sl*]&act=order&from_ajax=1', sort, 
            function (data) {
                if (data=='OK!')
                    alert(success_text);
                else
                    alert(error_text+': '+data);
            });
        }   
</script>
[*include file='admin/default_functions.tpl'*]