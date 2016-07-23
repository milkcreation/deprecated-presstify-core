/**
 * --------------------------------------------------------------------------------
 *	Milkcreation One Page Scroll
 * --------------------------------------------------------------------------------
 *
 * @name 		Milk Home Slideshow
 * @package    	Wordpress
 * @copyright 	Milkcreation 2013
 * @link 		http://www.milkcreation.fr
 * @author 		Jordy Manner
 * @version 	1.1
 *
 * Structure HTML suivante :
 * 
 * <div id="selecteur-du-slideshow" ou class="selecteur-du-slideshow">
 * 		<section id="id-de-section#1">
 * 		</section>
 * 		<section id="id-de-section#2">
 * 		</section>
 * </div> 
 * 
**/
!( function( $, doc, win, undefined ){
	"use strict";
	
	var name = 'milk-onepage-scroll';
	
	/**
	 * Public Methods 
	 */
	var methods = {
		init: function(opts){
			return this.each(function (i, el) {
				var instance = new MilkOnePageScroll( this, opts );
			});
		}
	};
		
	$.fn.milkOnePageScroll = function( method ) {
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('La methode ' + method + ' n\'existe pas pour jQuery.milkOnePageScroll');
		}
 	};	
		
	function MilkOnePageScroll( el, opts ) {
		this.$el = $(el);
		this.$target;
				
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {  
			easing : 'swing',  
			speed : 300,
			'max-width' : 0,
			cycle : false,
			scroller : '', // Element du DOM pour lesquels activer le défilement avec la souris  
			before : function( target, self ){
	    		return false;
	    	},
			after : function( target, self ){
	    		return false;
	    	}   
		};
	    this.o = $.extend(true, this.defaults, opts );
		// Initialisation	
	    this.init();	   
	}
	
	// Prototype	
	MilkOnePageScroll.prototype = 
	{
		// Initialisation du plugin	    
	 	init : function( ){
			var self = this;
			
			if( ! $('section.active', self.$el ).size() )
				self.$target = $( 'section:first', self.$el ).addClass('active');
			// Ecoute des actions sur la galerie
			self._listen( );
		},
		// Ecoute des actions
		_listen : function( ){
			var self = this;
			
			// Navigation
			self._nav();
			// Navigation
			self._mouseWheel();  
			// Responsivité 
			self._resize();	
		},
		// Navigation
		_nav : function(){
			var self = this;
			
			var url_target, page_url = document.URL;
	
			if( ( url_target = page_url.split("#") ) && ( url_target[1] != undefined ) ){
				var target = '#'+ url_target[1];
				self.$target = $( target );
				
				self._scrollTo();
			}
			// Navigation ciblée
			$( document ).on( 'click.onepagescroll.target', "a[data-pagescroll='target']", function(e){
				e.preventDefault();
				// Bypass : animation en cours
				if( self.$el.is(':animated') )
					return false;
				
				var target = $(this).attr('href');
				
				// Bypass				
				if( $(target).hasClass('active') )
					return false;
				self.$target = $( target );
				if( ! self.$target.size() )
					return false;
									
				self.o.before( self.$target, self );
				self._scrollTo();	
			});
			// Navigation précédent
			$( document ).on( 'click.onepagescroll.prev', "a[data-pagescroll='prev']", function(e){
				e.preventDefault();
				self._prev();					
			});
			// Navigation suivant
			$( document ).on( 'click.onepagescroll.next', "a[data-pagescroll='next']", function(e){
				e.preventDefault();
				self._next();					
			});			
		},
		_scrollTo : function(){
			var self = this;			
						
			var targetIndex = self.$target.index();
			var currentIndex = $( 'section.active', self.$el ).index();

			var ratio = Math.abs( currentIndex - targetIndex );
			
			var speed = Math.abs( self.o.speed*ratio );	

			var scroll = self.$target.height()*targetIndex;
			
			$( 'section', self.$el ).removeClass( 'active' );			
			self.$el.stop().animate({ marginTop : -scroll+'px' }, speed, self.o.easing, function(){
				self.$target.addClass('active');
				self.o.after( self.$target, self );				
			});
		},
		_prev : function(){
			var self = this;
			
			// Bypass : animation en cours
			if( self.$el.is(':animated') )
				return false;
			
			if( ! $('section.active', self.$el ).size() ){
				if( ! self.o.cycle ) return false;
				self.$target = $('section:last');					
			} else if( $('section.active', self.$el ).is(':first-child') ){
				if( ! self.o.cycle ) return false;
				self.$target = $('section:last');					
			} else {	
				self.$target = $('section.active').prev();
			}
							
			self.o.before( self.$target, self );
			self._scrollTo();
		},
		_next : function(){
			var self = this;
			
			// Bypass : animation en cours
			if( self.$el.is(':animated') )
				return false;
			if( ! $('section.active', self.$el ).size() ){
				if( ! self.o.cycle ) return false;
				self.$target = $('section:first');
			} else if( $('section.active', self.$el ).is(':last-child') ){
				if( ! self.o.cycle ) return false;
				self.$target = $('section:first');
			} else {	
				self.$target = $('section.active').next();
			}
							
			self.o.before( self.$target, self );
			self._scrollTo();
		},
		_mouseWheel : function(){
			var self = this;
			$( document ).on( 'mousewheel.onepagescroll', self.el, function( event, delta, deltaX, deltaY ){
				if( delta > 0 )
					self._prev();
				else
					self._next();			
			});
		},
		_resize : function(){
			var self = this;
			$(window).resize( function(e){
				if( $( 'section.active', self.$el ).size() )
					self.$el.css({ 'margin-top' : - $( 'section.active', self.$el ).position().top+'px' });
			});
		}				
	};	
})( jQuery, document, window, undefined );