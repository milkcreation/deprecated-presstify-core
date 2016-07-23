( function( $ ) {
	var $body, $window, $sidebar, adminbarOffset, top = false,
	    bottom = false, windowWidth, windowHeight, lastWindowPos = 0,
	    topOffset = 0, bodyHeight, sidebarHeight, resizeTimer,
	    secondary, button;

	// Sidebar scrolling.
	function resize() {
		windowWidth = $window.width();

		top = bottom = false;
		$sidebar.removeAttr( 'style' );
		
		return true;
	}

	function scroll() {
		var windowPos = $window.scrollTop();

		sidebarHeight = $sidebar.height();
		windowHeight  = $window.height();
		bodyHeight    = $body.height();

		if ( sidebarHeight + adminbarOffset > windowHeight ) {
			if ( windowPos > lastWindowPos ) {
				if ( top ) {					
					top = false;
					topOffset = ( $sidebar.offset().top > 0 ) ? $sidebar.offset().top - adminbarOffset : 0;
					$sidebar.attr( 'style', 'top: ' + topOffset + 'px;' );
				} else if ( ! bottom && windowPos + windowHeight > sidebarHeight + $sidebar.offset().top && sidebarHeight + adminbarOffset < bodyHeight ) {
					bottom = true;
					$sidebar.attr( 'style', 'position: fixed; bottom: 0;' );
				}
			} else if ( windowPos < lastWindowPos ) {
				if ( bottom ) {
					bottom = false;
					topOffset = ( $sidebar.offset().top > 0 ) ? $sidebar.offset().top - adminbarOffset : 0;
					$sidebar.attr( 'style', 'top: ' + topOffset + 'px;' );
				} else if ( ! top && windowPos + adminbarOffset < $sidebar.offset().top ) {
					top = true;
					$sidebar.attr( 'style', 'position: fixed;' );
				}
			} else {		
				top = bottom = false;
				topOffset = ( $sidebar.offset().top > 0 ) ? $sidebar.offset().top - adminbarOffset : 0;
				$sidebar.attr( 'style', 'top: ' + topOffset + 'px;' );
			}
		} else if ( ! top ) {
			
			top = true;
			$sidebar.attr( 'style', 'position: fixed;' );
		}

		lastWindowPos = windowPos;
		
		return true;
	}

	function resizeAndScroll() {
		resize();
		scroll();
	}
	
	jQuery( document ).ready( function( $ ) {
		$body          = $( document.body );
		$window        = $( window );
		$sidebar       = $( '#tify_sidebar-panel_left' ).first();
		adminbarOffset = $body.is( '.admin-bar' ) ? $( '#wpadminbar' ).height() : 0;
	
		$( document ).on( 'click.tify_sidebar', '[data-toggle="tify_sidebar"]', function(e){
			e.preventDefault();
			var dir = $(this).data('dir');
			$( this ).toggleClass( 'active' );
			$body.toggleClass( 'tify_sidebar-'+dir+'_active' );	
		});
		
		$window
			.on( 'scroll.tify_sidebar', scroll )
			.on( 'load.tify_sidebar', resizeAndScroll )
			.on( 'resize.tify_sidebar', function() {
				clearTimeout( resizeTimer );
				resizeTimer = setTimeout( resizeAndScroll, 500 );
			});
	
		for ( var i = 1; i < 6; i++ ) {
			setTimeout( resizeAndScroll, 100 * i );
		}
	});
})( jQuery );