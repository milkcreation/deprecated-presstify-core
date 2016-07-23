var tify_threesixty_view,
	tify_threesixty_view_get_items;
jQuery( document ).ready( function($){	
	/* = ARGUMENTS = */
	var threesixty_view;
		
	tify_threesixty_view_get_items = function( $container ){
		var items = [];
		$( '[data-tify_threesixty_view="list"] > li', $container ).each( function(){
			items.push( $(this).data( 'tify_threesixty_view' ) );
		});
		
		return items;
	};
			
	tify_threesixty_view = function( $container ){		
		var $this = $('[data-tify_threesixty_view="container"]', $container ),
			total_frames = $this.data( 'frames' ),
			items = tify_threesixty_view_get_items( $container ),
			width,
			height,
			autoplaydirection = $this.data( 'autoplaydirection' );
		
		// Bypass
		if( items.length === 0 )
			return;
		
		// Définition de la largeur de la vue 360 degrés
		if( $this.data( 'width' ) == 'auto' )
			width = $this.width();
		else
			width = $this.data( 'width' );
			
		// Définition de la hauteur de la vue 360 degrés
		if( $this.data( 'height' ) == 'auto' )
			height = $this.height();
		else
			height = $this.data( 'height' );

	    threesixty_view = $('[data-tify_threesixty_view="container"]', $container ).ThreeSixty({
	        totalFrames			: total_frames, // Total no. of image you have for 360 slider
	        endFrame			: total_frames, // end frame for the auto spin animation
	        currentFrame		: 1, // This the start frame for auto spin
	        imgList				: '[data-tify_threesixty_view="display"]', // selector for image list
	        imgArray			: items,
	        progress			: '[data-tify_threesixty_view="spinner"]', // selector to show the loading progress
	        height				: height,
       		width				: width,
	        navigation			: $this.data( 'navigation' ) ? true : false,
	        autoplayDirection 	: $this.data( 'autoplay' ) ? autoplaydirection : 1,
	        drag 				: $this.data( 'drag' ) ? true : false,
	        responsive			: true
	    });
	   // Autoplay
	   if( $this.data( 'autoplay' ) )
	  		threesixty_view.play();			
	};
			
	tify_threesixty_view( $( this ) );
});
