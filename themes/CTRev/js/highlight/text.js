/*
===============================================================================
Chili is the jQuery code highlighter plugin
...............................................................................
LICENSE: http://www.opensource.org/licenses/mit-license.php
WEBSITE: http://noteslog.com/chili/

                                               Copyright 2008 / Andrea Ercolino
===============================================================================
*/

ChiliBook.recipes[ "text.js" ] = {
	  _name: "text"
	, _case: true
	, _main: {
		  mlcom  : { 
			  _match: /\/\*[^*]*\*+(?:[^\/][^*]*\*+)*\// 
			, _style: "color: #4040c2;"
		}
		, shcom  : { 
	  		_match: /\#.*/ 
			, _style: "color: green;"
		}
		, com    : { 
			  _match: /\/\/.*/ 
			, _style: "color: green;"
		}
		, string : { 
			  _match: /(?:\'[^\'\\\n]*(?:\\.[^\'\\\n]*)*\')|(?:\"[^\"\\\n]*(?:\\.[^\"\\\n]*)*\")/ 
			, _style: "color: teal;"
		}
		, number : { 
			  _match: /\b[+-]?(?:\d*\.?\d+|\d+\.?\d*)(?:[eE][+-]?\d+)?\b/ 
			, _style: "color: red;"
		}
		, keyword: { 
			  _match: /\b(?:until|while|void|using|typeof|try|true|throw|this|switch|string|static|sizeof|short|return|public|protected|private|object|null|new|namespace|long|lock|is|internal|interface|int|in|implicit|if|goto|function|foreach|for|float|finally|false|enum|else|double|do|delegate|default|decimal|continue|const|class|char|catch|case|byte|break|bool|base|as|abstract)\b/ 
			, _style: "color: navy; font-weight: bold;"
		}
	}
}