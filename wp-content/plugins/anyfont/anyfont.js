/*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor,
    Boston, MA  02110-1301, USA.
    ---
    Copyright (C) 2009, Ryan Peel ryan@2amlife.com
 */

Hash.prototype.without = function() {
    var values = $A(arguments);
	var retHash = $H();
    this.each(function(entry) {
		if(!values.include(entry.key))
			retHash.set(entry.key, entry.value);
    });
	return retHash;
}

Element.insertAfter = function(insert, element) {
	if (element.nextSibling) element.parentNode.insertBefore(insert, element.nextSibling);
	else element.parentNode.appendChild(insert);
}

// Fix exceptions thrown thrown when removing an element with no parent
Element._remove = Element.remove;
Element.remove = function(element) {
	element = $(element);
	if (element.parentNode)
		return Element._remove(element);
}

/*
 * Control.ColorPicker
 *
 * Transforms an ordinary input textbox into an interactive color chooser,
 * allowing the user to select a color from a swatch palette.
 *
 * Features:
 *  - Allows saving custom colors to the palette for later use
 *  - Customizable by CSS
 *
 * Written and maintained by Jeremy Jongsma (jeremy@jongsma.org)
 */
var Control = {};

Control.ColorPicker = Class.create({
	initialize: function (element, options) {
		this.element = $(element);
		this.options = Object.extend({
				className: 'colorpickerControl'
			}, options || {});
		this.colorpicker = new Control.ColorPickerPanel({
				onSelect: this.colorSelected.bind(this)
			});

		this.dialogOpen = false;
		this.element.maxLength = 7;

		this.dialog = new Element('div');
		this.dialog.style.position = 'absolute';
		var cpCont = new Element('div').addClassName(this.options.className);
		cpCont.insert(this.colorpicker.element);
		this.dialog.insert(cpCont);

		var cont = new Element('div', {'style': 'position: relative;float:left;'});
		this.element.wrap(cont);

		this.swatch = new Element('div', {'style':'border:1px solid gray; position:absolute;right: 6px;top: 2px; width:12px; height: 12px; background-color:'+this.element.value});
		this.swatch.title = 'Open color palette';
		this.swatch.addClassName('inputExtension');
		cont.insert(this.swatch);
		this.element.onchange = this.textChanged.bindAsEventListener(this);
		this.element.onblur = this.hidePicker.bindAsEventListener(this);
		this.swatch.onclick = this.togglePicker.bindAsEventListener(this);
		this.documentClickListener = this.documentClickHandler.bindAsEventListener(this);
	},

	colorSelected: function(color) {
		this.element.value = color;
		this.swatch.style.backgroundColor = color;
		this.hidePicker();
		try{
			if(typeof this.element.up('div#anyfont-style-new-css') !='undefined'){
				this.updateText = $('anyfont-css-preview');
			} else {
				this.updateText = this.element.up('li.curOpen').down('span.font-preview');
			}
			if(typeof this.updateText != 'undefined'){
				if(this.element.getAttribute('name') == "color"){
					this.updateText.setStyle({"color":color})
				} else if(this.element.getAttribute('name') == "shadow-color"){
					AnyFont.setShadow(this.element);
				}
			}
		}catch(e){}
	},

	textChanged: function(e) {
		this.swatch.style.backgroundColor = this.element.value;
		if(typeof this.element.up('div#anyfont-style-new-css') !='undefined'){
			this.updateText = $('anyfont-css-preview');
		} else {
			this.updateText = this.element.up('li.curOpen').down('span.font-preview');
		}
		if(typeof this.updateText != 'undefined'){
			if(this.element.getAttribute('name') == "color"){
				this.updateText.setStyle({"color":color})
			} else if(this.element.getAttribute('name') == "shadow-color"){
				AnyFont.setShadow(this.element);
			}
		}
	},

	togglePicker: function(e) {
		if (this.dialogOpen) this.hidePicker();
		else this.showPicker();
	},
	showPicker: function(e) {
		if (!this.dialogOpen) {
			var dim = Element.getDimensions(this.element);
			var position = Position.cumulativeOffset(this.element);
			var pickerTop = Prototype.IE ? (position[1] + dim.height) + 'px' : (position[1] + dim.height - 1) + 'px';
			this.dialog.style.top = pickerTop;
			this.dialog.style.left = position[0] + 'px';
			document.body.insert(this.dialog);
			document.observe('click', this.documentClickListener);
			this.dialogOpen = true;
		}
	},
	hidePicker: function(e) {
		if (this.dialogOpen) {
			Event.stopObserving(document, 'click', this.documentClickListener);
			Element.remove(this.dialog);
			this.dialogOpen = false;
		}
	},
	documentClickHandler: function(e) {
		var element = Event.element(e);
		var abort = false;
		do {
			if (element == this.swatch || element == this.dialog)
				abort = true;
		} while (element = element.parentNode);
		if (!abort)
			this.hidePicker();
	}
});

