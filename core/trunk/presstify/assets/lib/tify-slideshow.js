/**
 * --------------------------------------------------------------------------------
 *	TiFy Slideshow
 * --------------------------------------------------------------------------------
 *
 * @name 		Slideshow
 * @package    	Wordpress
 * @copyright 	Milkcreation
 * @link 		http://www.milkcreation.fr
 * @version 	1.161005
 *
 * Ressources
 * @see http://tympanus.net/Tutorials/CSS3SlidingImagePanels/index3.html
**/
!( function( $, doc, win, undefined ){
	"use strict";
	var name = 'tify-slideshow';

	var methods = 
	{
		init: function(opts){
			return this.each(function (i, el) {
				var instance = new tiFySlideshow( this, opts );
			});
		}
	};
		
	$.fn.tiFySlideshow = function( method ) 
	{
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('La methode ' + method + ' n\'existe pas pour jQuery.tiFySlideshow');
		}
 	};	
		
	function tiFySlideshow( el, opts) 
	{
		this.$el = $(el);
		this.$viewer = $( '.viewer', this.$el );
		this.$roller = $( '.roller', this.$el );
		this.dir = "next";
				
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {
			// Animation
			/// Durée entre chaque transition automatique
			interval: 		5000,
			/// Arrêt de l'automate au survol
			pause: 			'hover',
			/// Effet de transition
			transition: 	'slideLeft',
			/// Vitesse de transition
			speed: 			500,
			/// Equation pour l'effet de transition
			easing: 		'easeInOutExpo',			
			/// Adaptabilité au redimentionnement de l'ecran
			resize: 		true,
			/// Nombre d'éléments par page
			bypage: 		1,
			
			// Callback
			before: 		function( dir, target, self ){
	    		return false;
	    	},
			after: 			function( dir, target, self ){
	    		return false;
	    	},
	    	beforeInit: 	function(){
	    		return false;
	    	},
	    	afterInit: 		function(){
	    		return false;
	    	},
			onResize : 		function( current, self ){
	    		return false;
	    	}			
		};
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
		
		// Définition des options
	    this.o = $.extend(true, this.defaults, opts );

		// Initialisation	
	    this.init();	   
	}
	
	// Prototype	
	tiFySlideshow.prototype = 
	{
		// Initialisation du plugin	    
	 	init : function( ){
			var self = this;

			// Tatouage de l'index des éléments
			$( '> li', self.$roller ).each( function(u,v){
				$(this).attr('data-index', u );
			});
			// Initialisation de l'élément courant
			self._style();			
			self.$current = $( '> li:eq(0)', self.$roller );
			self.currentIndex = 0;
			self._setCurrent();
			self.$viewer.scrollLeft(0);
			self.gap = 1;
			
			self.o.beforeInit();
			
			// Court-circuitage du diaporama si le nombre de slide est insuffisant
			if( $( '> li', self.$roller ).length <= self.o.bypage ){
				$( '[data-arrownav], [data-tabnav]', self.$el ).hide();
				//return;
			}
			
			// Ecoute des actions sur la galerie
			self._listen();			
			// Défilement automatique
			if( self.o.interval > 0 )
				self._auto( );
				
			self.o.afterInit();
		},
		
		// Adaptation du style
		_style : function()
		{
			var self = this;
			switch( self.o.transition ){
				case 'slideLeft' :
					$( '> li', self.$roller ).each( function(){
						$(this).css({ width:( $( '.viewer', self.$el ).width()/self.o.bypage)+'px' });
					});					
					break;
			}		
		},
		
		// Démarrage de l'autoscroll
		_auto : function()
		{
			var self = this;
			
			self.interval = setInterval( function(){ 
				self.autoscroll = true;				
				
				// Définition de la direction
				self.dir = ( $(this).hasClass('prev') )? 'prev' : 'next';			
				// Définition de la vignette cible
				self.$current =  $( '> li.active', self.$roller );			
				if( self.dir === 'prev'){
					if( self.$current.is(':first-child') ){
						self.$target = $( '> li:last', self.$roller );
					} else {	
						self.$target =  self.$current.prev();
					}
				} else {
					if( self.$current.is(':last-child') ){
						self.$target = $( '> li:first', self.$roller );
					} else {	
						self.$target =  self.$current.next();
					}
				}
				
				self.targetIndex = self.$target.data('index');			
				self._slide();

			}, self.o.interval );
		},
		
		// Ecoute des actions
		_listen : function()
		{
			var self = this;
			
			// Survol du diaporama
			if( self.o.interval > 0 ){
				if( self.o.pause === 'hover' ){
					self.$el.hover( function(e){
						self.autoscroll = false;
						clearTimeout( self.setprogress );
						clearTimeout( self.interval );					
					}, function(){
						self._auto();
					});
				} else {
					self._auto();
				}
			}
			
			// 
			self._drag();
			
			// Navigation suivant/précédent
			self._nav(); 			
			
			// Responsivité 		
			if( self.o.resize )
				self._resize();			
		},
		
		// 
		_drag : function()
		{
		    var self = this;
		    
		    $( '> li', self.$roller ).draggable({		        
		        axis: 'x',
		        stop: function( event, ui ) {
		            
		            
                  if( ui.position.left < 0 ){
                      self.dir = 'next';
                  } else {
                      self.dir = 'prev';
                  }
                  self._slide();
		        }
            });
		},
		
		// Navigation
		_nav : function(){
			var self = this;

			// Navigation suivant/précédent
			$( '[data-arrownav]', self.$el ).click( function(e){
				e.preventDefault();
				
				// Bypass
				if( self.$viewer.is(':animated') )
					return false;
				
				// Définition de la direction
				self.dir = ( $(this).data( 'arrownav' ) === 'prev' )? 'prev' : 'next';	
				
				// Animation
				self._slide();
			});	
			
			// Navigation tabulation
			$( '[data-tabnav]', self.$el ).click( function(e){
				e.preventDefault();
				// Bypass
				if( self.$viewer.is(':animated') )
					return false;
				
				var index = $(this).data( 'tabnav' );
				
				if( $( '[data-tabnav]', self.$el ).closest( 'li.active' ).index() > index ){
					self.dir = 'prev';
				} else {
					self.dir = 'next';
				}
				
				// Définition de la cible
				self.$target = $( "> li[data-index='"+$(this).closest('li').index()+"']", self.$roller );	
				
				// Animation
				self._slide();
			});							
		},
		
		_slide : function(){
			var self = this;
			
			self.$current =  $( '> li.active', self.$roller );
			self.$current.removeClass('animated');
			
			// Définition de la cible
			if( ! self.$target ){				
				if( self.dir === 'prev'){
					if( self.$current.is(':first-child') ){
						self.$target = $( '> li:last', self.$roller );
					} else {	
						self.$target =  self.$current.prev();						
					}
				} else {
					if( self.$current.is(':last-child') ){
						self.$target = $( '> li:first', self.$roller );
					} else {	
						self.$target =  self.$current.next();
					}
				}			
			}
			self.$target.addClass('animated');
			
			var rollerPos = self.$roller.position().left;
			var targetPos = self.$target.position().left;			
			self.targetIndex = self.$target.data('index');
			self.gap = self.targetIndex - self.currentIndex;
	
			switch( self.o.transition ){	
				case 'slideLeft' :
					if( self.dir === 'prev' ){						
						$('> li', self.$roller ).slice( 0, self.gap ).each( function(){
							$(this).appendTo( self.$roller );
						});
						var ratio = ( self.gap<0)? -self.gap : 1;					
						
						self.o.before( self.dir, self.$target, self );
					
						self.$viewer.scrollLeft( self.$target.outerWidth()*ratio );
						self.$viewer.stop().animate({ scrollLeft: 0 }, self.o.speed, self.o.easing, function(){
						    self.$current.css( 'left', 0 );
							self.o.after( self.dir, self.$target, self );
							self.$current = self.$target;
							self._setCurrent(); 							
							self._reset();	
						});					
					} else {					    
 						self.o.before( self.dir, self.$target, self );	
 						
						self.$viewer.stop().animate({ scrollLeft: targetPos - rollerPos }, self.o.speed, self.o.easing, function(){
						    self.$current.css( 'left', 0 );
							self.o.after( self.dir, self.$target, self );	
							self.$current = self.$target;
							self._setCurrent();
							$('> li', self.$roller ).slice( 0, self.gap ).each( function(){
								$(this).appendTo( self.$roller );
							});											
							self._reset();							
						});
					}					
				break;
				case 'fadeIn' :			
					if( self.dir === 'prev' ){													
						for( var i = 0; i<-gap; i++ )
							$('> li:last', self.$roller ).prependTo( self.$roller );
						self.o.before( self.dir, self.$target, self );
						self.$target.hide();
						self.$current.fadeOut( function(){
							self.o.after( self.dir, self.$target, self );
							self.$current = self.$target;
							self._setCurrent(); 							
							self._reset();	
						});						
					} else {
 						self.o.before( self.dir, self.$target, self );
	 					self.$target.hide();				
						self.$current.fadeOut( function(){
							self.$target.fadeIn();
							self.o.after( self.dir, self.$target, self );	
							self.$current = self.$target;
							self._setCurrent();
							$('> li', self.$roller ).slice( 0, gap ).each( function(){
								$(this).appendTo( self.$roller );
							});											
							self._reset();							
						});
					}
				break;
			}	
		},
		
		_setCurrent : function(){
			var self = this;
			
			self.$current
				.addClass('active')
				.siblings().removeClass('active');
			
			self.currentIndex = self.$current.data('index');
			
			$( '[data-tabnav="'+self.currentIndex+'"]', self.$el ).closest( 'li' )
				.addClass( 'active' )
				.siblings().removeClass( 'active' );		
		},
		
		_reset : function(){
			var self = this;
			
			self.$target = undefined;
			self.$viewer.scrollLeft(0);
		},
		
		_resize : function(){
			var self = this;
			
			$(window).resize( function(e) {
				// Déclenchement de la fonction de rappel
				self.o.onResize( self.$current, self );
				
				$( '> li', self.$roller )
					.css('width', (self.$viewer.width()/self.o.bypage)+'px');
				
				var rollerPos = self.$roller.position().left;				
				var targetPos = self.$current.position().left;
				
				self.$viewer.scrollLeft( targetPos - rollerPos );		
			});
		}	
	};	
})( jQuery, document, window, undefined );

jQuery( document ).ready( function($){
	$( '[data-tify="slideshow"]' ).tiFySlideshow();
});