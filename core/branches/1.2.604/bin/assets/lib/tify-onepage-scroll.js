/**
 * --------------------------------------------------------------------------------
 *	TiFy One Page Scroll
 * --------------------------------------------------------------------------------
 *
 * @name 		tiFy Home Slideshow
 * @package    	Wordpress
 * @copyright 	Milkcreation 2013
 * @link 		http://www.milkcreation.fr
 * @author 		Jordy Manner
 * @version 	1.1
 *
 * Structure HTML suivante :
 * <div id="selecteur-du-slideshow"">
 * 		<div id="id-de-section#1" data-tify-onepage-scroll="section" data-offset="0"></div>
 * 		<div id="id-de-section#2" data-tify-onepage-scroll="section" data-offset="100"></div>
 * 		<div id="id-de-section#2" data-tify-onepage-scroll="section" data-offset="-150"></div>
 * </div> 
 * 
**/
!( function( $, doc, win, undefined ){
	"use strict";
	
	var name = 'tify-onepage-scroll';
	
	/**
	 * Public Methods 
	 */
	var methods = {
		init: function(opts){
			return this.each(function (i, el) {
				var instance = new tiFyOnePageScroll( this, opts );
			});
		}
	};
		
	$.fn.tiFyOnePageScroll = function( method ) {
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error( 'La methode ' + method + ' n\'existe pas pour jQuery.tiFyOnePageScroll' );
		}
 	};	
		
	function tiFyOnePageScroll( el, opts ) {
		this.$el = $(el);
		this.$target;
				
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {  
			easing 			: 'swing',		// Effet de transition
			speed 			: 300,			// Vitesse de défilement d'un slide à l'autre
			sum 			: true,			// 
			cycle 			: false,
			before			: function( target, self ){
	    		return false;
	    	},
			after 			: function( target, self ){
	    		return false;
	    	}   
		};
	    this.o = $.extend(true, this.defaults, opts );
		// Initialisation	
	    this.init();	   
	}
	
	// Prototype	
	tiFyOnePageScroll.prototype = 
	{
		// Initialisation du plugin	    
	 	init : function( ){
			var self = this;
			
			// Récupération de l'élément courant
			self._current();
			
			// Ecoute des actions sur la galerie
			self._listen();		
		},
		// Récupération de l'element courant
		_current : function(){
			var self = this;
			
			if( $( '[data-tify-onepage-scroll="section"].active', self.$el ).length ){
				self.$target = $( '[data-tify-onepage-scroll="section"].active', self.$el );
			} else {
				var scrollTop = $(window).scrollTop();
				self.$target = $( '[data-tify-onepage-scroll="section"]:first', self.$el );
				$( '[data-tify-onepage-scroll="section"]', self.$el ).each( function(){
					if( scrollTop > $(this).offset().top )
						self.$target = $(this);
					else
						return false;
				});
			}
			self._scrollTo();
		},		
		// Ecoute des actions
		_listen : function( ){
			var self = this;
			
			// Navigation
			self._nav();
			// Scoll de la souris
			self._mouseWheel();
			// Ecoute des events JQuery
			self._events( );
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
			$( document ).on( 'click.tify.onepage-scroll.target', "a[data-tify-onepage-scroll='target']", function(e){
				e.preventDefault();
				// Bypass : animation en cours
				if( self.$el.is(':animated') )
					return false;
				
				if( $(this).data('target') )
					target =  $(this).data('target');
				else	
					target = $(this).attr('href');
				
				// Bypass				
				if( $(target).hasClass('active') )
					return false;
				self.$target = $( target );
				if( ! self.$target.length )
					return false;
								
				self.o.before( self.$target, self );
				self._scrollTo();	
			});
			// Navigation précédent
			$( document ).on( 'click.tify.onepage-scroll.prev', "a[data-tify-onepage-scroll='prev']", function(e){
				e.preventDefault();
				self._prev();					
			});
			// Navigation suivant
			$( document ).on( 'click.tify.onepage-scroll.next', "a[data-tify-onepage-scroll='next']", function(e){
				e.preventDefault();
				self._next();					
			});			
		},
		_scrollTo : function(){
			var self = this;			
						
			var targetIndex = self.$target.index();
			var currentIndex = $( '[data-tify-onepage-scroll="section"].active', self.$el ).index();
			
			if( self.o.sum ){
				var ratio = Math.abs( currentIndex - targetIndex );			
				var speed = Math.abs( self.o.speed*ratio );	
			} else {
				var speed = self.o.speed;
			}
			
			var scrollTop = self.$target.offset().top;
			if( self.$target.data( 'offset' ) )
				scrollTop += self.$target.data( 'offset' );
	
			$( '[data-tify-onepage-scroll="section"]', self.$el ).removeClass( 'active' );
			
			$( "a[data-tify-onepage-scroll='target']" ).each( function(){ $(this).removeClass( 'active' ); });
			$( "a[data-tify-onepage-scroll='target'][data-target='#"+ self.$target.attr('id') +"'], a[data-tify-onepage-scroll='target'][href='#"+ self.$target.attr('id') +"']" ).each( function(){ $(this).addClass( 'active' ); });
			
			$( 'html, body' ).animate({ scrollTop:scrollTop }, speed, self.o.easing, function(e){
				self.$target.addClass('active');
				self.o.after( self.$target, self );
			});
		},
		_prev : function(){
			var self = this;
			
			// Bypass : animation en cours
			if( self.$el.is(':animated') )
				return false;
			
			if( ! $( '[data-tify-onepage-scroll="section"].active', self.$el ).length ){
				if( ! self.o.cycle ) return false;
				self.$target = $('[data-tify-onepage-scroll="section"]:last');					
			} else if( $('[data-tify-onepage-scroll="section"].active', self.$el ).is(':first-child') ){
				if( ! self.o.cycle ) return false;
				self.$target = $('[data-tify-onepage-scroll="section"]:last');					
			} else {	
				self.$target = $('[data-tify-onepage-scroll="section"].active').prev();
			}
							
			self.o.before( self.$target, self );
			self._scrollTo();
		},
		_next : function(){
			var self = this;
			
			// Bypass : animation en cours
			if( self.$el.is(':animated') )
				return false;
			if( ! $( '[data-tify-onepage-scroll="section"].active', self.$el ).length ){
				if( ! self.o.cycle ) return false;
				self.$target = $( '[data-tify-onepage-scroll="section"]:first');
			} else if( $( '[data-tify-onepage-scroll="section"].active', self.$el ).is(':last-child') ){
				if( ! self.o.cycle ) return false;
				self.$target = $( '[data-tify-onepage-scroll="section"]:first' );
			} else {	
				self.$target = $( '[data-tify-onepage-scroll="section"].active' ).next();
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
		_events	: function(){
			var self = this;
			
			self.resizeTimer;
			$(window).resize( function(){
				clearTimeout(self.resizeTimer);
				self.resizeTimer = setTimeout( function() {
					self._scrollTo();				            
				}, 100);				
			});
		}
	};	
})( jQuery, document, window, undefined );