Control.ColorPickerPanel = Class.create({

	initialize: function(options) {
		this.options = Object.extend({
				addLabel: 'Add',
				colors: Array(
					'#000000', '#993300', '#333300', '#003300', '#003366', '#000080', '#333399', '#333333',
					'#800000', '#FF6600', '#808000', '#008000', '#008080', '#0000FF', '#666699', '#808080',
					'#FF0000', '#FF9900', '#99CC00', '#339966', '#33CCCC', '#3366FF', '#800080', '#969696',
					'#FF00FF', '#FFCC00', '#FFFF00', '#00FF00', '#00FFFF', '#00CCFF', '#993366', '#C0C0C0',
					'#FF99CC', '#FFCC99', '#FFFF99', '#CCFFCC', '#CCFFFF', '#99CCFF', '#CC99FF', '#FFFFFF'),
				onSelect: Prototype.emptyFunction
			}, options || {});
		this.activeCustomSwatch =  null,
		this.customSwatches = [];

		this.element = this.create();
	},

	create: function() {
		var cont = new Element("div");
		var colors = this.options.colors;

		// Create swatch table
		var swatchTable = new Element("table");
		swatchTable.cellPadding = 0;
		swatchTable.cellSpacing = 0;
		swatchTable.border = 0;
		for (var i = 0; i < 5; ++i) {
			var row = swatchTable.insertRow(i);
			for (var j = 0; j < 8; ++j) {
				var cell = row.insertCell(j);
				var color = colors[(8 * i) + j];
				var swatch = new Element("div").addClassName("colour_swatch");
				swatch.setStyle({'backgroundColor': color});
				swatch.onclick = this.swatchClickListener(color);
				swatch.onmouseover = this.swatchHoverListener(color);
				cell.appendChild(swatch);
			}
		}

		// Add spacer row
		var spacerRow = swatchTable.insertRow(5);
		var spacerCell = spacerRow.insertCell(0);
		//spacerCell.colSpan = 8;
		spacerCell.colSpan = 8;
		var hr =  new Element("hr").setStyle({'color': 'gray', 'backgroundColor': 'gray', 'height': '1px', 'border': '0', 'marginTop': '3px', 'marginBottom': '3px', 'padding': '0'});
		spacerCell.appendChild(hr);

		// Add custom color row
		var customRow = swatchTable.insertRow(6);
		var customColors = this.loadSetting('customColors')
			?  this.loadSetting('customColors').split(',')
			: new Array();
		this.customSwatches = [];
		for (var i = 0; i < 8; ++i) {
			var cell = customRow.insertCell(i);
			var color = customColors[i] ? customColors[i] : '#000000';
			var swatch = new Element("div").addClassName("colour_swatch");
			swatch.setStyle({'backgroundColor': color});
			cell.appendChild(swatch);
			swatch.onclick = this.swatchCustomClickListener(color, swatch);
			swatch.onmouseover = this.swatchHoverListener(color);
			this.customSwatches.push(swatch);
		}

		// Add spacer row
		spacerRow = swatchTable.insertRow(7);
		spacerCell = spacerRow.insertCell(0);
		spacerCell.colSpan = 8;
		hr = document.createElement('hr');
		Element.setStyle(hr, {'color': 'gray', 'backgroundColor': 'gray', 'height': '1px', 'border': '0', 'marginTop': '3px', 'marginBottom': '3px', 'padding': '0'});
		spacerCell.appendChild(hr);
		// Add custom color entry interface
		var entryRow = swatchTable.insertRow(8);
		var entryCell = entryRow.insertCell(0);
		entryCell.colSpan = 8;
		var entryTable = document.createElement('table');
		entryTable.cellPadding = 0;
		entryTable.cellSpacing = 0;
		entryTable.border = 0;
		entryTable.style.width = '136px';
		entryCell.appendChild(entryTable);

		entryRow = entryTable.insertRow(0);
		var previewCell = entryRow.insertCell(0);
		previewCell.valign = 'bottom';
		var preview = new Element("div").setStyle({'width': '15px', 'height': '15px', 'font-size': '15px', 'border': '1px solid #EEEEEE', 'backgroundColor': '#000000'});

		previewCell.appendChild(preview);
		this.previewSwatch = preview;

		var textboxCell = entryRow.insertCell(1);
		textboxCell.valign = 'bottom';
		textboxCell.align = 'center';
		var textbox = new Element("input", {"type":"text", "value":"#000000"}).setStyle({'width': '70px', 'border': '1px solid gray' });
		textbox.onkeyup = function(e) {
				this.previewSwatch.style.backgroundColor = textbox.value;
			}.bindAsEventListener(this);
			textboxCell.appendChild(textbox);
		this.customInput = textbox;

		var submitCell = entryRow.insertCell(2);
		submitCell.valign = 'bottom';
		submitCell.align = 'right';
		var submit = document.createElement('input');
		submit.type = 'button';
		Element.setStyle(submit, {'width': '40px', 'border': '1px solid gray'});
		submit.value = this.options.addLabel;
		submit.onclick = function(e) {
				var idx = 0;
				if (this.activeCustomSwatch) {
					for (var i = 0; i < this.customSwatches.length; ++i)
						if (this.customSwatches[i] == this.activeCustomSwatch) {
							idx = i;
							break;
						}
					this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
					this.activeCustomSwatch = null;
				} else {
					var lastIndex = this.loadSetting('customColorIndex');
					if (lastIndex) idx = (parseInt(lastIndex) + 1) % 8;
				}
				this.saveSetting('customColorIndex', idx);
				customColors[idx] = this.customSwatches[idx].style.backgroundColor = this.customInput.value;
				this.customSwatches[idx].onclick = this.swatchCustomClickListener(customColors[idx], this.customSwatches[idx]);
				this.customSwatches[idx].onmouseover = this.swatchHoverListener(customColors[idx]);
				this.saveSetting('customColors', customColors.join(','));
			}.bindAsEventListener(this);
		submitCell.appendChild(submit);

		// Create form
		var swatchForm = document.createElement('form');
		Element.setStyle(swatchForm, {'margin': '0', 'padding': '0'});
		swatchForm.onsubmit = function() {
			if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
			this.activeCustomSwatch = null;
			this.editor.setDialogColor(this.customInput.value);
			return false;
		}.bindAsEventListener(this);
		swatchForm.appendChild(swatchTable);

		// Add to dialog window
		cont.appendChild(swatchForm);
		return cont;
	},

	swatchClickListener: function(color) {
		return function(e) {
				if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
				this.activeCustomSwatch = null;
				this.options.onSelect(color);
			}.bindAsEventListener(this);
	},

	swatchCustomClickListener: function(color, element) {
		return function(e) {
				if (e.ctrlKey) {
					if (this.activeCustomSwatch) this.activeCustomSwatch.style.border = '1px solid #EEEEEE';
					this.activeCustomSwatch = element;
					this.activeCustomSwatch.style.border = '1px solid #FF0000';
				} else {
					this.activeCustomSwatch = null;
					this.options.onSelect(color);
				}
			}.bindAsEventListener(this);
	},

	swatchHoverListener: function(color) {
		return function(e) {
				this.previewSwatch.style.backgroundColor = color;
				this.customInput.value = color;
			}.bindAsEventListener(this);
	},

	loadSetting: function(name) {
		name = 'colorpicker_' + name;
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},

	saveSetting: function(name, value, days) {
		name = 'colorpicker_' + name;
		if (!days) days = 180;
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"="+value+expires+"; path=/";
	},

	clearSetting: function(name) {
		this.saveSetting(name,"",-1);
	}

});




Effect.Accordian = function(el){
	typeof el != 'object' ? el = $(el) : 0;
    var cls = 'curOpt';
    var pel = el.up('ul');
    var mel = pel.select('.'+cls).first();
	if(typeof mel != 'object'){
		new Effect.BlindDown(el, {scaleFromCenter:true, duration:0.25});
		el.addClassName(cls)
	}else if (mel != el){
		new Effect.Parallel([
			new Effect.BlindUp(mel, {scaleFromCenter:true}),
			new Effect.BlindDown(el, {scaleFromCenter:true})
		], {
			  duration: 0.25
		});
		mel.removeClassName(cls);
		el.addClassName(cls)
    }else{
        new Effect.BlindUp(el, {scaleFromCenter:true, duration:0.25});
		el.removeClassName(cls);
		pel.select('.curOpen').each(function(oel){
			oel.removeClassName('curOpen')
		})
    }
}

Element.addMethods({

	accordian: function(element){
		new Effect.Accordian(element)
	},

	createColorPicker: function(el){
		if(!el.hasClassName('color-on')){
			new Control.ColorPicker(el);
			el.addClassName('color-on');
			el.observe("change", function(e){
				var val = e.element().getValue();
				e.element().value = val != '' ? !val.startsWith("#") ? "#"+val : val : "#000000";
			})
		}
	},

	createCheckbox: function(el){
		new CheckboxStyle(el)
	},

	createSpinInput: function(el, arg){
		if(!el.hasClassName('spin-on')){
			new SpinInput(el, arg)
			el.addClassName('spin-on');
		}
	},

	checkInputValue: function(el, def, append, altappend){
		el.observe("change", function(e){
			var val = e.element().getValue();
			!altappend ? altappend = "" : 0;
			e.element().value = isNaN(parseInt(val)) ? def : val != '' ? !val.endsWith(append) || !val.endsWith(altappend) ? parseInt(val)+append : val : def;
			var menu_el = e.element().next('ul.menu');
			if(menu_el.hasClassName('active')){
				menu_el.hide();
				menu_el.removeClassName('active');
			}
		});
		el.observe("spinner:change", function(e){
			var val = e.element().getValue();
			e.element().value = isNaN(parseInt(val)) ? def : val != '' ? !val.endsWith(append) ? parseInt(val)+append : val : def;
			var menu_el = e.element().next('ul.menu');
			if(typeof menu_el == 'object' && menu_el.hasClassName('active')){
				menu_el.hide();
				menu_el.removeClassName('active');
			}
		})
	},

	helpText: function(el){
		new HelpTextPopup(el);
	}
});

var HelpTextPopup = Class.create({

	mouseEvent:false,

	initialize: function(el){
		this.el = $(el);
		this.txt = this.el.getAttribute("title");
		this.el.setAttribute("title", "");
		this.helpDiv = new Element("div").update(this.txt).addClassName("help-text-popup").setStyle('opacity:0');
		this.el.observe('click', function(e){
			if(this.el.hasClassName('active')){
				this.hideHelp();
			} else if(this.el.hasClassName('hover')){
				this.el.removeClassName('hover');
				this.el.addClassName('active');
			} else {
				this.hideHelp();
				this.el.insert({'after':this.helpDiv});
				this.pos = this.el.cumulativeOffset();
				this.helpDiv.setStyle({"left":(this.pos.left+25)+"px","top":(this.pos.top-9)+"px"});
				this.helpDiv.morph('opacity:0.70', { duration: 0.25 });
				this.el.addClassName('active');
			}
		}.bind(this));

		this.el.observe('mouseover', function(e){
			this.mouseEvent = true;
			Event.stop(e);
			if(!this.el.hasClassName('active') && !this.el.hasClassName('hover')){
				this.hideHelp();
				this.el.insert({'after':this.helpDiv});
				this.pos = this.el.cumulativeOffset();
				this.helpDiv.setStyle({"left":(this.pos.left+25)+"px","top":(this.pos.top-9)+"px"});
				setTimeout( function(){
					this.helpDiv.morph('opacity:0.70', { duration: 0.25 });
					this.el.addClassName('hover');
					this.mouseEvent = false
				}.bind(this), 750)
			}
		}.bind(this));

// 		this.el.observe('mouseout', function(e){
// 			Event.stop(e);
// 			if(this.mouseEvent != false && !this.el.hasClassName('active')){
// 				this.hideHelp();
// 			}
// 		}.bind(this))
	},

	hideHelp: function(){
		$('anyfont_page').select("div.help-text-popup").each(function(el){
// 			el.previous("span.help-txt").setAttribute("title", el.innerHTML);
			el.remove();
		});
		$('anyfont_page').select("span.help-txt").invoke("removeClassName", "hover");
		$('anyfont_page').select("span.help-txt").invoke("removeClassName", "active");
	}
});


