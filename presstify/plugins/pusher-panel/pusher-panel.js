jQuery( document ).ready( function(){
	$( '.tify-pusher_toggle' ).click( function(e){
		e.preventDefault();
		var dir = $(this).data('dir');
		$( this ).toggleClass( 'active' );
		$( 'body' ).toggleClass( 'tify-pusher_'+dir+'_active' );	
	});
	
	var isIOS = /iPhone|iPad|iPod/.test( navigator.userAgent ),
		isAndroid = navigator.userAgent.indexOf( 'Android' ) !== -1,
		isIE8 = $( document.documentElement ).hasClass( 'ie8' ),
	
		$window		= $( window ),
		$toggle		= $( '.tify-pusher_panel > .toggle-button' ),
		$panel 		= $( '.tify-pusher_panel' ),
		$panelWrap 	= $( '.tify-pusher_panel > .wrapper' ),
		$adminbar 	= $( '#wpadminbar' ),
		
		lastScrollPosition = $window.scrollTop(),
		pinnedTop = false,
		pinnedBottom = false,
		panelTop = 0,
		
		height = {
			window		: $window.height(),
			//target		: $target.height(),
			adminbar	: $adminbar.size()? $adminbar.height() : 0,
			panel		: $panelWrap.outerHeight()
		};
	
	pinPanel();
	$window.on( 'resize scroll', pinPanel );
		
	function pinPanel( event ) {
		var windowPos = $window.scrollTop(),
			resizing = ! event || event.type !== 'scroll';
			
		$toggle.css({
			position : 'absolute',
			top: windowPos - $panelWrap.offset().top + height.adminbar +'px'
		});
					
		if( resizing ){
			height.window = $window.height();
			height.adminbar = $adminbar.size()? $adminbar.height() : 0;
		}
		
		if ( height.panel + height.adminbar > height.window ) {			
			// Défilement vers le bas
			if ( windowPos > lastScrollPosition ){
				if ( pinnedTop ) {
					// let it scroll
					pinnedTop = false;
					menuTop = $panelWrap.offset().top - height.adminbar - ( windowPos - lastScrollPosition );
					/*
					if ( menuTop + height.panel + height.adminbar < windowPos + height.window ) {
						menuTop = windowPos + height.window - height.panel - height.adminbar;
					}*/
	
					$panel.css({
						position: 'absolute',
						top: menuTop,
						bottom: ''
					});
				} else if( ! pinnedBottom && $panelWrap.offset().top + height.panel < windowPos + height.window ){			
					pinnedBottom = true;
					
					$panel.css({
						'position'  : 'fixed',
						'top'		: '',
						'bottom'	: 0
					});
					$toggle.css({ position : 'fixed', top: height.adminbar +'px' });
				}	
			// Défilement vers le haut	
			} else if ( windowPos < lastScrollPosition ) {
				if ( pinnedBottom ) {
					pinnedBottom = false;
					
					menuTop = $panelWrap.offset().top - height.adminbar + ( lastScrollPosition - windowPos );
					/*
					if ( menuTop + height.panel > windowPos + height.window ) {
						menuTop = windowPos;
					}*/
	
					$panel.css({
						position: 'absolute',
						top: menuTop,
						bottom: ''
					});
				} else if ( ! pinnedTop && $panelWrap.offset().top >= windowPos + height.adminbar ) {
					pinnedTop = true;
	
					$panel.css({
						position: 'fixed',
						top: '',
						bottom: ''
					});
					$toggle.css({ position : 'fixed', top: height.adminbar +'px' });
				}
			} else if ( resizing ) {
				// Resizing
				pinnedTop = pinnedBottom = false;
				menuTop = windowPos + height.window - height.panel - height.adminbar - 1;
	
				if ( menuTop > 0 ) {
					$panel.css({
						position: 'absolute',
						top: menuTop,
						bottom: ''
					});
				} else {
					//unpinMenu();
				}
			}
		} else {
			$panel.css({
				position: 'fixed',
				top: height.adminbar,
				bottom: 0
			});
			pinnedTop = false; pinnedBottom = false;
			return;
		}
		
		lastScrollPosition = windowPos;
	};
});