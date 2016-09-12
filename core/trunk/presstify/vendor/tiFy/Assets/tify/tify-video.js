var tify_video;
jQuery( document ).ready( function(e){	
	/** == Chargement de la vidéo via Ajax == **/
	tify_video = function( $this ){
		$( '.modal-spinner', $this ).show();
		$.post( 
			tify_ajaxurl, 
			{ action : 'tify_video', 'attr' : $this.data( 'video_attr' ) }, 
			function( resp ){
				$( '.modal-spinner', $this ).hide();
				$( '.modal-body', $this ).html( resp );
				$( window ).trigger( 'resize.tify_video' );
				/*if( $( resp ).hasClass( 'tify_video-shortcode' ) )
					$( 'video', $wrapper ).mediaelementplayer( _wpmejsSettings );	*/			
			}
		);		
	};
	/** == Action à l'ouverture de la modal == **/
	$( '.tify_video.modal' ).on( 'show.bs.modal', function(e){
		tify_video( $( this ) );		
	});
	/** == Action à la fermeture de la modal == **/
	$( '.tify_video.modal' ).on( 'hidden.bs.modal', function (e){
		$( '.modal-body', $( this ) ).empty();
	});
	/** == Action au redimensionnement de la modal == **/	
	/*$( window ).on( 'resize.tify_video', function(e){
		$( '.tify_video.modal .tify_video-container iframe, .tify_video.modal .tify_video-container object, .tify_video.modal .tify_video-container embed, .tify_video.modal .tify_video-container video' ).each( function(){
			$this = $(this);
			$closest = $(this).closest( '.modal-content' );
			maxHeight = $closest.innerHeight() - $( '.modal-header', $closest ).outerHeight() - $( '.modal-footer', $closest ).outerHeight();
			$this.css( 'max-height', maxHeight );
			$( '.modal-body', $closest ).height( $this.height() );
		});	
	});*/
	$( '.tify_video.modal' ).each( function(){
		$( this ).modal( );
	});
});