/**
 * Инициализация скруглённых углов
 * @returns {null}
 */
function init_corners() {
    jQuery(document).ready(function($) {
        var f = '.cornerText';
        f += ',.cornerImg';
        f += ',fieldset';
        f += ',input[type="button"]:not(.very_simple_button)';
        f += ',input[type="submit"]:not(.very_simple_button)';
        jQuery(f).each(function() {
            var obj = jQuery(this);
            if (obj.is('already_cornered'))
                return;
            obj.addClass('already_cornered');
            var size = '5';
            if (obj.is('fieldset'))
                size = '3';
            else if (obj.is('img'))
                size = '7';
            else if (obj.is('input'))
                size = '9';
            add_corners(obj, size);
        });
    });
}

/**
 * Добавление скруглённых углов
 * @param {object} obj объект для действий
 * @param {int} size радиус скругления
 * @returns {null}
 */
function add_corners(obj, size) {
    var t = jQuery(obj);
    var b = (size?size:'5')+'px';
    if (t.is('.js_notop'))
        b = '0 0 '+b+' '+b;
    else if (t.is('.js_nobottom')) 
        b = b+' '+b+' 0 0';
    t.css({
        'border-radius': b,
        '-moz-border-radius': b,
        '-webkit-border-radius': b
    });
}