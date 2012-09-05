/*******************************************************************************
 * JS-TrackBar
 * 
 * Copyright (C) 2008 by Alexander Burtsev - http://webew.ru/ and abarmot -
 * http://abarmot.habrahabr.ru/ and 1602 - http://1602.habrahabr.ru/ desing:
 * Светлана Соловьева - http://my.mail.ru/bk/concur/
 * 
 * This code is a public domain.
 ******************************************************************************/

$.fn.trackbar = function(op, id) {
	op = $.extend( {
		onMove : function() {
		},
		dual : true,
		width : 250, // px
		leftLimit : 0, // unit of value
		leftValue : 500, // unit of value
		rightLimit : 5000, // unit of value
		rightValue : 1500, // unit of value
		// roundUp: 50, // unit of value
		jq : this
	}, op);
	$.trackbar.getObject(id).init(op);
}

$.trackbar = { // NAMESPACE
	archive : [],
	getObject : function(id) {
		if (typeof id == 'undefined')
			id = this.archive.length;
		if (typeof this.archive[id] == "undefined") {
			this.archive[id] = new this.hotSearch(id);
		}
		return this.archive[id];
	}
};

$.trackbar.hotSearch = function(id) { // Constructor
	// Vars
	this.id = id;

	this.leftWidth = 0; // px
	this.rightWidth = 0; // px
	this.width = 0; // px
	this.intervalWidth = 0; // px

	this.leftLimit = 0;
	this.leftValue = 0;
	this.rightLimit = 0;
	this.rightValue = 0;
	this.valueInterval = 0;
	this.widthRem = 6;
	this.valueWidth = 0;
	this.roundUp = 0;

	this.x0 = 0;
	this.y0 = 0;
	this.blockX0 = 0;
	this.rightX0 = 0;
	this.leftX0 = 0;
	// Flags
	this.dual = true;
	this.moveState = false;
	this.moveIntervalState = false;
	this.debugMode = true;
	this.clearLimits = false;
	this.clearValues = false;
	// Handlers
	this.onMove = null;
	// Nodes
	this.leftBlock = null;
	this.rightBlock = null;
	this.leftBegun = null;
	this.rightBegun = null;
	this.centerBlock = null;
	this.itWasMove = false;
	this.leftBegunImage = './imgtrackbar/b_l.gif';
	this.rightBegunImage = './imgtrackbar/b_r.gif';
}

