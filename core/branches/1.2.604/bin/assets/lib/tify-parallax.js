/**
 * --------------------------------------------------------------------------------
 *	TiFy Parallax
 * --------------------------------------------------------------------------------
 *
 * @name 		Parallax
 * @copyright 	Milkcreation 2017
 * @link 		http://www.milkcreation.fr
 * @version 	1.170120
 *
 * USAGE :
 * <div data-tify=\"parallax\" data-offset=\"[offset]\"></div>";
 * [offset] : (int) décalage en px (ex: 250 -> descendre de 250px | -250 -> monte de 250px )
 *
**/
!( function( $, doc, win, undefined ){
	"use strict";
	var name 		= 'tify-parallax';
	var instance 	= [];
	
	var methods = 
	{
		init : 				function(opts){
			if( ! instance.length ){
				return this.each( function (i, el) {
					instance[i] = new tiFyParallax( this, opts );
				});				
			}
		},
		
		destroy :			function(){
			$.each( instance, function(u,v){
				v.destroy();
			});
		},
		
		listen :			function(){
			$.each( instance, function(u,v){
				v.listen();
			});
		}
	};
		
	$.fn.tiFyParallax = function( method ) 
	{
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('La methode ' + method + ' n\'existe pas pour jQuery.tiFyParallax');
		}
 	};	
		
	function tiFyParallax( el, opts) 
	{
		this.$el = $(el);
		
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {};
		
		// Récupération des options passée par les attributs data de l'élément
		var defaults = {};
		$.each( this.defaults, function(u,v){
			var data = $(el).data(u);			
			if( data != undefined )
				defaults[u] = data;
			else
				defaults[u] = v;			
		});		
		this.defaults = defaults;

	    this.o = $.extend(true, this.defaults, opts );
	    	
	    this.listen();
	}
	
	// Prototype	
	tiFyParallax.prototype = 
	{		
		init :				function()
		{
			var self = this;
			
			self.updateEl( self.$el, $(win) );
		},
		
		listen :			function()
		{
			var self = this;
			
			$(win).on( 'scroll.tiFyParallax resize.tiFyParallax', function() {
				self.updateEl( self.$el, $(this) );			
			});
		},
		
		getWindowAttrs :	function( $window )
		{
			if( $window === undefined ){
				$window = $(win);
			}

			var wTop    = $window.scrollTop(),
				wLeft   = $window.scrollLeft(),
				wWidth  = $window.innerWidth(),
				wHeight = $window.innerHeight(),
				wBottom = wHeight+wTop,
				wRight  = wWidth+wLeft;

			return { top: wTop, right : wRight, bottom : wBottom, left: wLeft, width : wWidth, height : wHeight };
		},
		
		updateEl : 			function( $el, $window )
		{
			var self = this;
			
			if( $window === undefined ){
                    $window = $(win);
            }

			var wCoord  = self.getWindowAttrs( $window );

			var offset = $el.data('offset');
			
			// Définition des  
			var	eTop 	= self.$el.offset().top,
				eLeft 	= self.$el.offset().left,
				eHeight = $el.outerHeight(true), 
				eWidth 	= $el.outerWidth(true),
				eBottom = eTop+eHeight,
				eRight 	= eLeft+eWidth; 
		
			var eCoord = { top: eTop, right : eRight, bottom : eBottom, left : eLeft, width: eWidth, height: eHeight };
			
			var tPos = wCoord.bottom-eCoord.top,
				bPos = wCoord.top-eCoord.bottom;
			
			if( tPos > 0 && bPos < 0 ){
				var current = tPos;
				var max = wCoord.height+eCoord.height;
				var percent = Math.round( (current/max)*100 ),
					ratio	= percent/100;
			
				var value = offset*ratio;
					
				$el.css({ 
					transform: 			'translateY('+ value +'px)',
					MozTransform: 		'translateY('+ value +'px)',
					WebkitTransform: 	'translateY('+ value +'px)',
					msTransform: 		'translateY('+ value +'px)'
				});			
			}
		},
		
		resetEl :	function( $el )
		{
			$el.css({ 
				transform: 			'translateY(0px)',
				MozTransform: 		'translateY(0px)',
				WebkitTransform: 	'translateY(0px)',
				msTransform: 		'translateY(0px)'
			});			
		},
		
		destroy:	function()
		{
			var self = this;
			
			$(win).off( 'scroll.tiFyParallax resize.tiFyParallax' );
			
			self.resetEl( self.$el );			
		}
	};	
})( jQuery, document, window, undefined );

jQuery( document ).ready( function($){
	$( '[data-tify="parallax"]' ).tiFyParallax();
});