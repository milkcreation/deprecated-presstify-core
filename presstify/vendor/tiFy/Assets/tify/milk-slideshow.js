/**
 * --------------------------------------------------------------------------------
 *	Milkcreation Cycle Slideshow
 * --------------------------------------------------------------------------------
 *
 * @name 		Milk Home Slideshow
 * @package    	Wordpress
 * @copyright 	Milkcreation 2013
 * @link 		http://www.milkcreation.fr
 * @author 		Jordy Manner
 * @version 	2.150505
 *
 * Structure HTML suivante :
 * 
 * <div id="selecteur-du-slideshow" ou class="selecteur-du-slideshow">
 * 		<div class="viewer">
 * 			<ul class="roller">
 * 				<li></li>
 * 					...
 * 			</ul>
 * 		</div>
 * </div> 
 * 
 * 
 * Ressources
 * @see http://tympanus.net/Tutorials/CSS3SlidingImagePanels/index3.html
 * 
**/
!( function( $, doc, win, undefined ){
	"use strict";
	var name = 'milk-slideshow';

	var methods = {
		init: function(opts){
			return this.each(function (i, el) {
				var instance = new MilkSlideshow( this, opts );
			});
		}
	};
		
	$.fn.milkSlideshow = function( method ) {
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('La methode ' + method + ' n\'existe pas pour jQuery.milkSlideshow');
		}
 	};	
		
	function MilkSlideshow( el, opts) {
		this.$el = $(el);
		this.$viewer = $( '.viewer', this.$el );
		this.$roller = $( '.roller', this.$el );
		this.dir = "next";
				
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {  
			autoscroll: true,
			delay : 3000,
			easing : 'easeInOutExpo',  
			transition : 'slideLeft',
			behavior : 'cycle', //cycle ||rewind
			speed : 500,
			bypage : 1,
			bystep : 1,
			responsive : true,
			stop_hover : true,
			before : function( dir, target, self ){
	    		return false;
	    	},
			after : function( dir, target, self ){
	    		return false;
	    	},
	    	beforeInit: function(){
	    		return false;
	    	},
	    	afterInit: function(){
	    		return false;
	    	},
			resize : function( current, self ){
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

	    this.o = $.extend(true, this.defaults, opts );
	    
		// Initialisation	
	    this.init();	   
	}
	
	// Prototype	
	MilkSlideshow.prototype = 
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
				$( '.nav', self.$el ).hide();
				return;
			}
			
			// Ecoute des actions sur la galerie
			self._listen();			
			// Défilement automatique	
			if( self.o.autoscroll )
				self._auto( );
				
			self.o.afterInit();
		},
		// Adaptation du style
		_style : function(){
			var self = this;
			switch( self.o.transition ){
				case 'slideLeft' :
					self.$el.css('position', 'relative');
					self.$roller.css({ width:'99999em', margin:0, padding:0, 'list-style-type': 'none' });
					$( '> li', self.$roller ).each( function(){
						$(this).css({ float:'left'});
						$(this).css({ width:(self.$el.width()/self.o.bypage)+'px' });
					});					
					break;
			}		
		},
		// Démarrage de l'autoscroll
		_auto : function(){
			var self = this;
			self.timeout = setInterval( function(){ 
				self.autoscroll = true;
				// Définition de la direction
				self.dir = ( $(this).hasClass('prev') )? 'prev' : 'next';			
				// Définition de la vignette cible
				self.$current =  $( '> li.current', self.$roller );			
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
				//
				self.targetIndex = self.$target.data('index');			
												
				self._slide();
			}, self.o.delay );
		},
		// Ecoute des actions
		_listen : function(){
			var self = this;
			// Survol du diaporama
			if( self.o.autoscroll ){
				if( self.o.stop_hover ){
					self.$el.hover( function(e){
						self.autoscroll = false;
						clearTimeout( self.timeout );
					}, function(){
						self._auto();
					});
				} else {
					self._auto();
				}
			}
			// Navigation suivant/précédent
			self._nav(); 			
			// Navigation tablette
			if( jQuery().swipe )
				self._swipe();
			// Responsivité 		
			if( self.o.responsive )
				self._resize();			
		},
		_preloadImg : function(){
			var self = this;
			var src = self.$target.data('src');
			if( src )
				$( '<img />' ).load( src, function(){
					$('.item-image', $target ).attr('style', 'background-image:url('+src+');' );
					self._transition( $target, dir );
				});	
		},
		// Navigation
		_nav : function(){
			var self = this;
			// Navigation suivant/précédent
			$( '.nav', self.$el ).click( function(e){
				e.preventDefault();
				// Bypass
				if( self.$viewer.is(':animated') )
					return false;				
				// Définition de la direction
				self.dir = ( $(this).hasClass('prev') )? 'prev' : 'next';									
				self._slide();
			});							
			// Navigation tabulation
			$( '.tabs > li > a', self.$el ).click( function(e){
				e.preventDefault();
				// Bypass
				if( self.$viewer.is(':animated') )
					return false;
				var index = $(this).closest('li').index();
				if( $( '.tabs > li.current').index() > index )
					self.dir = 'prev';
				else
					self.dir = 'next';
					
				self.$target = $( "> li[data-index='"+$(this).closest('li').index()+"']", self.$roller );			
				self._slide();
			});			
			// Navigation personnalisée
			$( "a[data-milkslideshow-slide]" ).click( function(e){
				e.preventDefault();
				// Bypass
				if( self.$viewer.is(':animated') )
					return false;
				self.$target = $( "> li[data-index='"+parseInt( $(this).data('milkslideshow-slide') - 1 )+"']", self.$roller );	
				self._slide();
			});					
		},
		_slide : function(){
			var self = this;	
			self.$current =  $( '> li.current', self.$roller );
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
			
			var rollerPos = self.$roller.position().left;				
			var targetPos = self.$target.position().left;			
			self.targetIndex = self.$target.data('index');
			self.gap = self.targetIndex - self.currentIndex;
			/*
			if( self.o.behavior == 'rewind' ){		
				var gap = self.targetIndex - self.currentIndex;	
				if( ! self.autoscroll && ( gap < 0) )
					self.dir = 'prev';
				else
					self.dir = 'next';
			} else if( self.o.behavior == 'cycle' ){
				if( self.dir == 'prev' )
					var gap = -1;
				else
					var gap = 1;					
			}*/		
	
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
							self.o.after( self.dir, self.$target, self );
							self.$current = self.$target;
							self._setCurrent(); 							
							self._reset();	
						});					
					} else {
 						self.o.before( self.dir, self.$target, self );		
						self.$viewer.stop().animate({ scrollLeft: targetPos - rollerPos }, self.o.speed, self.o.easing, function(){
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
		_swipe : function(){
			var self = this;
			self.$viewer.swipe({
				triggerOnTouchEnd : true,	
				swipeStatus : self._onSwipe,
				allowPageScroll:"horizontal",
				threshold:75			
			});
		},
		_onSwipe : function( event, phase, direction, distance ){			
			if ( phase == "end" ){
				if( direction == "left" ){
					
				} else if( direction == "right"){
					
				}
			}
		},
		_setCurrent : function(){
			var self = this;
			self.$current.addClass('current').siblings().removeClass('current');
			self.currentIndex = self.$current.data('index');
			$( '.tabs > li:eq('+self.currentIndex+')', self.$el ).addClass('current').siblings().removeClass('current');
			$( 'li[data-milkslideshow="tabindex"]:eq('+self.currentIndex+')').addClass('current').siblings().removeClass('current');		
		},
		_reset : function(){
			var self = this;
			self.$target = undefined;
			self.$viewer.scrollLeft(0);
		},
		_resize : function(){
			var self = this;
			$(window).resize( function() {
				self.o.resize( self.$current, self );
				$( '> li', self.$roller ).css('width', (self.$viewer.width()/self.o.bypage)+'px');
				var rollerPos = self.$roller.position().left;				
				var targetPos = self.$current.position().left;
				self.$viewer.scrollLeft( targetPos - rollerPos );		
			}).trigger('resize');
		}	
	};	
})( jQuery, document, window, undefined );