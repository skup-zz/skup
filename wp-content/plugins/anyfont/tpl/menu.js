function AnyFontImageSwap(el, style, txt){

	el.src = '{URL}/images/'+style+'/'+txt+'.png';

}


// var AnyFontImageSwap = Class.create({
//
// 	initialize: function(el){
// 		this.el = $(el);
// 		this.text =  this.el.getAttribute('alt');
// 		this.hover_img = new Image();
// 		this.hover_img.src = '{URL}/images/{HOVER_STYLE}/'+this.text+'.png';
// 		this.active_img = new Image();
// 		this.active_img.src = '{URL}/images/{ACTIVE_STYLE}/'+this.text+'.png';
// 		if(this.el.up('li').hasClassName('current_page_item') && ('{ACTIVE_STYLE}' != 'false') && ('{MENU_STYLE}' != '{ACTIVE_STYLE}')){
// 			this.std_img =  this.active_img;
// 			this.el.replace(this.std_img);
// 			this.el = this.std_img;
// 			this.el.setAttribute('alt', this.text)
// 		}else{
// 			this.std_img = this.el
// 		}
// 		if(('{HOVER_STYLE}' != 'false') && ('{MENU_STYLE}' != '$HOVER_STYLE')){
// 			this.el.up('li').observe('mouseover', function(){
// 				this.el.replace(this.hover_img);
// 				this.el = this.hover_img;
// 				this.el.setAttribute('alt', this.text)
// 			}.bind(this))
// 		}
// 		if(('{ACTIVE_STYLE}' != 'false') && (('{MENU_STYLE}' != '{HOVER_STYLE}') || ('{MENU_STYLE}' != '{ACTIVE_STYLE}'))){
// 			this.el.up('li').observe('mouseout', function(){
// 				this.el.replace(this.std_img);
// 				this.el = this.std_img;
// 				this.el.setAttribute('alt', this.text)
// 			}.bind(this))
// 		}
// 	}
// });
//
// Element.addMethods({
// 	anyfont_image_swap: function(el){
// 		new	AnyFontImageSwap(el)
// 	}
// });

// document.observe('dom:loaded', function() {
// 	$$('body').first().select('img.anyfont_menu_image').invoke("anyfont_image_swap")
// });

// var AnyfontImageSwap = Class.create({
//
// 	initialize: function(el){
// 		this.cancelHoverEvent = this.cancelHover.bindAsEventListener(this);
// 		this.showHoverEvent = this.showHover.bindAsEventListener(this);
// 		this.el = $(el);
// 		if(!this.el.match("img")){
// 			delete this.el;
// 			return false
// 		}
// 		this.txt =  this.el.getAttribute('alt');
// 		this.hover_img = new Image();
// 		this.hover_img.src = '{URL}/images/{HOVER_STYLE}/'+this.txt+'.png';
// 		this.hover_img.setAttribute('alt', this.txt);
// 		this.hover_img.addClassName("anyfont_hover");
// 		this.active_img = new Image();
// 		this.active_img.src = '{URL}/images/{ACTIVE_STYLE}/'+this.txt+'.png';
// 		this.active_img.setAttribute('alt', this.txt);
// 		if(this.el.up('li').hasClassName('current_page_item') && ('{ACTIVE_STYLE}' != 'false') && ('{MENU_STYLE}' != '{ACTIVE_STYLE}')){
// 			this.std_img =  this.active_img;
// 			this.el.replace(this.std_img);
// 			this.el = this.std_img;
// 		}else{
// 			this.std_img = this.el
// 		}
// 		if(('{HOVER_STYLE}' != 'false') && ('{MENU_STYLE}' != '$HOVER_STYLE')){
// 			this.el.up('li').observe("cancel:hover", this.cancelHoverEvent);
// // 			this.el.observe('mouseover',this.showHoverEvent);
// 			this.el.up('li').observe('mouseover',this.showHoverEvent);
// 		}
// // 		if(('{ACTIVE_STYLE}' != 'false') && (('{MENU_STYLE}' != '{HOVER_STYLE}') || ('{MENU_STYLE}' != '{ACTIVE_STYLE}'))){
// // 			this.el.up('li').observe('mouseout', function(){
// // 				this.el.replace(this.std_img);
// // 				this.el = this.std_img;
// // 			}.bind(this))
// // 		}
// 	},
//
// 	showHover: function(){
// 		this.el.hasClassName("anyfont_hover"){
// 			try{this.el.up('ul').select('li.anyfont_hover').invoke("fire", "cancel:hover")}catch(e){}
// 			this.el.up('li').addClassName("anyfont_hover");
// 			this.el.replace(this.hover_img);
// 			this.el = this.hover_img;
// 		}
// 	},
//
// 	cancelHover: function(){
// 		this.el.up('li').removeClassName("anyfont_hover");
// 		this.el.replace(this.std_img);
// 		this.el = this.std_img;
// 	}
// });
//
// Element.addMethods({
// 	anyfont_image_swap: function(el){
// 		new	AnyfontImageSwap(el)
// 	}
// });