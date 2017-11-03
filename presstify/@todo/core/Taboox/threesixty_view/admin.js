/**
Dependencies: jquery, jquery-ui-sortable
Version: 2.0
*/

var threesixty_view_frame;

jQuery(document).ready( function($){
	var $gallery = $( '.threesixty_view-list' );
	
	// Affichage du selecteur de média
	$(document).on('click', '.threesixty_view-add', function( e ){
		e.preventDefault();
	 	
	 	var name = $(this).data('name'),
	 		post_id = $(this).data('post_id');
		var $target = $(this).prev();
		
		
		if( ( $( '> li',  $gallery ).length >= tify.max ) && ( tify.max >= 0 ) ){						
			alert( tify.l10nMax );
			return false;
		}
		
		threesixty_view_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'media_title' ),
			editing:    true,
			button: {
				text: $( this ).data( 'media_button_text' ),
			},
			multiple: true,
			library:{ type: 'image'}// ['image/gif','image/png']}
		});
		
		threesixty_view_frame.on( 'select', function() {
			// Déclarations des variables
			var selection = threesixty_view_frame.state().get('selection'),
				order = $( '> li', $target ).length+1,
				count = 0,
				array = new Array(),
				current_elem = 0,
				attachment_length = ( ( selection.length > tify.max ) && ( tify.max >= 0 ) ) ? tify.max : selection.length;
			
			
			selection.map( function( attachment ) {
				
				attachment = attachment.toJSON();		
				
		 		count++;
		 		
		 		if( ( ( count <= tify.max ) && ( tify.max > 0 ) ) || ( tify.max <= 0 ) ){
		 			
		 			if( count > 1 )
			 		order++;
			 	
			 		$.ajax({
						url 		: tify_ajaxurl,
						data 		: { action : 'threesixty_view_item', post_id : post_id, attachment_id : attachment.id, order : order },
						dataType 	: 'json',
						type 		: 'post',
						beforeSend : function(){
							$( '.threesixty_view-loading, .threesixty_view-overlay' ).fadeIn( 300 );
						},
						complete 	: function(){
							$( '.threesixty_view-loading, .threesixty_view-overlay' ).fadeOut( 300 );
						},
						success		: function( resp ){
							current_elem++;					
							
							array[resp.order] = resp.html;
							
							if( current_elem == attachment_length ){	
								$.each( array, function( key, value ){
									$target.append( value );											  
								});	
							}																
						}
					});					
		 		}		 		 		 	
			});		
		});
		threesixty_view_frame.open();
	});
	// Ordonnacement des images de la galerie
	$( ".threesixty_view-list" ).sortable({
		placeholder: "ui-sortable-placeholder",
		update : function( event, ui ){
			$('input.order',  $(this) ).each( function( u, v ){
				$(this).val( $(this).parent().index()+1 );
				threesixty_view_item_reorder( $( this ) );
			});
		}
	});
	$( ".threesixty_view-list" ).disableSelection();
	// Suppression d'une image de la galerie
	$( document ).on( 'click', '.threesixty_view-list li .tify_button_remove', function(e){
		var $container = $(this).parent(),
			$target	   = $(this).closest( '.threesixty_view-list' );
		$container.fadeOut( function(){
			$container.remove();
			threesixty_view_item_reorder( $target );
		});
	});
	// Mise à jour de l'ordre des items
	function threesixty_view_item_reorder( $target ){
		$( '> li', $target ).each( function(){
			$(this).find( '.order').val( parseInt( $(this).index()+1 ) );
		});
	}
	threesixty_view_item_reorder( $gallery );
});
