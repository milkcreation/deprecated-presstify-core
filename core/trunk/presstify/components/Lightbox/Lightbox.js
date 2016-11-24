var tiFyImageLightbox;
!( function( $, doc, win, undefined ){	
	var
	// ACTIVITY INDICATOR
	activityIndicatorOn = function()
	{
		$( '<div id="tiFyLightbox-Loading"><div></div></div>' ).appendTo( 'body' );
	},
	activityIndicatorOff = function()
	{
		$( '#tiFyLightbox-Loading' ).remove();
	},

	// OVERLAY
	overlayOn = function()
	{
		$( '<div id="tiFyLightbox-Overlay"></div>' ).appendTo( 'body' );
	},
	overlayOff = function()
	{
		$( '#tiFyLightbox-Overlay' ).remove();
	},

	// CLOSE BUTTON
	closeButtonOn = function( instance )
	{
		$( '<button type="button" id="tiFyLightbox-Close" title="Close"></button>' ).appendTo( 'body' ).on( 'click touchend', function(){ $( this ).remove(); instance.quitImageLightbox(); return false; });
	},
	closeButtonOff = function()
	{
		$( '#tiFyLightbox-Close' ).remove();
	},

	// CAPTION
	captionOn = function( instance, selector )
	{
		var current = selector.filter( '[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"]' );
		var caption = '';
		if( caption = current.data( 'caption' ) ){
		} else if( caption = $( 'img', current ).attr('alt') ){
		} else if( caption = $( current ).attr( 'title' ) ){
		}

		if( caption )
			$( '<div id="tiFyLightbox-Caption">' + caption + '</div>' ).appendTo( 'body' );
	},
	captionOff = function()
	{
		$( '#tiFyLightbox-Caption' ).remove();
	},

	// NAVIGATION
	navigationOn = function( instance, selector )
	{		
		if( instance.length < 2 )
			return;
		
		var nav = $( '<div id="tiFyLightbox-Nav"></div>' );
		for( var i = 0; i < instance.length; i++ )
			nav.append( '<button type="button"></button>' );

		nav.appendTo( 'body' );
		nav.on( 'click touchend', function(){ return false; });

		var navItems = nav.find( 'button' );
		navItems.on( 'click touchend', function()
		{
			var $this = $( this );
			if( selector.eq( $this.index() ).attr( 'href' ) != $( '#tiFyLightbox' ).attr( 'src' ) ){
				instance.switchImageLightbox( $this.index() );
			}

			navItems.removeClass( 'active' );
			navItems.eq( $this.index() ).addClass( 'active' );

			return false;
		})
		.on( 'touchend', function(){ return false; });
	},
	navigationUpdate = function( instance, selector )
	{
		var items = $( '#tiFyLightbox-Nav button' );
		items.removeClass( 'active' );
		
		var current = selector.filter( '[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"]' );
		
		items.eq( selector.index( current ) ).addClass( 'active' );
	},
	navigationOff = function()
	{
		$( '#tiFyLightbox-Nav' ).remove();
	},

	// ARROWS
	arrowsOn = function( instance, selector )
	{
		if( instance.length < 2 )
			return;
		
		var $arrows = $( '<button type="button" class="tiFyLightbox-Arrow tiFyLightbox-Arrow--left"></button><button type="button" class="tiFyLightbox-Arrow tiFyLightbox-Arrow--right"></button>' );

		$arrows.appendTo( 'body' );

		$arrows.on( 'click touchend', function( e )
		{
			e.preventDefault();

			var $this	= $( this ),
				$target	= selector.filter( '[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"]' ),
				index	= selector.index( $target );

			if( $this.hasClass( 'tiFyLightbox-Arrow--left' ) ) {
				index = index - 1;
				if( ! selector.eq( index ).length )
					index = selector.length;
			} else {
				index = index + 1;
				if( ! selector.eq( index ).length )
					index = 0;
			}
			
			instance.switchImageLightbox( index );
			return false;
		});
	},
	arrowsOff = function()
	{
		$( '.tiFyLightbox-Arrow' ).remove();
	};
	
	tiFyImageLightbox = function( $selectors, o )
	{
		$selectors = $selectors.filter( function(){ return $(this).attr('href').match(/\.(jpe?g|png|gif)/i); });
				
		var opts = {
			selector:		'id="tiFyLightbox"',
			
			enableKeyboard:	o.keyboard,
			
			quitOnDocClick:	o.overlay_close,
			
			animationSpeed: parseInt( o.animation_speed ),
		
			onStart:		function() { 
				if( o.overlay )
					overlayOn(); 
				if( o.close_button )
					closeButtonOn( instance );
				if( o.navigation )  
					arrowsOn( instance, $selectors ); 
				if( o.tabs )
					navigationOn( instance, $selectors );	
			},
			
			onEnd:			function() { 
				if( o.overlay )
					overlayOff(); 
				if( o.caption )
					captionOff(); 
				if( o.close_button )					
					closeButtonOff();
				if( o.navigation )  
					arrowsOff();
				if( o.spinner ) 
					activityIndicatorOff(); 
				if( o.tabs )
					navigationOff();
			},
			
			onLoadStart: 		function() { 
				if( o.caption )
					captionOff();
				if( o.spinner ) 
					activityIndicatorOn(); 
			},
			
			onLoadEnd:	 	function() { 
				if( o.caption )					
					captionOn( instance, $selectors );
				if( o.spinner ) 
					activityIndicatorOff(); 
				if( o.navigation )
					$( '.tiFyLightbox-Arrow' ).css( 'display', 'block' ); 
				if( o.tabs )
					navigationUpdate( instance, $selectors );
			}
		};
		var instance = $selectors.imageLightbox( opts );
	};
})( jQuery, document, window, undefined );

jQuery(document).ready( function($){
	// Galeries Wordpress
	$( '[id^="gallery-"]' ).each( function(){
		tiFyImageLightbox( $( 'a', $(this) ), tiFyLightbox );
	});
	// Médias des articles 
	tiFyImageLightbox( $( 'a' ).has( 'img[class*="wp-image-"]' ), tiFyLightbox );

	// Images personnalisées
	tiFyImageLightbox( $( '[data-role="tiFyLightbox-image"]' ), tiFyLightbox );

});