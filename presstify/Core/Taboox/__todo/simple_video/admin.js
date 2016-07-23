/**
Dependencies: jquery
*/
var mkpbx_simple_video_src_frame, mkpbx_simple_video_poster_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$(document).on('click', '#select-simple-video-src', function( e ){
		e.preventDefault();
	 	
	 	var target = $(this).data('target');
	 	
		if ( mkpbx_simple_video_src_frame ) {
			mkpbx_simple_video_src_frame.open();
			return;
		}

		mkpbx_simple_video_src_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			editing:    true,
			button: {
				text: $( this ).data( 'uploader_button_text' ),
			},
			multiple: false,
			library:{ type: 'video'} // ['video/flv','video/mp4']}
		});
		 
		mkpbx_simple_video_src_frame.on( 'select', function() {
			attachment = mkpbx_simple_video_src_frame.state().get('selection').first().toJSON();
			$( target ).html( attachment.url );
		});	 

		mkpbx_simple_video_src_frame.open();
	});
	
	// Affichage du selecteur de média
	$(document).on('click', '#mkpbx-simple_video_poster-add', function( e ){
		e.preventDefault();	 	
	 	var title = $(this).data('media_library_title');
	 	var button = $(this).data('media_library_button');
	 		 	
		if ( mkpbx_simple_video_poster_frame ) {
			mkpbx_simple_video_poster_frame.open();
			return;
		}

		mkpbx_simple_video_poster_frame = wp.media.frames.file_frame = wp.media({
			title : title,
			editing :    true,
			button : {
				text : button,
			},
			multiple : false,
			library:{ type: 'image'}// ['image/gif','image/png']}
		});
		 
		mkpbx_simple_video_poster_frame.on( 'select', function() {
			attachment = mkpbx_simple_video_poster_frame.state().get('selection').first().toJSON();
			$( '#mkpbx-simple_video_poster' ).css( 'background-image', 'url('+attachment.url+'' );
			$( '#mkpbx-simple_video_poster input' ).val( attachment.id );
			$( '#mkpbx-simple_video_poster .reset:hidden' ).fadeIn();
		});	 

		mkpbx_simple_video_poster_frame.open();		
	});
	$(document).on('click', '#mkpbx-simple_video_poster .reset', function(e){
		e.preventDefault();
		$(this).fadeOut();
		$( '#mkpbx-simple_video_poster input' ).val( '' );
		$( '#mkpbx-simple_video_poster' ).css( 'background-image', 'none' );		
	});
});