Object.extend(Event, {
	wheel:function(e){
		var delta = 0;
		!e ? e = window.event : 0;
		if(e.wheelDelta){
			delta = e.wheelDelta/120;
		} else if (e.detail) {
			delta = -e.detail/3;
		}
		return Math.round(delta); //Safari Round
	}
});

var TabController = Class.create({

    initialize: function(tabs, tab_cont, tab_id_prefix){
        $(tabs).select('li').each(function(el){
            el.observe('click', function(e){
                $(tab_cont).select(".active-tab").each(function(opentab){
                    opentab.removeClassName('active-tab').setStyle({"display":"none"});
                });
                $(tabs).select(".active").each(function(tab){
                    tab.removeClassName('active')
                });
                el.addClassName('active');
                $(tab_id_prefix+el.identify()).addClassName('active-tab').setStyle({"display":"block"});
			})
        })
    }
})

var SpinInput = Class.create({
	initialize: function(el, options){
		this.options = Object.extend({
			min:null,
			max:null,
			step:1,
			page:10,
			spinClass:'shadow-spin',
			upClass:'up',
			downClass:'down',
			reset:0,
			delay:250,
			interval:100,

			_btn_width: 20,
			_btn_height: 10,
			_direction: false,
			_delay: false,
			_repeat: false
		}, options || {});

		this.element = $(el);
		this.element.observe('mousemove',this.onMouseMove.bindAsEventListener(this));
		this.element.observe('mouseover',this.onMouseMove.bindAsEventListener(this));
		this.element.observe('mouseout',this.onMouseLeave.bindAsEventListener(this));
		if(Prototype.Browser.Gecko){
			this.element.observe('DOMMouseScroll',this.mousewheel.bindAsEventListener(this),false);
			this.element.observe('input',this.onPaste.bindAsEventListener(this));//FF_paste
		}else{
			this.element.observe('mousewheel',this.mousewheel.bindAsEventListener(this),false);
		}
		if(Prototype.Browser.IE){
			this.element.observe('dblclick',this.onDblClick.bindAsEventListener(this));
			this.element.onpaste= function(){
				this.adjustValue(this.options._direction * this.options.step);
			}.bind(this);
		}
		this.element.observe('mousedown',this.onMouseDown.bindAsEventListener(this));
		this.element.observe('mouseup',this.onMouseUp.bindAsEventListener(this));
		this.element.observe('keydown',this.onKeyDown.bindAsEventListener(this));
		this.element.observe('change',this.onPaste.bindAsEventListener(this));
		this.options.reset = parseInt(this.element.value);
	},

	onDblClick:function(ev){
		this.adjustValue(this.options._direction * this.options.step);
	},

    onMouseUp:function(ev){
			// Cancel repeating adjustment
			window.clearInterval(this.options._repeat);
			window.clearTimeout(this.options._delay);
    },

	onMouseDown:function(ev){
		if (this.options._direction == 0)
			return;
		this._val = this.options._direction * this.options.step;
		this.adjustValue();

      // Initial delay before repeating adjustment
		this.options._delay = window.setTimeout(function() {
			this._val = this.options._direction * this.options.step;
			this.adjustValue();
			// Repeat adjust at regular intervals
			this.options._repeat = window.setInterval(function() {
				this._val = this.options._direction * this.options.step;
				this.adjustValue();
			}.bind(this), this.options.interval);
		}.bind(this), this.options.delay);
    },

	onKeyDown:function(ev){
		switch(ev.keyCode){
			case Event.KEY_UP:
				this._val = this.options.step;
				this.adjustValue();
				ev.stop();
				break;
			case Event.KEY_DOWN:
				this._val = -this.options.step;
				this.adjustValue();
				ev.stop();
				break;
			case Event.KEY_PAGEUP:
				this._val = this.options.page;
				this.adjustValue();
				ev.stop();
				break;
			case Event.KEY_PAGEDOWN:
				this._val = -this.options.page;
				this.adjustValue();
				ev.stop();
				break;
		}
    },

	onPaste:function(ev){
		this._val = 0;
		this.adjustValue();
	},

	adjustValue: function(){
		this._val = (isNaN(parseInt(this.element.value)) ? this.options.reset : parseInt(this.element.value)) + parseInt(this._val);
		this._val = (this.options.min !== null) ? Math.max(this._val, this.options.min) : 0;
		this._val = (this.options.max !== null) ? Math.min(this._val, this.options.max): 0;
		this.element.value = this._val;
		this.element.fire("spinner:change");
	},

    onMouseMove:function(ev){
      var of = this.element.cumulativeOffset();// [left, top]
      var direction = (Event.pointerX(ev) > of[0] + this.element.getWidth() - this.options._btn_width)
        ? ((Event.pointerY(ev) < of[1] + this.options._btn_height) ? 1 : -1) : 0;

        if (direction !== this.options._direction) {
        // Style up/down buttons:
        switch(direction){
          case 1: // Up arrow:
            this.element.removeClassName(this.options.downClass).addClassName(this.options.upClass);
            break;
          case -1: // Down arrow:
            this.element.removeClassName(this.options.upClass).addClassName(this.options.downClass);
            break;
          default: // Mouse is elsewhere in the textbox
            this.element.removeClassName(this.options.upClass).removeClassName(this.options.downClass);
			this.onMouseUp();
        }
        this.options._direction = direction;
        }
    },

	onMouseLeave: function(ev){
		this.element.removeClassName(this.options.upClass).removeClassName(this.options.downClass);
	},

    mousewheel:function(e){
		Event.stop(e);
		if(Event.wheel(e) >= 1){
			this._val = this.options.step;
			this.element.addClassName(this.options.upClass);
			this.adjustValue();
			setTimeout(function(){
				this.element.removeClassName(this.options.upClass);
			}.bind(this), 250);
		}
		else if(Event.wheel(e) <= -1){
			this._val = -this.options.step;
			this.element.addClassName(this.options.downClass);
			this.adjustValue();
			setTimeout(function(){
				this.element.removeClassName(this.options.downClass);
			}.bind(this), 250);
		}
	}
});

/*
Based on the FancyBoxes script: http://projects.functino.com/fancyboxes/
Which in turn was inspired by Stephane Caron's jQuery plugin "prettyCheckboxes": http://www.no-margin-for-errors.com/projects/prettyCheckboxes/

*/

