var tify_control_media_image_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$( document ).on( 'click', '.tify_control_media_image-add[data-image_editable="1"]', function( e ){
		e.preventDefault();
		
		var $this = $(this),
			$closest = $this.closest( '.tify_control_media_image' );		

	 	var title = $(this).data( 'media_library_title' ),
	 		button = $(this).data( 'media_library_button' );
	 		 	
		tify_control_media_image_frame = wp.media.frames.file_frame = wp.media({
			title 		: title,
			editing 	: true,
			button : {
				text 	: button,
			},
			multiple 	: false,
			library:{ 
				type	: 'image'
			}
		});
		 
		tify_control_media_image_frame.on( 'select', function() {
			attachment = tify_control_media_image_frame.state().get('selection').first().toJSON();
			
			$this.css( 'background-image', 'url('+attachment.url+'' );
			$( '.tify_control_media_image-input', $closest ).val( attachment.id );
			$( '.tify_control_media_image-reset:hidden', $closest ).fadeIn();
		});	 

		tify_control_media_image_frame.open();		
	});
	// Réinitialisation de l'image originale
	$( document ).on( 'click', '.tify_control_media_image-reset', function(e){
		e.preventDefault();
		var $this = $(this),
			$closest = $this.closest( '.tify_control_media_image' );
		
		$this.hide();
		$( '.tify_control_media_image-input', $closest ).val( '' );
		$( '.tify_control_media_image-add', $closest ).css( 'background-image', 'url('+ $( '.tify_control_media_image-add', $closest ).data( 'default' ) +')' );		
	});
});