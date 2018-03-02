/**
Dependencies: jquery
*/
var mkpbx_custom_bkg_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$(document).on('click', '#add-custom-background', function( e ){
		e.preventDefault();
	 	
	 	var name = $(this).data('name');
	 		 	
		if ( mkpbx_custom_bkg_frame ) {
			mkpbx_custom_bkg_frame.open();
			return;
		}

		mkpbx_custom_bkg_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			editing:    true,
			button: {
				text: $( this ).data( 'uploader_button_text' ),
			},
			multiple: false,
			library:{ type: 'image'}// ['image/gif','image/png']}
		});
		 
		mkpbx_custom_bkg_frame.on( 'select', function() {
			attachment = mkpbx_custom_bkg_frame.state().get('selection').first().toJSON();
			$( '#mkpbx_custom_background' ).css( 'background-image', 'url('+attachment.url+'' );
			$( '#mkpbx_custom_background input[name="'+name+'"]' ).val( attachment.url );
			$( '#mkpbx_custom_background .reset-cbkg:hidden' ).fadeIn();
		});	 

		mkpbx_custom_bkg_frame.open();		
	});
	$(document).on('click', '.reset-cbkg', function(e){
		e.preventDefault();
		$(this).fadeOut();
		var name = $(this).data('name');
		$( '#mkpbx_custom_background input[name="'+name+'"]' ).val( '' );
		$( '#mkpbx_custom_background' ).css( 'background-image', 'url('+$('#original_background').val()+'' );
		
	});
});