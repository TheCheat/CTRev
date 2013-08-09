/*! 
 * a-tools 1.2
 * 
 * Copyright (c) 2009 Andrey Kramarev, Ampparit Inc. (www.ampparit.com)
 * Licensed under the MIT license.
 * http://www.ampparit.fi/a-tools/license.txt
 *
 * Basic usage:
 
    <textarea></textarea>
    <input type="text" />
    
    var sel = jQuery("textarea").getSelection()
    
    jQuery("input").replaceSelection("foo");
    
    jQuery("#textarea").insertAtCaretPos("hello");

 */
var caretPositionAmp;

jQuery.fn.extend({
    getSelection: function(doc) {  // function for getting selection, and position of the selected text
        var input = this.jquery ? this[0] : this;
        var start;
        var end;
        var part;
        var number = 0;
        if (!doc)
            doc = document;
        if (doc.selection) {
            // part for IE and Opera
            var s = doc.selection.createRange();
            var minus = 0;
            var position = 0;
            var minusEnd = 0;
            var re;
            var rc;
            if (input.value.match(/\n/g) != null) {
                number = input.value.match(/\n/g).length;// number of EOL simbols
            }
            if (s.text) {
                part = s.text;
                // OPERA support
                if (typeof(input.selectionStart) == "number") {
                    start = input.selectionStart;
                    end = input.selectionEnd;
                    // return null if the selected text not from the needed area
                    if (start == end) {
                        return {
                            start: start,
                            end: end,
                            text: s.text,
                            length: end - start
                        };
                    }
                } else {
                    // IE support
                    var firstRe;
                    var secondRe;
                    re = input.createTextRange();
                    rc = re.duplicate();
                    firstRe = re.text;
                    re.moveToBookmark(s.getBookmark());
                    secondRe = re.text;
                    rc.setEndPoint("EndToStart", re);
                    // return null if the selectyed text not from the needed area
                    if (firstRe == secondRe && firstRe != s.text) {
                        return this;
                    }
                    start = rc.text.length;
                    end = rc.text.length + s.text.length;
                }
                // remove all EOL to have the same start and end positons as in MOZILLA
                if (number > 0) {
                    for (var i = 0; i <= number; i++) {
                        var w = input.value.indexOf("\n", position);
                        if (w != -1 && w < start) {
                            position = w + 1;
                            minus++;
                            minusEnd = minus;
                        } else if (w != -1 && w >= start && w <= end) {
                            if (w == start + 1) {
                                minus--;
                                minusEnd--;
                                position = w + 1;
                                continue;
                            }
                            position = w + 1;
                            minusEnd++;
                        } else {
                            i = number;
                        }
                    }
                }
                if (s.text.indexOf("\n", 0) == 1) {
                    minusEnd = minusEnd + 2;
                }
                start = start - minus;
                end = end - minusEnd;
                return {
                    start: start,
                    end: end,
                    text: s.text,
                    length: end - start
                };
            }
            input.focus ();
            if (typeof(input.selectionStart) == "number") {
                start = input.selectionStart;
            } else {
                s = doc.selection.createRange();
                re = input.createTextRange();
                rc = re.duplicate();
                re.moveToBookmark(s.getBookmark());
                rc.setEndPoint("EndToStart", re);
                start = rc.text.length;
            }
            if (number > 0) {
                for (var i = 0; i <= number; i++) {
                    var w = input.value.indexOf("\n", position);
                    if (w != -1 && w < start) {
                        position = w + 1;
                        minus++;
                    } else {
                        i = number;
                    }
                }
            }
            start = start - minus;
            return {
                start: start,
                end: start,
                text: s.text,
                length: 0
            };
        } else if (typeof(input.selectionStart) == "number" ) {
            start = input.selectionStart;
            end = input.selectionEnd;
            part = input.value.substring(input.selectionStart, input.selectionEnd);
            return {
                start: start,
                end: end,
                text: part,
                length: end - start
            };
        } else {
            return {
                start: undefined,
                end: undefined,
                text: undefined,
                length: undefined
            };
    }
},

// function for the replacement of the selected text
replaceSelection: function(inputStr, doc) {
    var input = this.jquery ? this[0] : this;
    //part for IE and Opera
    var start;
    var end;
    var position = 0;
    var rc;
    var re;
    var number = 0;
    var minus = 0;
    if (!doc)
        doc = document;
    if (doc.selection && typeof(input.selectionStart) != "number") {
        var s = doc.selection.createRange();
			
        // IE support
        if (typeof(input.selectionStart) != "number") { // return null if the selected text not from the needed area
            var firstRe;
            var secondRe;
            re = input.createTextRange();
            rc = re.duplicate();
            firstRe = re.text;
            re.moveToBookmark(s.getBookmark());
            secondRe = re.text;
            rc.setEndPoint("EndToStart", re);
            if (firstRe == secondRe && firstRe != s.text) {
                return this;
            }
        }
        if (s.text) {
            part = s.text;
            if (input.value.match(/\n/g) != null) {
                number = input.value.match(/\n/g).length;// number of EOL simbols
            }
            // IE support
            start = rc.text.length;
            // remove all EOL to have the same start and end positons as in MOZILLA
            if (number > 0) {
                for (var i = 0; i <= number; i++) {
                    var w = input.value.indexOf("\n", position);
                    if (w != -1 && w < start) {
                        position = w + 1;
                        minus++;
							
                    } else {
                        i = number;
                    }
                }
            }
            s.text = inputStr;
            caretPositionAmp = rc.text.length + inputStr.length;
            re.move("character", caretPositionAmp);
            doc.selection.empty();
            input.blur();
        }
        return this;
    } else if (typeof(input.selectionStart) == "number" && // MOZILLA support
        input.selectionStart != input.selectionEnd) {
			
        start = input.selectionStart;
        end = input.selectionEnd;
        input.value = input.value.substr(0, start) + inputStr + input.value.substr(end);
        position = start + inputStr.length;
        input.setSelectionRange(position, position);
        return this;
    }
    return this;
},
	
// insert text at current caret position
insertAtCaretPos: function(inputStr, doc) {
    var input = this.jquery ? this[0] : this;
    var start;
    var end;
    var position;
    var s;
    var re;
    var rc;
    var point;
    var minus = 0;
    var number = 0;
    input.focus();
    if (!doc)
        doc = document;
    if (doc.selection && typeof(input.selectionStart) != "number") {
        if (input.value.match(/\n/g) != null) {
            number = input.value.match(/\n/g).length;// number of EOL simbols
        }
        point = parseInt(caretPositionAmp);
        if (number > 0) {
            for (var i = 0; i <= number; i++) {
                var w = input.value.indexOf("\n", position);
                if (w != -1 && w <= point) {
                    position = w + 1;
                    point = point - 1;
                    minus++;
                }
            }
        }
    }
    caretPositionAmp = parseInt(caretPositionAmp);
    // IE
    input.onclick = function() { // for IE because it loses caret position when focus changed
        if (doc.selection && typeof(input.selectionStart) != "number") {
            s = doc.selection.createRange();
            re = input.createTextRange();
            rc = re.duplicate();
            re.moveToBookmark(s.getBookmark());
            rc.setEndPoint("EndToStart", re);
            caretPositionAmp = rc.text.length;
        }
    }
    if (doc.selection && typeof(input.selectionStart) != "number") {
        s = doc.selection.createRange();
        if (s.text.length != 0) {
            return this;
        }
        re = input.createTextRange();
        textLength = re.text.length;
        rc = re.duplicate();
        re.moveToBookmark(s.getBookmark());
        rc.setEndPoint("EndToStart", re);
        start = rc.text.length;
        if (caretPositionAmp >= 0 && start ==0 && caretPositionAmp != start) {
            minus = caretPositionAmp - minus;
            re.move("character", minus);
            re.select();
            s = doc.selection.createRange();
            caretPositionAmp += inputStr.length;
        } else if (!(caretPositionAmp >= 0) && start ==0) {
            re.move("character", textLength);
            re.select();
            s = doc.selection.createRange();
            caretPositionAmp = inputStr.length + textLength;
        } else if (!(parseInt(caretPositionAmp) >= 0)) {
            if (caretPositionAmp >= 0) {
                re.move("character", start);
                doc.selection.empty();
                re.select();
                s = doc.selection.createRange();
                caretPositionAmp = start + inputStr.length;
            } else {
                re.move("character", 0);
                doc.selection.empty();
                re.select();
                s = doc.selection.createRange();
                caretPositionAmp = start + inputStr.length;
            }
        } else if (parseInt(caretPositionAmp) >= 0 && parseInt(caretPositionAmp) == textLength) {
            re.move("character", textLength);
            re.select();
            s = doc.selection.createRange();
            caretPositionAmp = inputStr.length + textLength;
        } else if (parseInt(caretPositionAmp) >= 0) {
            re.move("character", 0);
            doc.selection.empty();
            re.select();
            s = doc.selection.createRange();
            caretPositionAmp = caretPositionAmp + inputStr.length;
        } else {
            re.move("character", caretPositionAmp-start);
            doc.selection.empty();
            re.select();
            s = doc.selection.createRange();
            caretPositionAmp = caretPositionAmp + inputStr.length;
        }
        s.text = inputStr;
        input.focus();

        return this;
    } else if (typeof(input.selectionStart) == "number" && // MOZILLA support
        input.selectionStart == input.selectionEnd) {
        position = input.selectionStart + inputStr.length;
        start = input.selectionStart;
        end = input.selectionEnd;
        input.value = input.value.substr(0, start) + inputStr + input.value.substr(end);
        input.setSelectionRange(position, position);
        return this;
    }
    return this;
}
}); 