var InputStyles = Class.create({

	initialize: function(options){
		this.options = Object.extend({
			"elements": 'input[type=checkbox],input[type=radio]',//by default convert all checkboxes and radio-elements to FancyBoxes, elements can either be a string (used as css-selector $$()) or an array of input-elements
			"autoBind": true, // convert boxes automatically, if set to false you have to call FancyBoxes.bind()
			"cssPrefix": 'anyfont_', // prefix for all css-classes used by FancyBoxes
			"display": 'inline', // display the box as inline or block
			"images": false
		}, options || {});
		this.changeActionEvent = this.changeAction.bindAsEventListener(this);
		this.resetActionEvent = this.resetAction.bindAsEventListener(this);
		this.mousedownActionEvent = this.mousedownAction.bindAsEventListener(this);
		this.mouseupActionEvent = this.mouseupAction.bindAsEventListener(this);
		this.elements = this.options.elements;
		// this.elements can either be a css-selector string or an array of elements
		Object.isString(this.elements) ? this.elements = $$(this.elements) : 0;
		this.options.autoBind ? this.bindInputs() : 0;

	},

	bindInputs: function(){
		// to avoid "double" checkboxes, unbind FancyBoxes first
		this.unbindInputs();
		this.elements.each(function(el){
			this.changeInput(el)
		}.bind(this));
	},

	changeInput: function(it){
		this.label = this.findLabel(it);
		if(!this.label){
			return false;
		}
		this.inputEl = it;
		this.holder = new Element('span', {'className': this.options.cssPrefix + 'holder'});
		this.holderWrap = new Element('span', {'className': this.options.cssPrefix + 'holderWrap'}).insert(this.holder); // avoid code like <label><span>...</span><input ..> text</label>because IE doesn't trigger click events then
		this.inputEl.up('label') ? this.inputEl.insert({after: this.holderWrap}) : this.inputEl.insert({top: this.holderWrap}); // <label><input ... > [insert here] text</label> OR <input id="x" > <label for="x"> [insert here] text </label>
		this.inputEl.checked ? this.label.addClassName(this.options.cssPrefix + 'checked') : 0;
		this.label.addClassName(this.options.cssPrefix + 'box')
		.addClassName(this.options.cssPrefix + this.inputEl.getAttribute('type'))
		.addClassName(this.options.cssPrefix + this.options.display);
		// Hide the original input-elements
		!Prototype.Browser.IE ? this.inputEl.hide() : this.inputEl.setStyle({'position':'absolute','top':'-10000px','left':'-10000px'});

		if(this.options.images){
			if(this.inputEl.getAttribute('type').toLowerCase() == 'checkbox')
				this.holder.setStyle({backgroundImage: 'url(' + this.options.images.checkbox + ')'});
			else
				this.holder.setStyle({backgroundImage: 'url(' + this.options.images.radio + ')'});
		}
		this.inputEl.observe('change', this.changeActionEvent);
		this.inputEl.observe("btn:change", this.changeActionEvent);
		this.inputEl.observe("btn:reset", this.resetActionEvent);
		this.label.observe('mousedown', this.mousedownActionEvent);
		$$("body").first().observe('mouseup', this.mouseupActionEvent)
	},

	resetAction: function(e){
		this.label = this.findLabel(e.element());
		if(this.label.hasClassName(this.options.cssPrefix + 'checkbox')){
			e.element().checked = false;
			this.label.removeClassName( this.options.cssPrefix + 'checked');
		}
	},

	changeAction: function(e){
		this.label = this.findLabel(e.element());
		if(this.label.hasClassName(this.options.cssPrefix + 'checkbox')){
			this.label.toggleClassName( this.options.cssPrefix + 'checked');
			if(e.element().getValue() == "on"){
				e.element().checked = false;
			} else {
				e.element().checked == true;
			}
		} else if(this.label.hasClassName(this.options.cssPrefix + 'radio')){
			$$('input[name="'+ e.element().getAttribute('name')+'"][type=radio]').each(function(el){// Uncheck all radios
				this.findLabel(el).removeClassName(this.options.cssPrefix + 'checked');
			}.bind(this));
			this.label.addClassName(this.options.cssPrefix + 'checked');
			e.element().checked = true;
			this.label.down("input").fire("radio:change");
		}
	},
// 		this.inputEl.observe('focus', function(ev){
// 			this.label.addClassName(options.cssPrefix + 'focus');
// 		}.bind(this));
// 		this.inputEl.observe('blur', function(ev){
// 			this.label.removeClassName(options.cssPrefix + 'focus');
// 		}.bind(this));

	mousedownAction: function(ev){
		ev.element().addClassName(this.options.cssPrefix + 'hover');
	},

	mouseupAction: function(ev){
		ev.element().removeClassName(this.options.cssPrefix + 'hover');
	},

	unbindInputs: function(){
		this.elements.each(function(el){
			this.label = this.findLabel(el);

			// remove event listeners
			el.stopObserving('change', this.changeActionEvent);
			this.label.stopObserving('mousedown', this.mousedownActionEvent);
			this.label.stopObserving('mouseup', this.mouseupActionEvent)
// 			it.stopObserving('focus');
// 			it.stopObserving('blur');

		// remove added classes
			!Prototype.Browser.IE ? el.show() : el.setStyle({'position':'relative','top':'0','left':'0'});
			this.label.removeClassName(this.options.cssPrefix + 'box');
			this.label.removeClassName(this.options.cssPrefix + el.getAttribute('type'));
			this.label.removeClassName(this.options.cssPrefix + this.options.display);
			this.label.removeClassName(this.options.cssPrefix + 'checked');
// 			this.label.removeClassName(this.options.cssPrefix + 'focus');

		// remove inserted elements
			var span = this.label.down('.' + this.options.cssPrefix + 'holderWrap');
			if(span){
				span.remove();
			}
		}.bind(this));
	},

	// function to find the label for each input, detects labels in two ways
	findLabel: function(input){
		// detect <label><input type="text" name="a" /> MyLabel</label>
		var label = input.up('label');
		if(!label){
		// detect <input type="text" name="a" id="a" /> <label for="a">MyLabel</label>
			var labels = $$('label[for="' + input.getAttribute('id') + '"]');
			var label = labels[0];
		}
		return label;
	}
});

