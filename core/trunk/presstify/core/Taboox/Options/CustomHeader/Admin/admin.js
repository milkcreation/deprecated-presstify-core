/**
Dependencies: jquery, tify_controls-media_image
*/
var mkpbx_custom_header_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$(document).on('click', '.mkpbx-custom_header-add', function( e ){
		e.preventDefault();
		
		var $this = $(this),
			$closest = $this.closest('div');		

	 	var title = $(this).data( 'media_library_title' ),
	 		button = $(this).data( 'media_library_button' );
	 		 	
		if ( mkpbx_custom_header_frame ) {
			mkpbx_custom_header_frame.open();
			return;
		}

		mkpbx_custom_header_frame = wp.media.frames.file_frame = wp.media({
			title 		: title,
			editing 	: true,
			button : {
				text 		: button,
			},
			multiple 	: false,
			library:{ 
				type		: 'image'
			}
		});
		 
		mkpbx_custom_header_frame.on( 'select', function() {
			attachment = mkpbx_custom_header_frame.state().get('selection').first().toJSON();
			$this.css( 'background-image', 'url('+attachment.url+'' );
			$( '.customized_header', $closest ).val( attachment.url );
			$( '.reset:hidden', $closest ).fadeIn();
		});	 

		mkpbx_custom_header_frame.open();		
	});
	
	$(document).on('click', '.mkpbx-custom_header .reset', function(e){
		e.preventDefault();
		var $this = $(this),
			$closest = $this.closest( 'div' );
		
		$this.hide();
		$( '.customized_header', $closest ).val( '' );
		$( '.mkpbx-custom_header-add', $closest ).css( 'background-image', 'url('+ $( '.mkpbx-custom_header-add', $closest ).data( 'default' ) +')' );		
	});
});