var taboox_video_gallery_src_frame, taboox_video_gallery_poster_frame;
jQuery( document ).ready( function($){
	// Ajout d'une interface de saisie vidéo
	$( '.taboox_video_gallery-add' ).click( function( e ){
		e.preventDefault();
		
		var $list = $( this ).prev(),
			$spinner = $( this ).next(),
			name = $(this).data( 'name' ),
			max = $( this ).data( 'max' );

		if( max > 0 && $( 'li', $list ).length >= max ){ 
			alert( tify_taboox_video_gallery.maxAttempt );
			return false;
		}
			
		$spinner.css( 'visibility', 'visible' );
		$.post( tify_ajaxurl, { action: 'taboox_video_gallery_add_item', name: name }, function( resp ){
			console.log( resp );
			
			$list.append( resp );
			$spinner.css( 'visibility', 'hidden');
		}, 'html');
	});
	
	// Suppression d'une interface de saisie vidéo
	$( document ).on( 'click', '.taboox_video_gallery-list li .tify_button_remove', function(e){
		$container = $(this).parent();
		$container.fadeOut( function(){
			$container.remove();
		});
	});
	
	// Ajout d'une vidéo de la médiathèque
	$( document ).on('click', '.taboox_video_gallery-media_add', function( e ){
		e.preventDefault();
	 	
	 	var $target = $( this ).closest( 'li' ).find( '.src textarea' );
	 	
		taboox_video_gallery_src_frame = wp.media.frames.file_frame = wp.media({
			title		: $( this ).data( 'media_title' ),
			editing		:    true,
			button		: {
				text 		: $( this ).data( 'media_button_text' ),
			},
			multiple	: false,
			library		: { 
				type		: 'video'		// ['video/flv','video/mp4']
			} 
		});
		 
		taboox_video_gallery_src_frame.on( 'select', function() {
			attachment = taboox_video_gallery_src_frame.state().get('selection').first().toJSON();
			$target.html( attachment.url );
		});	 

		taboox_video_gallery_src_frame.open();
	});
	
	// Ajout d'une jaquette pour la vidéo
	$(document).on('click', '.taboox_video_gallery-poster_add', function( e ){
		e.preventDefault();	 	
	 	
	 	var $target = $( this );
	 	
		taboox_video_gallery_poster_frame = wp.media.frames.file_frame = wp.media({
			title 		: $( this ).data( 'media_title' ),
			editing 	:    true,
			button 		: {
				text : $( this ).data( 'media_button_text' ),
			},
			multiple : false,
			library:{ type: 'image'}// ['image/gif','image/png']}
		});
		 
		taboox_video_gallery_poster_frame.on( 'select', function() {
			attachment = taboox_video_gallery_poster_frame.state().get('selection').first().toJSON();
			$target.css( 'background-image', 'url('+attachment.url+'' );
			$target.find( 'input' ).val( attachment.id );
		});	 

		taboox_video_gallery_poster_frame.open();		
	});
	$(document).on('click', '#mkpbx-simple_video_poster .reset', function(e){
		e.preventDefault();
		$(this).fadeOut();
		$( '#mkpbx-simple_video_poster input' ).val( '' );
		$( '#mkpbx-simple_video_poster' ).css( 'background-image', 'none' );		
	});
	
	// Ordonnacement des fichiers
	$( ".taboox_video_gallery-list" ).sortable({
		placeholder: "ui-sortable-placeholder",
		axis: 'y'
	});
});