var AnyFont = {

	ajaxUrl: false,
	otn: false,
	charMaps: [],

	showOptions: function(el){
		$(el).style.display = $(el).style.display == 'none' ? '' : 'none';
	},

	optionSwap: function(input, el, elid){
		if($(input).checked == true){
			try{
				$(el).checked=false
			}catch(e){}
			$(elid).style.display = 'none';
		} else {
			$(elid).style.display = '';
		}
	},

	toggleNew: function(elID){
		try{
			$(elID).getStyle('display') == 'none' ? new Effect.BlindDown(elID, {scaleFromCenter:true, duration:0.5}) : new Effect.BlindUp(elID, {scaleFromCenter:true, duration:0.5});
		} catch(e){}
	},

	selectAll: function(el, val){
		this.val = val
		$(el).select(".clist").each(function(el){
			el.checked = !this.val.checked ? false : true;
		}.bind(this));
	},

	showFontDetails: function(fontid){
		this.elCont = $(fontid+'_font_details');
		$('anyfont-fontlist').select('.curOpen').invoke("removeClassName", "curOpen");
		this.elCont.up('li.anyfont-font-block').addClassName('curOpen');
		this.elCont.accordian();
	},

	showCharacterMap: function(fontname, fontid){
            this.elCont = $(fontid+'_character_map_cont');
			this.el = $(fontid+'_character_map');
			this.show = true;
			$('anyfont-fontlist').select('.curOpenMap').each(function(el){
				el.removeClassName("curOpenMap");
				if(el == this.elCont){
					this.show = false;
				} else{
					el.hide();
				}

			}.bind(this));
			if(this.show != false){
				this.elCont.addClassName('curOpenMap');
				new Effect.BlindDown(this.elCont, {"duration":0.25});
			}  else {
				this.elCont.removeClassName("curOpenMap");
				new Effect.BlindUp(this.elCont, {"duration":0.25});
			}
            this.map = false;
            this.charMaps.each(function(map){
				if(map == fontid){
                    this.map = true;
					$(map+'_character_map').down('img').setStyle({"max-width":($(map+'_character_map').up('div.charmap').getWidth()-40)+"px"})
				}
            }.bind(this))
            if(!this.map){
                this.el.insert(new Element("span", {"className":"loading_msg"}).update("Character Map Loading... "));
				this.cm = new AnyFont.CharacterMap(fontname, fontid);

            }
    },

    CharacterMap: Class.create({

        initialize: function(fontname, fontid){
            this.charmap = new Image();
            this.charmap.src = af_set.permalinks ? af_set.imageurl+"admin/"+fontname+"/charactermap.png" : af_set.imageurl+"admin&txt="+fontname+"&displaytext=charactermap" ;
			AnyFont.charMaps.push(fontid);
            this.charmap.onload = function(){
				$(fontid+'_character_map').update(this.charmap);
				this.charmap.setStyle({"max-width":($(fontid+'_character_map').up('div.charmap').getWidth()-40)+"px"});
            }.bind(this);
        }
	}),

	updateStyle: function(fel){
		AnyFont.showMessage(af_i18n.msg_saving_style, false);
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: Form.serialize(fel)+'&action=anyfont_edit_styles',
			onSuccess: function(transport){
				this.resp = transport.responseJSON;
				if(this.resp.savestatus == "saved"){
					AnyFont.showMessage(af_i18n.msg_saved_style, 5);
					this.img = new Image();
					this.img.src = 'data:image/png;base64,'+this.resp.img;
					this.imgStyleID = this.resp.stylename.gsub(" ", "__")
					this.imgID = 'preview_image_'+this.imgStyleID;
					$(this.imgID).replace(this.img);
					this.img.id = this.imgID;
					this.img.addClassName('anyfont-style-preview');
					try{
						$('new_'+this.imgID).remove()
					}catch(e){}
				} else if(this.resp.savestatus == "savedNew"){
					AnyFont.showMessage(this.resp.msg, 5);
					this.upel = $('anyfont-list-'+this.resp.type).down('ul.style-list');
					AnyFont.toggleNew('anyfont-style-new-'+this.resp.type);
					AnyFont.toggleNew('anyfont-'+this.resp.type+'-preview');
					this.upel.insert(this.resp.styleblock);
					this.newstylename = this.resp.stylename.gsub(" ", "__");
					$(this.newstylename).select("input.colorinput").invoke('createColorPicker');
					$(this.newstylename).select("input.font-size").invoke('checkInputValue', "18pt", "pt");
					$(this.newstylename).select("input.shadow-spread").invoke('createSpinInput', {min:1, max:10});
					$(this.newstylename).select("input.shadow-distance").invoke('createSpinInput', {min:0, max:20});
                    $(this.newstylename).select("input.shadow-distance").invoke('checkInputValue', "1px", "px");
                    $(this.newstylename).select("input.padding").invoke('createSpinInput', {min:0, max:100})
					$(this.newstylename).select("input.padding").invoke('checkInputValue', "0px", "px");
					$(this.newstylename).select("input.max-width").invoke('createSpinInput', {min:0, max:5000});
					$(this.newstylename).select("input.max-width").invoke('checkInputValue', "0px", "px");
					$(this.newstylename).select("input.line-height").invoke('createSpinInput', {min:0, max:500});
					$(this.newstylename).select("input.line-height").invoke('checkInputValue', "0px", "px");
// 					AnyFont.styleOptionsHide();
					AnyFont.stylesAccordian();
					new CheckboxStyle($('anyfont-options-'+this.newstylename));
					new InputStyles({elements: 'input[type=radio]'});
					new InputStyles({elements: '.font-style-input'});
				} else {
					AnyFont.showMessage(this.resp.error, 10);
				}
			}.bind(this)
		});
	},

	clearCache: function(){
		var response = confirm(af_i18n.chk_clear_cache);
		if(response){
			AnyFont.showMessage(af_i18n.msg_clear_cache, false);
			new Ajax.Request(AnyFont.ajaxUrl, {
				parameters: 'action=anyfont_clear_cache',
				onSuccess: function(transport){
					resp = transport.responseJSON;
					$(resp.block).replace(resp.content);
					AnyFont.showMessage(resp.message, 5);
				}
			});
		}
	},

	fontUploaded: function(){
		var resp = (frames['upload_target'].document.getElementsByTagName("body")[0].innerHTML).evalJSON();
		if(resp.success){
			AnyFont.showMessage(resp.file_name+" "+af_i18n.msg_upload_success, 5);
			$("anyfont-fontlist").insert({'top':(AnyFont.decode(resp.fontlist)).gsub("+", " ")});
			$('font').setValue('');
		} else {
			AnyFont.showMessage(af_i18n.err_upload_failed+" "+resp.failure, 5);
		}
	},

	decode: function(string){
		return AnyFont._utf8_decode(unescape(string));
	},

	_utf8_decode: function(utftext){
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	},

	deleteFont: function(font, fontid){
        this.confirmed = confirm(af_i18n.chk_del_fonts+"\n\n"+af_i18n.del_font_note);
        if(this.confirmed){
            if(!font){
                this.fontlist = [];
                this.fontidlist = [];
                $('anyfont-fontlist').select('.clist').each(function(el){
                    this.na = el.name.split("_checkbox_");
                    if(el.getValue() == "on"){
                        this.fontlist.push(this.na[0]);
                        this.fontidlist.push(this.na[1]);
                    }
                }.bind(this));
                if(this.fontlist.length > 0){
                    this.param = 'action=anyfont_delete_font&fonts='+this.fontlist;
                    this.fontlist.each(function(font, i){
                        $(this.fontidlist[i]+'_item').up("li.anyfont-font-block").remove();
                    }.bind(this));
                    AnyFont.showMessage(af_i18n.msg_del_fonts, 5);
                } else {
                    AnyFont.showMessage(af_i18n.err_select_font, 5);
                    return false;
                }
            }else{
                this.param = 'action=anyfont_delete_font&font-name='+font;
                AnyFont.showMessage(af_i18n.msg_del+" "+font+"...", 5);
				if($(fontid+'_item').up("ul#extrapreviews").select(".style-list-item").size() > 1){
					$(fontid+'_item').remove();
				}else{
					$(fontid+'_item').up("li.anyfont-font-block").remove();
				}
            }
            new Ajax.Request(AnyFont.ajaxUrl, {
                parameters: this.param,
                onSuccess: function(transport){
                    AnyFont.showMessage(transport.responseText, 5);
                }
            });
        }
	},

	deleteStyle: function(style, styleid, type){
		if(!style){
			this.stylelist = [];
			$('anyfont-list-'+type).select('.clist').each(function(el){
				this.na = el.name.split("_checkbox");
				el.getValue() == "on" ? this.stylelist.push(this.na[0]):0;
			}.bind(this));
			if(this.stylelist.length > 0){
				this.confirmed = confirm(af_i18n.chk_del_styles+"\n\n"+af_i18n.del_style_note);
				if(this.confirmed){
					this.param = 'action=anyfont_delete_style&type='+type+'&styles='+this.stylelist;
					this.stylelist.each(function(style){
						$(style.gsub(" ", "__")+'_item').up("li.anyfont-style-block").remove();
					});
					AnyFont.showMessage(af_i18n.msg_del_styles, 5);
				}else{
					return false;
				}
			} else {
				AnyFont.showMessage(af_i18n.err_select_style, 5);
				return false;
			}
		}else{
			this.confirmed = confirm(af_i18n.chk_del_style+"\n\n"+af_i18n.del_style_note);
			if(this.confirmed){
				this.param = 'action=anyfont_delete_style&type='+type+'&style-name='+style;
				AnyFont.showMessage(af_i18n.msg_del+" "+style+"...", 5);
				$(style.gsub(" ", "__")+'_item').up("li.anyfont-style-block").remove();
			}else{
				return false;
			}
		}
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: this.param,
			onSuccess: function(transport){
				AnyFont.showMessage(transport.responseText, 5);
			}
		});
	},

	convertFont: function(fontname, fontid, display_name, type){
		this.param = 'action=anyfont_convert_font&fontname='+fontname+'&type='+type;
		this.printtype = type == 'css3' ? "'@font-face' CSS" : "Cufon";
		AnyFont.showMessage("Processing "+display_name+" at FontServ.com to enable compatibility with "+this.printtype+" styles. Please wait...", 100);
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: this.param,
			onSuccess: function(transport){
				resp = transport.responseJSON;
				AnyFont.showMessage(resp.msg, 10);
				if(resp.success){
					$(fontid+'_'+type+'_status').removeClassName("No").addClassName("Yes");
					$(fontid+'_'+type+'_convert_button').remove();
				}
			}
		});
	},

	startUpload: function(){
		AnyFont.showMessage(af_i18n.msg_upload_start, false);
	},

	showMessage: function(msg, timeout){
		this.offset = document.viewport.getScrollOffsets();
		this.msgbox = $("anyfont-upload-messages");
		if(this.offset[1] > 0){
			this.msgbox.style.position = "absolute";
			this.msgbox.style.top = (this.offset[1] + 50)+"px";
			this.msgbox.style.left = "250px";
			this.msgbox.style.padding = "15px 50px"
			this.msgbox.style.background = "rgb(255, 251, 204) url("+af_set.url+"wp-content/plugins/anyfont/img/info.png) no-repeat 0 0";
		} else {
			this.msgbox.style.position = "relative";
			this.msgbox.style.top = "";
			this.msgbox.style.left = "";
			this.msgbox.style.padding = "5px";
			this.msgbox.style.background = this.msgbox.style.backgroundColor
		}
		this.msgbox.update(msg);
		this.msgbox.getStyle('display') == 'none' ? new Effect.Appear("anyfont-upload-messages") : 0;
		if(timeout != false){
			setTimeout("AnyFont.hideMessage()", (timeout*1000));
		}
	},

	hideMessage:  function(){
		$("anyfont-upload-messages").getStyle('display') != 'none' ? new Effect.Fade("anyfont-upload-messages") : 0;
	},

	stylesAccordian: function(){
		if( (typeof $('anyfont-tab-container') ) === "object" ){
			$('anyfont-tab-container').select('.anyfont-style-edit').each(function(action_el){
				if(!action_el.hasClassName('accord')){
					action_el.observe('click', function(e){
						this.el = e.element();
						this.el.up('ul.style-list').select('.curOpen').each(function(oel){
							oel.removeClassName('curOpen');
// 							this.restoreOrig = oel.down('span.font-preview').clone();
// 							this.restoreOrig.update(oel.down('span.font-preview').innerHTML);
// 							oel.down('span.font-preview').remove()
// 							oel.down('ul.style-list-item').down('li.style-list-preview').update(this.restoreOrig);
						});
// 						this.preview = this.el.up('ul.style-list-item').down('span.font-preview').clone();
// 						this.preview.update(this.el.up('ul.style-list-item').down('span.font-preview').innerHTML);
// 						this.el.up('ul.style-list-item').down('span.font-preview').remove();
// 						this.el.up('ul.style-list').down('div.anyfont-css-preview').update(this.preview);
						this.el = this.el.up('li.anyfont-style-block').addClassName('curOpen').down('div');
						this.el.accordian();
// 						try{$('preview_image_container').hide()}catch(e){}
					});
					action_el.addClassName('accord');
				}
			});
		}
	},

	styleOptionsHide: function(){
		if( typeof( $('anyfont-tab-container') ) === 'object' ){
			$('anyfont-tab-container').select('.anyfont-options-block').invoke('hide');
			$('anyfont_page').select('.hidden_option').each(function(el){
				!el.previous("div.anyfont_checkbox").hasClassName('anyfont_checkbox_on') ? el.hide() :0;
			});
		}
		$('anyfont-tab-container').select('.anyfont-style-new').invoke('hide');
	},

	toggleDisabled: function(el){
		dropdown = $(el).up('div').next('select');
		el.getValue() == 'on' ? dropdown.enable() : dropdown.disable();
	},

	toggleHidden: function(el){
		hidden_el = $(el).up('div').next('div.hidden_option');
		$(el).getValue() == 'on' ? new Effect.BlindUp(hidden_el, {scaleFromCenter:true, duration:0.3}) : new Effect.BlindDown(hidden_el, {scaleFromCenter:true, duration:0.3});
		AnyFont.setShadow(el);
	},

	setShadow: function(e){
		if(Object.isElement(e)){
			this.shadowID = e.identify().gsub("shadow-color-", "");
			this.shadowID = this.shadowID.gsub("shadow-spread-", "");
			this.shadowID = this.shadowID.gsub("shadow-distance-", "")
		} else {
			this.shadowID = e.element().identify().gsub("shadow-distance-", "");
			this.shadowID = this.shadowID.gsub("shadow-spread-", "")
		}
		if($("text-shadow-"+this.shadowID).getValue() == "on"){
			$('anyfont-'+this.shadowID+'-preview').setStyle("text-shadow:"+
												$('shadow-distance-'+this.shadowID).getValue()+' '+
												$('shadow-distance-'+this.shadowID).getValue()+' '+
												$('shadow-spread-'+this.shadowID).getValue()+'px '+
												$('shadow-color-'+this.shadowID).getValue()
											);
		} else {
			$('anyfont-'+this.shadowID+'-preview').setStyle("text-shadow:0 0 0 transparent");
		}
	},

	setLineHeight: function(e){
		this.lhID = e.element().identify().gsub("line-height-", "");
		this.lhval = $("line-height-"+this.lhID).getValue();
		$('anyfont-'+this.lhID+'-preview').setStyle("line-height:"+(this.lhval == "0px" ? "1" : this.lhval));
	},

	toggleDropMenu: function(el){
		var menu_el = $(el).next('ul.menu');
		var menu_pos = $(el).cumulativeOffset();
		menu_el.setStyle({"left":menu_pos.left+"px","top":(menu_pos.top+$(el).getHeight())+"px"});
		if(menu_el.hasClassName('active')){
			menu_el.hide();
			menu_el.removeClassName('active');
		} else {
			$('anyfont_page').select("ul.active").each(function(el){
				el.hide();
				el.removeClassName('active');
			})
			var menu_height = menu_el.select('li').size()*30;
			menu_height > 265 ? menu_height = 265 : 0;
			menu_el.setStyle({"height":menu_height+"px"});
			menu_el.show();
			menu_el.addClassName('active');
		}
	},

	selectOption: function(el, val, fontfile, type){
		this.fontinput = !type ? $(el) : $(el+'-'+type);
		if(!fontfile){
			this.fontinput.value = val;
			if(typeof $('anyfont-'+type+'-preview') != 'undefined' && type == "css"){
				$('anyfont-'+type+'-preview').setStyle("font-size:"+val)
			}
		} else {
			this.fontinput.value = fontfile;
			this.fontfile = fontfile;
			try{$("_"+el+'-'+type).value = val}catch(e){};
			try{$("_"+el).value = val}catch(e){};
			if(typeof $('anyfont-'+type+'-preview') != 'undefined'){
					try{
						if(!Object.isString(this.fontfile)){
							this.keys = Object.keys(this.fontfile);
							this.val = val;
							this.keys.each(function(k){
								this.insertFontFamily(this.val, this.fontfile[k], k);
							}.bind(this));
						} else {
							this.insertFontFamily(val, this.fontfile, 'normal');
						}
					$('anyfont-'+type+'-preview').setStyle("font-family:"+val);
				}catch(e){};
			}
		}
		AnyFont.toggleDropMenu(this.fontinput.identify());
	},

	insertFontFamily: function(val, fontfile, style){
		if(style != 'normal'){
			var fstyle = 'font-style:';
			var fweight = 'font-weight:';
			switch(style){
				default:
				case "Regular":
					fstyle="";
					fweight+="normal";
					break;
				case "Medium":
					fstyle="";
					fweight+="medium";
					break;
				case "Bold":
					fstyle="";
					fweight+="bold";
					break;
				case "Oblique":
					fstyle+="oblique";
					fweight+="normal";
					break;
				case "BoldOblique":
				case "Bold Oblique":
					fstyle+="oblique";
					fweight+="bold";
					break;
				case "Italic":
					fstyle+="italic";
					fweight+="normal";
					break;
				case "BoldItalic":
				case "Bold Italic":
					fstyle+="italic";
					fweight+="bold";
					break;
				
			}
			
		}

		if(!Prototype.Browser.IE){
				$$("head").first().insert("<style>@font-face { font-family: '"+val+"'; src: url('/wp-content/uploads/fonts/"+fontfile+".eot');src: local('☺'), url('/wp-content/uploads/fonts/"+fontfile+".woff') format('woff'), url('/wp-content/uploads/fonts/"+fontfile+".ttf') format('truetype');"+fweight+";"+fstyle+"}</style>");
				return fweight+";"+fstyle;
		} else if(type == 'css'){
			AnyFont.showMessage("When using Internet Explorer, the newly selected font will only display after you have saved your style and refreshed the page.", 5);
// 				$('anyfont-'+type+'-preview').insert({"after":"<style>@font-face { font-family: '"+val+"'; src: url('/wp-content/uploads/fonts/"+fontfile+".eot');src: local('☺'), url('/wp-content/uploads/fonts/"+fontfile+".woff') format('woff'), url('/wp-content/uploads/fonts/"+fontfile+".ttf') format('truetype');font-weight: normal;font-style: normal;}</style>"});
		}
	},

	updateOptions: function(frm){
		if(frm == "fontserv_options"){
			$("anyfont_status").remove();
		}
		AnyFont.showMessage(af_i18n.msg_saving_settings, 5);
		this.params = $(frm).serialize();
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: 'action=anyfont_update_option&'+this.params,
			onSuccess: function(transport){
				resp = transport.responseJSON;

				switch(resp.type){

					case 'message':
						AnyFont.showMessage(resp.message, 5);
						break;

					case 'replace':
						$(resp.block).replace(resp.content);
						AnyFont.showMessage(resp.message, 5);
						break;

					case 'update':
						$(resp.block).update(resp.content);
						AnyFont.showMessage(resp.message, 5);
						if(resp.create_cb){
							new CheckboxStyle($(resp.create_cb));
							$(resp.block).select("span.help-txt").invoke('helpText')
						}
						break;
				}
			}
		});
	},

	previewStyle: function(fel){
		AnyFont.showMessage(af_i18n.msg_preview_style, false);
		if(typeof this.previewEl === 'object'){
			this.previewEl.remove();
			delete this.previewEl;
			delete this.previewImg;
		}
		new Ajax.Request(AnyFont.ajaxUrl, {
			parameters: Form.serialize(fel)+'&action=anyfont_preview_style',
			onSuccess: function(transport){
				this.resp = transport.responseJSON;
				if(this.resp.savestatus == "saved"){
					this.previewEl = new Element("div", {"id":"preview_image_container"}).observe('click', function(e){
						this.previewEl.remove();
						delete this.previewEl;
						delete this.previewImg;
					}.bind(this));
					this.previewEl.insert(new Element("div").update("click to remove"));
					this.previewImg = new Image();
					this.previewImg.src = 'data:image/png;base64,'+this.resp.img;
					this.parentEl = $('anyfont-options-'+this.resp.stylename);
					this.previewEl.setStyle({"left":((this.parentEl.getDimensions().width/2) + this.parentEl.cumulativeOffset()[0])+"px",
											 "top":((this.parentEl.getDimensions().height/4) + this.parentEl.cumulativeOffset()[1])+"px"
											});
					this.previewEl.insert({"top":this.previewImg});
					document.body.insert(this.previewEl);
					AnyFont.hideMessage()
				} else {
					AnyFont.showMessage(this.resp.error, 10);
				}
			}.bind(this)
		})
	},

	copyStyle: function(fel, type){
		this.styleObj = Form.serialize(fel, true);
		this.styleKeys = Object.keys(this.styleObj);
		$(fel).up('div.anyfont-options-block').removeClassName("curOpen").accordian();
		$('anyfont-style-new-'+type).getStyle('display') == 'none' ? AnyFont.toggleNew('anyfont-style-new-'+type) : 0;
		if(type === 'css'){
			$('anyfont-css-preview').getStyle('display') == 'none' ? AnyFont.toggleNew('anyfont-css-preview'):0;
			$("font-style-bold-"+type).fire("btn:reset");
			$("font-style-italic-"+type).fire("btn:reset");
			$("font-style-underline-"+type).fire("btn:reset");
			$$("head").first().insert("<style>@font-face { font-family: '"+this.styleObj["_font-family"]+"'; src: url('/wp-content/uploads/fonts/"+this.styleObj["font-family"]+".eot');src: local('"+this.styleObj["_font-family"]+"'), url('/wp-content/uploads/fonts/"+this.styleObj["font-family"]+".woff') format('woff'), url('/wp-content/uploads/fonts/"+this.styleObj["font-family"]+".ttf') format('truetype');font-weight: normal;font-style: normal;}</style>");
			$('anyfont-css-preview').setStyle("font-family:"+this.styleObj["_font-family"]);
			if(this.styleObj["text-shadow"] == 'on'){
				$('anyfont-css-preview').setStyle('text-shadow:'+this.styleObj["shadow-distance"]+' '+this.styleObj["shadow-distance"]+' '+this.styleObj["shadow-spread"]+'px '+this.styleObj["shadow-color"])
			}
		}
		this.styleKeys.each(function(key){
			if(type === 'css'){
				if(key != "update_style" && key != "style-type" && key !=  "_font-family" && key !=  "font-family" && key != "text-shadow" && key !=  "shadow-color" && key !=  "shadow-distance" && key != "shadow-spread"){
					$('anyfont-css-preview').setStyle(key+":"+this.styleObj[key]);
				}
			}
			if(key == 'image-padding' || key == 'limit-width' || key == 'shadow' || key == 'text-shadow'){
// 				key == 'text-shadow' ? key = 'shadow' : 0;
				if($(key+'-'+type).getValue() != 'on'){
					try{AnyFont.toggleHidden(key+'-'+type)}catch(e){};
					$(key+'-'+type).checked = true;
					this.chkel = $(key+'-'+type).up("div.anyfont_checkbox");
					this.chkel.addClassName("anyfont_checkbox_on");
					this.chkel.style.backgroundPosition = "200px -48px";
				}
			}else if(key == "font-weight" || key ==  "font-style" || key == "text-decoration" || key == "text-align"){
					switch(key){

						case "font-weight":
							$("font-style-bold-"+type).fire("btn:change");
							break;

						case "font-style":
							$("font-style-italic-"+type).fire("btn:change");
							break;

						case "text-decoration":
							$("font-style-underline-"+type).fire("btn:change");
							break;

						case "text-align":
							$("text-align-"+this.styleObj[key]+"-"+type).fire("btn:change");
							break;
					}
			}else if(key != 'update_style'){
				try{$(key+'-'+type).setValue(this.styleObj[key])}catch(e){}
			}
			if(key == 'background-color' || key == 'shadow-color' || key == 'color'){
				try{$(key+'-'+type).next('div').setStyle({'backgroundColor':this.styleObj[key]})}catch(e){}
			}
		}.bind(this));
		$('anyfont_page').scrollTo();
		Form.focusFirstElement('anyfont-style-new-form-'+type)
	},

	updatePreview: function(e, type){
		switch(e.getAttribute('name')){

			case 'update_style':
				$('anyfont-css-preview').update(e.getValue());
				break;

			case 'text-align':
				$('anyfont-'+type+'-preview').setStyle("text-align:"+e.getValue());
				break;

			case 'font-weight':
				$('anyfont-'+type+'-preview').getStyle('font-weight') == 'bold' ? $('anyfont-'+type+'-preview').setStyle('font-weight:normal') : $('anyfont-'+type+'-preview').setStyle('font-weight:bold');
				break;

			case 'font-style':
				($('anyfont-'+type+'-preview').getStyle('font-style') == 'oblique' || $('anyfont-'+type+'-preview').getStyle('font-style') == 'italic') ? $('anyfont-'+type+'-preview').setStyle('font-style:normal') : $('anyfont-'+type+'-preview').setStyle('font-style:oblique');
				break;

			case 'text-decoration':
				$('anyfont-'+type+'-preview').getStyle('text-decoration') == 'underline' ? $('anyfont-'+type+'-preview').setStyle('text-decoration:none') : $('anyfont-'+type+'-preview').setStyle('text-decoration:underline');
				break;

			case 'line-height':
				$('anyfont-'+type+'-preview').setStyle('line-height:'+e.getValue());
				break;

		}
		return false;
	},

	insertCSSRule: function(){
		this.addnewbtn = $('add_new_row');
		this.rulenum = parseInt($('new_rules').getValue())+1;
		$('css_custom_rule_block').insert({"bottom":new Element("input", {"type":"hidden", "name":'new_rule['+this.rulenum+']', "value":"anyfont_new_"+this.rulenum})});
		$('css_custom_rule_block').insert({"bottom":new Element("input", {"type":"text", "name":'anyfont_new_'+this.rulenum+'[result]', "style":"width:217px;"})});
		this.selectblock = new Element("select", {"className":'style-select', "name": 'anyfont_new_'+this.rulenum+'[style]'});
		this.selectblock.insert(new Element("option", {"value":'false'}).update("Select CSS3 Style..."));
		af_set.cssstyles.each(function(style){
			this.selectblock.insert(new Element("option", {"value":style}).update(style));
		}.bind(this));
		$('css_custom_rule_block').insert({"bottom":this.selectblock});
		this.help_ico = new Element("span",{"className":"help-txt", "title":af_i18n.css3_new_custom_help});
		$('css_custom_rule_block').insert({"bottom":this.help_ico});
		$('add_new_row').remove();
		$('css_custom_rule_block').insert({"bottom":this.addnewbtn});
		$('css_custom_rule_block').insert({"bottom":"<br /><br />"});
		$('new_rules').value = this.rulenum;
		this.help_ico.helpText();
	},

	activateCheckboxes: function(el){
		new CheckboxStyle($(el))
	},

	deleteCustomCSS: function(cssrule){
		this.confirm = confirm("Are you sure you want to delete this custom css rule?");
		if(this.confirm){
			AnyFont.showMessage(af_i18n.msg_del_css, false);
			new Ajax.Request(AnyFont.ajaxUrl, {
				parameters: 'action=anyfont_delete_custom_css&css_rule='+cssrule,
				onSuccess: function(transport){
					this.resp = transport.responseJSON;
					if(this.resp.result == "success"){
						$('custom_'+this.resp.delid).remove();
						AnyFont.showMessage(this.resp.message, 5);
					}
				}
			})
		}
	}
}

