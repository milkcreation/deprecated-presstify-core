var tify_video;
jQuery( document ).ready( function(e){
	tify_video = function( attr, target ){
		if( ! target )
			target = '#tify_video-modal';
		if( target === '#tify_video-modal' ){
			var $target = $( target );
			var $wrapper = $( '#tify_video-wrapper', target );
		} else {
			var $target = $( target );
			var $wrapper = $( target );
		}
		if( $( '#tify_video-spinner' ).size() && ! $( '#tify_video-spinner' ).is( ':visible' ) )
			$( '#tify_video-spinner' ).show();

		$( target ).addClass('active');
		$.post( 
			tify_ajaxurl, 
			{ action : 'tify_video', 'attr' : attr }, 
			function( resp ){	
				if( $( '#tify_video-spinner' ).size() )	
					$( '#tify_video-spinner' ).hide();	
				
				$wrapper.html( resp );
				$( window ).trigger( 'resize.tify_video' );
				if( $( resp ).hasClass( 'tify_video-shortcode' ) )
					$( 'video', $wrapper ).mediaelementplayer( _wpmejsSettings );				
			}
		);		
	};
	/** == Action au click sur l'overlay de la modale == **/ 
	$( document ).on( 'click.tify_video.close', '#tify_video-modal > #tify_video-overlay', function(e){
		$( this ).parent().removeClass('active');
		$( '#tify_video-wrapper', $(this) ).empty();									
	});
	
	$( document ).on( 'click.tify_video.launch', 'a[data-tify_video]', function(e){
		e.preventDefault();
		var attr = { 
			src 		: $( this ).data( 'src' ), 
			poster 		: $( this ).data( 'poster' ), 
			loop 		: $( this ).data( 'loop' ),
			autoplay 	: $( this ).data( 'autoplay' ),
			preload  	: $( this ).data( 'preload' ),
			width		: $( this ).data( 'width' ),
			height		: $( this ).data( 'height' ),
			rel			: $( this ).data( 'rel' ) 
		};
		tify_video( attr, $( this ).data( 'target' ) );		
	});
	$( window ).on( 'resize.tify_video', function(e){
		$( '.tify_video-container iframe, .tify_video-container object, .tify_video-container embed, .tify_video-container video' ).each( function(){
			$(this).css( 'max-height', $(this).parent().parent().height() );
		});		
	});
});

// DEPRECATED
/* MODIFICATION DES OPTIONS DE MEDIAELEMENTS 
	var o = $.extend( _wpmejsSettings, {
    // remove or reorder to change plugin priority
    plugins: [ 'youtube', 'vimeo' ],
   /* // specify to force MediaElement to use a particular video or audio type
    // path to Flash and Silverlight plugins
    // name of flash file
    flashName: 'flashmediaelement.swf',
    // name of silverlight file
    silverlightName: 'silverlightmediaelement.xap',
    // default if the <video width> is not specified
    defaultVideoWidth: 480,
    // default if the <video height> is not specified    
    defaultVideoHeight: 270,
    // overrides <video width>
    pluginWidth: -1,
    // overrides <video height>      
    pluginHeight: -1,
    // rate in milliseconds for Flash and Silverlight to fire the timeupdate event
    // larger number is less accurate, but less strain on plugin->JavaScript bridge
    timerRate: 250,
});*/

/* DESACTIVATION DU SCROLL
	var scrollPosition = [
        self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
        self.pageYOffset || document.documentElement.scrollTop  || document.body.scrollTop
	];
	var html = jQuery('html'); // it would make more sense to apply this to body, but IE7 won't have that
 	html.data('scroll-position', scrollPosition);
  	html.data('previous-overflow', html.css('overflow'));
  	html.css('overflow', 'hidden');
  	window.scrollTo(scrollPosition[0], scrollPosition[1]);*/
  	
/* REACTIVATION DU SCROLL
	var html = jQuery('html');
	var scrollPosition = html.data('scroll-position');
	html.css( 'overflow', html.data('previous-overflow') );
	window.scrollTo( scrollPosition[0], scrollPosition[1] );*/