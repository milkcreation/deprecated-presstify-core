var tify_control_media_file_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$( document ).on( 'click', '[data-tify_control="media_file"]:not(.tify_control_media_file-reset)', function( e ){
		e.preventDefault();
		
		var $closest = $(this);		

	 	var title 		= $(this).data( 'media_library_title' ),
	 		button 		= $(this).data( 'media_library_button' ),
	 		filetype	= $(this).data( 'media_library_filetype' );
	 		 	
		tify_control_media_file_frame = wp.media.frames.file_frame = wp.media({
			title 		: title,
			editing 	: true,
			button : {
				text 	: button,
			},
			multiple 	: false,
			library:{ 
				type	: filetype
			}
		});
		 
		tify_control_media_file_frame.on( 'select', function() {
			attachment = tify_control_media_file_frame.state().get('selection').first().toJSON();
			$closest.addClass( 'active' );
			console.log( attachment );
			$( '.tify_control_media_file-title', $closest ).val( attachment.title + ' → ' + attachment.filename );
			$( '.tify_control_media_file-id', $closest ).val( attachment.id );
		});	 
		
		tify_control_media_file_frame.open();		
	});
	// Réinitialisation de l'image originale
	$( document ).on( 'click', '[data-tify_control="media_file"] > .tify_control_media_file-reset', function(e){
		e.preventDefault();
		e.stopPropagation();
		
		$closest = $(this).parent();
		
		$closest.removeClass( 'active' );
		$( '.tify_control_media_file-title', $closest ).val( $closest.data( 'original_title' ) );
		$( '.tify_control_media_file-id', $closest ).val( $closest.data( 'original_id' ) );
	});
});