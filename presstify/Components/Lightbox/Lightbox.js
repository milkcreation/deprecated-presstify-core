jQuery(document).ready( function($){
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
	captionOn = function()
	{
		var description = $( 'a[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"] img' ).attr( 'alt' );
		if( description.length > 0 )
			$( '<div id="tiFyLightbox-Caption">' + description + '</div>' ).appendTo( 'body' );
	},
	captionOff = function()
	{
		$( '#tiFyLightbox-Caption' ).remove();
	},

	// NAVIGATION
	navigationOn = function( instance, selector )
	{
		var images = $( selector );
		if( images.length )
		{
			var nav = $( '<div id="tiFyLightbox-Nav"></div>' );
			for( var i = 0; i < images.length; i++ )
				nav.append( '<button type="button"></button>' );

			nav.appendTo( 'body' );
			nav.on( 'click touchend', function(){ return false; });

			var navItems = nav.find( 'button' );
			navItems.on( 'click touchend', function()
			{
				var $this = $( this );
				if( images.eq( $this.index() ).attr( 'href' ) != $( '#tiFyLightbox' ).attr( 'src' ) )
					instance.switchImageLightbox( $this.index() );

				navItems.removeClass( 'active' );
				navItems.eq( $this.index() ).addClass( 'active' );

				return false;
			})
			.on( 'touchend', function(){ return false; });
		}
	},
	navigationUpdate = function( selector )
	{
		var items = $( '#tiFyLightbox-Nav button' );
		items.removeClass( 'active' );
		items.eq( $( selector ).filter( '[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"]' ).index( selector ) ).addClass( 'active' );
	},
	navigationOff = function()
	{
		$( '#tiFyLightbox-Nav' ).remove();
	},

	// ARROWS
	arrowsOn = function( instance, selector )
	{
		var $arrows = $( '<button type="button" class="tiFyLightbox-Arrow tiFyLightbox-Arrow--left"></button><button type="button" class="tiFyLightbox-Arrow tiFyLightbox-Arrow--right"></button>' );

		$arrows.appendTo( 'body' );

		$arrows.on( 'click touchend', function( e )
		{
			e.preventDefault();

			var $this	= $( this ),
				$target	= $( selector + '[href="' + $( '#tiFyLightbox' ).attr( 'src' ) + '"]' ),
				index	= $target.index( selector );

			if( $this.hasClass( 'tiFyLightbox-Arrow--left' ) )
			{
				index = index - 1;
				if( !$( selector ).eq( index ).length )
					index = $( selector ).length;
			}
			else
			{
				index = index + 1;
				if( !$( selector ).eq( index ).length )
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
	
	var o = {};
	o.selector			= 'id="tiFyLightbox"';
	o.enableKeyboard 	= tiFyLightbox.keyboard;
	o.quitOnDocClick 	= tiFyLightbox.overlay_close;
	o.animationSpeed 	= parseInt( tiFyLightbox.animation_speed );

	var selector = {}, instance = {};
	$( '[id^="gallery-"' ).each( function(i){
		selector[i] = '#' + $(this).attr( 'id' ) + ' a';
		
		var opts = $.extend(
			o,
			{
				onStart:		function() { 
					if( tiFyLightbox.overlay )
						overlayOn(); 
					if( tiFyLightbox.close_button )
						closeButtonOn( instance[i] );
					if( tiFyLightbox.navigation )  
						arrowsOn( instance[i], selector[i] ); 
					if( tiFyLightbox.tabs )
						navigationOn( instance[i], selector[i] );
				},
				onEnd:			function() { 
					if( tiFyLightbox.overlay )
						overlayOff(); 
					if( tiFyLightbox.caption )
						captionOff(); 
					if( tiFyLightbox.close_button )					
						closeButtonOff();
					if( tiFyLightbox.navigation )  
						arrowsOff();
					if( tiFyLightbox.spinner ) 
						activityIndicatorOff(); 
					if( tiFyLightbox.tabs )
						navigationOff();
				},
				onLoadStart: 		function() { 
					if( tiFyLightbox.caption )
						captionOff();
					if( tiFyLightbox.spinner ) 
						activityIndicatorOn(); 
				},
				onLoadEnd:	 	function() { 
					if( tiFyLightbox.caption )					
						captionOn();
					if( tiFyLightbox.spinner ) 
						activityIndicatorOff(); 
					if( tiFyLightbox.navigation )
						$( '.tiFyLightbox-Arrow' ).css( 'display', 'block' ); 
					if( tiFyLightbox.tabs )
						navigationUpdate( selector[i] );
				}
			}	
		);
		console.log( opts );	
		instance[i] = $( selector[i] ).filter( function(){
			return $(this).attr('href').match(/\.(jpg|png|gif)/i);
		}).imageLightbox( opts );
	})
});