var CheckboxStyle = Class.create({

	initialize: function(parentEl){
		this.parentEl = parentEl;
		this.parentEl.select("div.anyfont_checkbox").each(function(e){
			e.style.background = "transparent url("+af_set.url+"wp-content/plugins/anyfont/img/checkbox.gif) no-repeat scroll 200px 2px";
			if(e.hasClassName('anyfont_checkbox_on')){
				e.style.backgroundPosition = "200px -48px";
			} else {
				e.style.backgroundPosition = "200px 2px";
			}
			e.down('input').hide();
			e.observe("mousedown", function(event){
				this.del = event.element();
				!this.del.hasClassName('anyfont_checkbox') ? this.del = this.del.up("div.anyfont_checkbox") : 0;
				if(this.del.className == "anyfont_checkbox"){
					this.del.style.backgroundPosition = "200px -23px";
				} else {
					this.del.style.backgroundPosition = "200px -75px";
				}
			}.bind(this));
			e.observe("mouseup", function(event){
				this.uel = event.element();
				!this.uel.hasClassName('anyfont_checkbox') ? this.uel = this.uel.up("div.anyfont_checkbox") : 0;
				this.selector = this.uel.down('input');
				if(!this.uel.hasClassName("anyfont_checkbox_on")) {
					this.selector.checked = true;
					this.uel.addClassName("anyfont_checkbox_on");
					this.uel.style.backgroundPosition = "200px -48px";
				} else  if(this.uel.hasClassName("anyfont_checkbox_on")){
					this.selector.checked = false;
					this.uel.removeClassName("anyfont_checkbox_on");
					this.uel.style.backgroundPosition = "200px 2px";
				}
				if(!this.selector.hasClassName('anyfont_chk_only')){
					if(!this.selector.hasClassName("settings")){
						this.hidden_el = this.uel.next('div.hidden_option');
						!this.selector.checked ? new Effect.BlindUp(this.hidden_el, {scaleFromCenter:true, duration:0.3}) : new Effect.BlindDown(this.hidden_el, {scaleFromCenter:true, duration:0.3});
					} else {
						this.dropdown = this.uel.next('select');
						if(this.dropdown.hasClassName('link-next')){
							while(this.dropdown !== false && this.dropdown.hasClassName('link-next')){
								!this.selector.checked ? this.dropdown.disable() : this.dropdown.enable();
								this.dropdown = this.dropdown.next('select') || false;
							}
						} else {
							!this.selector.checked ? this.dropdown.disable() : this.dropdown.enable();
						}
					}
				}
			}.bind(this));
		}.bind(this));
		document.observe("mouseup", function(){
			this.parentEl.select("div.anyfont_checkbox").each(function(e){
				if(e.down('input').getValue() == 'on'){
					e.style.backgroundPosition = "200px -48px";
				} else {
					e.style.backgroundPosition = "200px 2px";
				}
			})
		}.bind(this))
	}
});


