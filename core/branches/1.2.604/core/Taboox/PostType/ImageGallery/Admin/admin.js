var tify_taboox_image_gallery_frame;
jQuery(document).ready( function($){
	// Affichage du selecteur de média
	$( document ).on( 'click', '.taboox_image_gallery-add', function( e ){
		e.preventDefault();
	 	
	 	var $list = $(this).prev(),
	 		name 	= $(this).data('name'),
			max = $( this ).data( 'max' );
		
		if( max > 0 && $( 'li', $list ).length >= max ){ 
			alert( tify_taboox_image_gallery.maxAttempt );
			return false;
		}		
		
		tify_taboox_image_gallery_frame = wp.media.frames.file_frame = wp.media({
			title		: $( this ).data( 'media_title' ),
			editing		: true,
			button		: {
				text		: $( this ).data( 'media_button_text' ),
			},
			multiple	: true,
			library		: { 
				type		: 'image' // ['image/gif','image/png']
			}	
		});
		 
		tify_taboox_image_gallery_frame.on( 'select', function() {
			var selection = tify_taboox_image_gallery_frame.state().get( 'selection' );
			selection.map( function( attachment ) {
				var order = $( 'li', $list ).length+1;
			 	attachment = attachment.toJSON();
			 	html  = '<li>';
			 	html += 	'<img src="'+ attachment.sizes['thumbnail'].url +'" />';
			 	html += 	'<input type="hidden" name="tify_meta_post['+name+'][]" value="'+ attachment.id +'" />';
			 	html += 	'<a href="#remove" class="tify_button_remove"></a>';
			 	html += 	'<input type="text" class="order" value="'+ order +'" size="1" readonly/>';
			 	html += '</li>';			 	
			 	
				$list.append(html);
			});
		});	
		tify_taboox_image_gallery_frame.open();
	});
	
	// Ordonnacement des images de la galerie
	$( '.taboox_image_gallery-list' ).sortable({
		placeholder: "ui-sortable-placeholder",
		update : function( event, ui ){
			refresh_order( $(this) );
		}
	});
	$( '.taboox_image_gallery-list' ).disableSelection();
		
	// Suppression d'une image de la galerie
	$( document ).on( 'click', '.taboox_image_gallery-list > li > .tify_button_remove', function(e){
		$container = $(this).parent();
		$list = $container.parent();
		$container.fadeOut( function(){
			$container.remove();
			refresh_order( $list );
		});
	});
	
	function refresh_order( $list ){
		$('input.order',  $list ).each( function( u, v ){
			$(this).val( $(this).parent().index()+1 );
		});
	}
});
