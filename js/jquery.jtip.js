/*
 * JTip
 * By Cody Lindley (http://www.codylindley.com)
 * Under an Attribution, Share Alike License
 * JTip is built on top of the very light weight jquery library.
 */

//on page load (as soon as its ready) call JT_init
$(document).ready(JT_init);

function JT_event(event) {
    event.preventDefault();
    if (jQuery(this).attr('data-jt') == 1) {
        jQuery(this).attr('data-jt', 0);
        $('#JT').remove();
    } else {
        jQuery("a.jTip").attr('data-jt', 0);
        jQuery(this).attr('data-jt', 1);
        JT_show(this.id, this.name, jQuery(this).is('.jTipHover'));
    }
}

function JT_init() {
    $("a.jTip:not(.jTipHover)").unbind("click").attr('onclick', '')
            .bind("click", JT_event);
    $("a.jTipHover").unbind("hover").bind("hover", JT_event);
}

function JT_show(linkId, title, hover) {
    if (title == false)
        title = "&nbsp;";
    var de = document.documentElement;
    var w = self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
    var hasArea = w - getAbsoluteLeft(linkId);
    var clickElementy = getAbsoluteTop(linkId) - 3; //set y position
    var params = {};
    if (params['width'] === undefined) {
        params['width'] = hover ? 10 : 350;
    }
    ;
    if (params['link'] !== undefined) {
        $('#' + linkId).bind('click', function() {
            window.location = params['link']
        });
        $('#' + linkId).css('cursor', 'pointer');
    }

    if (jQuery('#JT').length == 0) {
        $("body").append("<div id='JT' style='width:" + params['width'] * 1 + "px'>\n\
<div id='JT_arrow_left'></div>\n\
<div id='JT_close_left'></div>\n\
<div id='JT_arrow_right' style='left:" + ((params['width'] * 1) + 1) + "px'></div>\n\
<div id='JT_close_right'></div><div id='JT_copy'>\n\
<div id='JT_copy'><div id='JT_loader'>\n\
<div></div>\n\
</div>");
    }

    if (hasArea > ((params['width'] * 1) + 75)) {
        jQuery('#JT_close_left').text(title);
        jQuery('#JT_close_left,#JT_arrow_left').removeClass('hidden');
        jQuery('#JT_close_right,#JT_arrow_right').addClass('hidden');
        var arrowOffset = getElementWidth(linkId) + 11;
        var clickElementx = getAbsoluteLeft(linkId) + arrowOffset; //set x position
    } else {
        jQuery('#JT_close_right').text(title);
        jQuery('#JT_close_right,#JT_arrow_right').removeClass('hidden');
        jQuery('#JT_close_left,#JT_arrow_left').addClass('hidden');
        var clickElementx = getAbsoluteLeft(linkId) - ((params['width'] * 1) + 15); //set x position
    }

    $('#JT').css({
        left: clickElementx + "px",
        top: clickElementy + "px"
    });
    $('#JT').show();
    prehide_ls();
    $('#JT_copy').empty().append(jQuery('#' + linkId + '_body').html());

}

function getElementWidth(objectId) {
    x = document.getElementById(objectId);
    return x.offsetWidth;
}

function getAbsoluteLeft(objectId) {
    // Get an object left position from the upper left viewport corner
    o = document.getElementById(objectId)
    oLeft = o.offsetLeft            // Get left position from the parent object
    while (o.offsetParent != null) {   // Parse the parent hierarchy up to the document element
        oParent = o.offsetParent    // Get parent object reference
        oLeft += oParent.offsetLeft // Add parent left position
        o = oParent
    }
    return oLeft
}

function getAbsoluteTop(objectId) {
    // Get an object top position from the upper left viewport corner
    o = document.getElementById(objectId)
    oTop = o.offsetTop            // Get top position from the parent object
    while (o.offsetParent != null) { // Parse the parent hierarchy up to the document element
        oParent = o.offsetParent  // Get parent object reference
        oTop += oParent.offsetTop // Add parent top position
        o = oParent
    }
    return oTop
}

function blockEvents(evt) {
    if (evt.target) {
        evt.preventDefault();
    } else {
        evt.returnValue = false;
    }
}