document.observe("dom:loaded", function() {
	try{AnyFont.ajaxUrl = typeof ajaxurl === 'string' ? ajaxurl : af_set.url+'wp-admin/admin-ajax.php'}catch(e){}
	var loc = document.location.toString();
	var page = loc.split("=");
	Object.isElement($("contextual-help-link")) ? $("contextual-help-link").update("<img style='float:left;' src='"+af_set.url+"wp-content/plugins/anyfont/img/help.png' /><div style='float:left;'>Help & Support</div>") : 0;
	if(page[1] == "anyfont-styles"){
		AnyFont.stylesAccordian();
		new TabController('anyfont-tabs', 'anyfont-tab-container', 'anyfont-tab-');
		$('anyfont_page').select("input.colorinput").invoke('createColorPicker');
		$('anyfont_page').select(".anyfont_style_settings").invoke('createCheckbox');
		$('anyfont_page').select("input.shadow-distance").invoke('createSpinInput', {min:-50, max:50});
		$('anyfont_page').select("input.shadow-spread").invoke('createSpinInput', {min:0, max:10})
		$('anyfont_page').select("input.font-size").invoke('checkInputValue', "18pt", "pt", "px");
		$('anyfont_page').select("input.shadow-distance").invoke('checkInputValue', "1px", "px");
		$('anyfont_page').select("input.padding").invoke('createSpinInput', {min:-50, max:200})
		$('anyfont_page').select("input.padding").invoke('checkInputValue', "0px", "px");
		$('anyfont_page').select("input.max-width").invoke('createSpinInput', {min:0, max:5000});
		$('anyfont_page').select("input.max-width").invoke('checkInputValue', "0px", "px");
		$('anyfont_page').select("input.line-height").invoke('createSpinInput', {min:0, max:500});
		$('anyfont_page').select("input.line-height").invoke('checkInputValue', "0px", "px");
		new InputStyles({elements: 'input[type=radio]'});
		new InputStyles({elements: '.font-style-input'});
		document.observe("click", function(e){
			if(!e.element().hasClassName('custom_select')){
				$('anyfont_page').select("ul.active").each(function(el){
					el.hide();
					el.removeClassName('active')
				})
			}
		});
		$('anyfont_page').select("input.css-shadow-change").each(function(el){
			el.observe("spinner:change", AnyFont.setShadow)
		});
		$('anyfont_page').select("input.lhcss").each(function(el){
			el.observe("spinner:change", AnyFont.setLineHeight)
		});
	} else if(page[1] == 'anyfont-fonts'){
		$('file_upload_form').observe("submit", function() {
			$('file_upload_form').target = 'upload_target';
			$("upload_target").observe("load", AnyFont.fontUploaded)
		});
		window.onresize = function(e){
			$("anyfont-fontlist").select('.char-map').each(function(el){
				try{el.down('img').setStyle({"max-width":(el.up('div.charmap').getWidth()-40)+"px"})}catch(e){}
			})
		}
	} else if(page[1] == 'anyfont-settings'){
		new CheckboxStyle($('anyfont-settings'));
		$('anyfont-settings').select("input.cache-max-size").invoke('createSpinInput', {min:1, max:1000});
		$('anyfont-settings').select("input.cache-max-size").invoke('checkInputValue', "10MB", "MB");
		new TabController('anyfont-tabs', 'anyfont-tab-container', 'anyfont-tab-');
		$('anyfont-tab-cache').select('.hidden_option').each(function(el){
			!el.previous("div").hasClassName('anyfont_checkbox_on') ? el.hide() :0;
		});
		$('advanced_form').select('.hidden_option').each(function(el){
			!el.previous("div").hasClassName('anyfont_checkbox_on') ? el.hide() :0;
		});
	}
	$('anyfont_page').select("div.help-txt").invoke('helpText');
	$('anyfont_page').select("span.help-txt").invoke('helpText');
	$("anyfont_page").observe("click", function(ev){
		try{
			if(!ev.element().hasClassName('help-txt')){
				$('anyfont_page').select("div.help-text-popup").each(function(el){
					el.previous("span.help-txt").setAttribute("title", el.innerHTML);
					el.previous("span.help-txt").removeClassName('active');
					el.previous("span.help-txt").hasClassName('hover')  ? el.removeClassName('hover') : 0;
					el.remove();
				});
			}
		}catch(e){}
	})
});