$.trackbar.hotSearch.prototype = {
	// Const
	ERRORS : {
		1 : "Ошибка при инициализации объекта",
		2 : "Левый бегунок не найден",
		3 : "Правый бегунок не найден",
		4 : "Левая область ресайза не найдена",
		5 : "Правая область ресайза не найдена",
		6 : "Не задана ширина области бегунка",
		7 : "Не указано максимальное изменяемое значение",
		8 : "Не указана функция-обработчик значений",
		9 : "Не указана область клика"
	},
	LEFT_BLOCK_PREFIX : "leftBlock",
	RIGHT_BLOCK_PREFIX : "rightBlock",
	LEFT_BEGUN_PREFIX : "leftBegun",
	RIGHT_BEGUN_PREFIX : "rightBegun",
	CENTER_BLOCK_PREFIX : "centerBlock",
	// Methods
	// Default
	gebi : function(id) {
		return this.jq.find('#' + id)[0];
	},
	addHandler : function(object, event, handler, useCapture) {
		if (object.addEventListener) {
			object.addEventListener(event, handler, useCapture ? useCapture
					: false);
		} else if (object.attachEvent) {
			object.attachEvent('on' + event, handler);
		} else
			alert(this.errorArray[9]);
	},
	defPosition : function(event) {
		var x = y = 0;
		if (document.attachEvent != null) {
			x = window.event.clientX + document.documentElement.scrollLeft
					+ document.body.scrollLeft;
			y = window.event.clientY + document.documentElement.scrollTop
					+ document.body.scrollTop;
		}
		if (!document.attachEvent && document.addEventListener) { // Gecko
			x = event.clientX + window.scrollX;
			y = event.clientY + window.scrollY;
		}
		return {
			x : x,
			y : y
		};
	},
	absPosition : function(obj) {
		var x = y = 0;
		while (obj) {
			x += obj.offsetLeft;
			y += obj.offsetTop;
			obj = obj.offsetParent;
		}
		return {
			x : x,
			y : y
		};
	},
	// Common
	debug : function(keys) {
		if (!this.debugMode)
			return;
		var mes = "";
		for ( var i = 0; i < keys.length; i++)
			mes += this.ERRORS[keys[i]] + " : ";
		mes = mes.substring(0, mes.length - 3);
		alert(mes);
	},
	init : function(hash) {
		// try {
		this.dual = typeof hash.dual != "undefined" ? !!hash.dual : this.dual;
		this.leftLimit = hash.leftLimit || this.leftLimit;
		this.rightLimit = hash.rightLimit || this.rightLimit;
		this.width = hash.width || this.width;
		this.onMove = hash.onMove || this.onMove;
		this.clearLimits = hash.clearLimits || this.clearLimits;
		this.clearValues = hash.clearValues || this.clearValues;
		this.roundUp = hash.roundUp || this.roundUp;
		this.jq = hash.jq;
		this.leftBegunImage = hash.leftBegunImage;
		this.rightBegunImage = hash.rightBegunImage;
		// HTML Write
		this.jq
				.html('<table'
						+ (this.width ? ' style="width:' + this.width + 'px;"'
								: '')
						+ 'class="trackbar" onSelectStart="return false;">'
						+ '<tr>'
						+ '<td class="l"><div id="leftBlock"><span></span><span class="limit"></span><img id="leftBegun" ondragstart="return false;" src="'
						+ this.leftBegunImage
						+ '" width="5" height="17" alt="" /></div></td>'
						+ '<td class="c" id="centerBlock"></td>'
						+ '<td class="r"><div id="rightBlock"><span></span><span class="limit"></span><img id="rightBegun" ondragstart="return false;" src="'
						+ this.rightBegunImage
						+ '" width="5" height="17" alt="" /></div></td>'
						+ '</tr>' + '</table>');
		// Is all right?
		if (this.onMove == null) {
			this.debug( [ 1, 8 ]);
			return;
		}
		// ---
		this.leftBegun = this.gebi(this.LEFT_BEGUN_PREFIX);
		if (this.leftBegun == null) {
			this.debug( [ 1, 2 ]);
			return;
		}
		this.rightBegun = this.gebi(this.RIGHT_BEGUN_PREFIX);
		if (this.rightBegun == null) {
			this.debug( [ 1, 3 ]);
			return;
		}
		this.leftBlock = this.gebi(this.LEFT_BLOCK_PREFIX);
		if (this.leftBlock == null) {
			this.debug( [ 1, 4 ]);
			return;
		}
		this.rightBlock = this.gebi(this.RIGHT_BLOCK_PREFIX);
		if (this.rightBlock == null) {
			this.debug( [ 1, 5 ]);
			return;
		}
		this.centerBlock = this.gebi(this.CENTER_BLOCK_PREFIX);
		if (this.centerBlock == null) {
			this.debug( [ 1, 9 ]);
			return;
		}
		// ---
		if (!this.width) {
			this.debug( [ 1, 6 ]);
			return;
		}
		if (!this.rightLimit) {
			this.debug( [ 1, 7 ]);
			return;
		}
		// Set default
		this.valueWidth = this.width - 2 * this.widthRem;
		this.rightValue = hash.rightValue || this.rightLimit;
		this.leftValue = hash.leftValue || this.leftLimit;
		if (!this.dual)
			this.rightValue = this.leftValue;
		this.valueInterval = this.rightLimit - this.leftLimit;
		this.leftWidth = parseInt((this.leftValue - this.leftLimit)
				/ this.valueInterval * this.valueWidth)
				+ this.widthRem;
		this.rightWidth = this.valueWidth
				- parseInt((this.rightValue - this.leftLimit)
						/ this.valueInterval * this.valueWidth) + this.widthRem;
		// Set limits
		if (!this.clearLimits) {
			this.leftBlock.firstChild.nextSibling.innerHTML = this.leftLimit;
			this.rightBlock.firstChild.nextSibling.innerHTML = this.rightLimit;
		}
		// Do it!
		this.setCurrentState();
		this.onMove();
		// Add handers
		var _this = this;
		this.addHandler(document, "mousemove", function(evt) {
			if (_this.moveState)
				_this.moveHandler(evt);
			if (_this.moveIntervalState)
				_this.moveIntervalHandler(evt);
		});
		this.addHandler(document, "mouseup", function() {
			_this.moveState = false;
			_this.moveIntervalState = false;
		});
		this.addHandler(this.leftBegun, "mousedown", function(evt) {
			evt = evt || window.event;
			if (evt.preventDefault)
				evt.preventDefault();
			evt.returnValue = false;
			_this.moveState = "left";
			_this.x0 = _this.defPosition(evt).x;
			_this.blockX0 = _this.leftWidth;
		});
		this.addHandler(this.rightBegun, "mousedown", function(evt) {
			evt = evt || window.event;
			if (evt.preventDefault)
				evt.preventDefault();
			evt.returnValue = false;
			_this.moveState = "right";
			_this.x0 = _this.defPosition(evt).x;
			_this.blockX0 = _this.rightWidth;
		});
		this.addHandler(this.centerBlock, "mousedown", function(evt) {
			evt = evt || window.event;
			if (evt.preventDefault)
				evt.preventDefault();
			evt.returnValue = false;
			_this.moveIntervalState = true;
			_this.intervalWidth = _this.width - _this.rightWidth
					- _this.leftWidth;
			_this.x0 = _this.defPosition(evt).x;
			_this.rightX0 = _this.rightWidth;
			_this.leftX0 = _this.leftWidth;
		}), this.addHandler(this.centerBlock, "click", function(evt) {
			if (!_this.itWasMove)
				_this.clickMove(evt);
			_this.itWasMove = false;
		});
		this.addHandler(this.leftBlock, "click", function(evt) {
			if (!_this.itWasMove)
				_this.clickMoveLeft(evt);
			_this.itWasMove = false;
		});
		this.addHandler(this.rightBlock, "click", function(evt) {
			if (!_this.itWasMove)
				_this.clickMoveRight(evt);
			_this.itWasMove = false;
		});
		// } catch(e) {this.debug([1]);}
	},
	clickMoveRight : function(evt) {
		evt = evt || window.event;
		if (evt.preventDefault)
			evt.preventDefault();
		evt.returnValue = false;
		var x = this.defPosition(evt).x - this.absPosition(this.rightBlock).x;
		var w = this.rightBlock.offsetWidth;
		if (x <= 0 || w <= 0 || w < x || (w - x) < this.widthRem)
			return;
		this.rightWidth = (w - x);
		this.rightCounter();

		this.setCurrentState();
		this.onMove();
	},
	clickMoveLeft : function(evt) {
		evt = evt || window.event;
		if (evt.preventDefault)
			evt.preventDefault();
		evt.returnValue = false;
		var x = this.defPosition(evt).x - this.absPosition(this.leftBlock).x;
		var w = this.leftBlock.offsetWidth;
		if (x <= 0 || w <= 0 || w < x || x < this.widthRem)
			return;
		this.leftWidth = x;
		this.leftCounter();

		this.setCurrentState();
		this.onMove();
	},
	clickMove : function(evt) {
		evt = evt || window.event;
		if (evt.preventDefault)
			evt.preventDefault();
		evt.returnValue = false;
		var x = this.defPosition(evt).x - this.absPosition(this.centerBlock).x;
		var w = this.centerBlock.offsetWidth;
		if (x <= 0 || w <= 0 || w < x)
			return;
		if (x >= w / 2) {
			this.rightWidth += (w - x);
			this.rightCounter();
		} else {
			this.leftWidth += x;
			this.leftCounter();
		}
		this.setCurrentState();
		this.onMove();
	},
	setCurrentState : function() {
		this.leftBlock.style.width = this.leftWidth + "px";
		if (!this.clearValues)
			this.leftBlock.firstChild.innerHTML = (!this.dual && this.leftWidth > this.width / 2) ? ""
					: this.leftValue;
		if (!this.dual) {
			var x = this.leftBlock.firstChild.offsetWidth;
			this.leftBlock.firstChild.style.right = (this.widthRem
					* (1 - 2 * (this.leftWidth - this.widthRem) / this.width) - ((this.leftWidth - this.widthRem)
					* x / this.width)) + 'px';
		}
		this.rightBlock.style.width = this.rightWidth + "px";
		if (!this.clearValues)
			this.rightBlock.firstChild.innerHTML = (!this.dual && this.rightWidth >= this.width / 2) ? ""
					: this.rightValue;
		if (!this.dual) {
			var x = this.rightBlock.firstChild.offsetWidth;
			this.rightBlock.firstChild.style.left = (this.widthRem
					* (1 - 2 * (this.rightWidth - this.widthRem) / this.width) - ((this.rightWidth - this.widthRem)
					* x / this.width)) + 'px';
		}
	},
	/*
	 * OLD setCurrentState : function() { this.leftBlock.style.width =
	 * this.leftWidth + "px"; this.leftBlock.firstChild.innerHTML = (!this.dual &&
	 * this.leftWidth > this.width / 2) ? "" : this.leftValue;
	 * this.rightBlock.style.width = this.rightWidth + "px";
	 * this.rightBlock.firstChild.innerHTML = (!this.dual && this.rightWidth >=
	 * this.width / 2) ? "" : this.rightValue; },
	 */
	moveHandler : function(evt) {
		this.itWasMove = true;
		evt = evt || window.event;
		if (evt.preventDefault)
			evt.preventDefault();
		evt.returnValue = false;
		if (this.moveState == "left") {
			this.leftWidth = this.blockX0 + this.defPosition(evt).x - this.x0;
			this.leftCounter();
		}
		if (this.moveState == "right") {
			this.rightWidth = this.blockX0 + this.x0 - this.defPosition(evt).x;
			this.rightCounter();
		}
		this.setCurrentState();
		this.onMove();
	},
	moveIntervalHandler : function(evt) {
		this.itWasMove = true;
		evt = evt || window.event;
		if (evt.preventDefault)
			evt.preventDefault();
		evt.returnValue = false;
		var dX = this.defPosition(evt).x - this.x0;
		if (dX > 0) {
			this.rightWidth = this.rightX0 - dX > this.widthRem ? this.rightX0
					- dX : this.widthRem;
			this.leftWidth = this.width - this.rightWidth - this.intervalWidth;
		} else {
			this.leftWidth = this.leftX0 + dX > this.widthRem ? this.leftX0
					+ dX : this.widthRem;
			this.rightWidth = this.width - this.leftWidth - this.intervalWidth;
		}
		this.rightCounter();
		this.leftCounter();
		this.setCurrentState();
		this.onMove();
	},
	updateRightValue : function(rightValue) {
		try {
			this.rightValue = parseInt(rightValue);
			this.rightValue = this.rightValue < this.leftLimit ? this.leftLimit
					: this.rightValue;
			this.rightValue = this.rightValue > this.rightLimit ? this.rightLimit
					: this.rightValue;
			if (this.dual) {
				this.rightValue = this.rightValue < this.leftValue ? this.leftValue
						: this.rightValue;
			} else
				this.leftValue = this.rightValue;
			this.rightWidth = this.valueWidth
					- parseInt((this.rightValue - this.leftLimit)
							/ this.valueInterval * this.valueWidth)
					+ this.widthRem;
			this.rightWidth = isNaN(this.rightWidth) ? this.widthRem
					: this.rightWidth;
			if (!this.dual)
				this.leftWidth = this.width - this.rightWidth;
			this.setCurrentState();
		} catch (e) {
		}
	},
	rightCounter : function() {
		if (this.dual) {
			this.rightWidth = this.rightWidth > this.width - this.leftWidth ? this.width
					- this.leftWidth
					: this.rightWidth;
			this.rightWidth = this.rightWidth < this.widthRem ? this.widthRem
					: this.rightWidth;
			this.rightValue = this.leftLimit
					+ this.valueInterval
					- parseInt((this.rightWidth - this.widthRem)
							/ this.valueWidth * this.valueInterval);
			if (this.roundUp)
				this.rightValue = parseInt(this.rightValue / this.roundUp)
						* this.roundUp;
			if (this.leftWidth + this.rightWidth >= this.width)
				this.rightValue = this.leftValue;
		} else {
			this.rightWidth = this.rightWidth > (this.width - this.widthRem) ? this.width
					- this.widthRem
					: this.rightWidth;
			this.rightWidth = this.rightWidth < this.widthRem ? this.widthRem
					: this.rightWidth;
			this.leftWidth = this.width - this.rightWidth;
			this.rightValue = this.leftLimit
					+ this.valueInterval
					- parseInt((this.rightWidth - this.widthRem)
							/ this.valueWidth * this.valueInterval);
			if (this.roundUp)
				this.rightValue = parseInt(this.rightValue / this.roundUp)
						* this.roundUp;
			this.leftValue = this.rightValue;
		}
	},
	updateLeftValue : function(leftValue) {
		try {
			this.leftValue = parseInt(leftValue);
			this.leftValue = this.leftValue < this.leftLimit ? this.leftLimit
					: this.leftValue;
			this.leftValue = this.leftValue > this.rightLimit ? this.rightLimit
					: this.leftValue;
			if (this.dual) {
				this.leftValue = this.rightValue < this.leftValue ? this.rightValue
						: this.leftValue;
			} else
				this.rightValue = this.leftValue;
			this.leftWidth = parseInt((this.leftValue - this.leftLimit)
					/ this.valueInterval * this.valueWidth)
					+ this.widthRem;
			this.leftWidth = isNaN(this.leftWidth) ? this.widthRem
					: this.leftWidth;
			if (!this.dual)
				this.rightWidth = this.width - this.leftWidth;
			this.setCurrentState();
		} catch (e) {
		}
	},
	leftCounter : function() {
		if (this.dual) {
			this.leftWidth = this.leftWidth > this.width - this.rightWidth ? this.width
					- this.rightWidth
					: this.leftWidth;
			this.leftWidth = this.leftWidth < this.widthRem ? this.widthRem
					: this.leftWidth;
			this.leftValue = this.leftLimit
					+ parseInt((this.leftWidth - this.widthRem)
							/ this.valueWidth * this.valueInterval);
			if (this.roundUp)
				this.leftValue = parseInt(this.leftValue / this.roundUp)
						* this.roundUp;
			if (this.leftWidth + this.rightWidth >= this.width)
				this.leftValue = this.rightValue;
		} else {
			this.leftWidth = this.leftWidth > (this.width - this.widthRem) ? this.width
					- this.widthRem
					: this.leftWidth;
			this.leftWidth = this.leftWidth < this.widthRem ? this.widthRem
					: this.leftWidth;
			this.rightWidth = this.width - this.leftWidth;
			this.leftValue = this.leftLimit
					+ parseInt((this.leftWidth - this.widthRem)
							/ this.valueWidth * this.valueInterval);
			if (this.roundUp)
				this.leftValue = parseInt(this.leftValue / this.roundUp)
						* this.roundUp;
			this.rightValue = this.leftValue;
		}
	}
}