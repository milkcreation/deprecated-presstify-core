jQuery(document).ready( function($){
	// Recherche par autocompletion
	var post_type, name, max, order, $list, post__not_in = [];
	 
	$( '.tify_taboox_related_posts-search' ).autocomplete({
		source:	function( request, response ){
			$.post(	tify_ajaxurl, { action : 'tify_taboox_related_posts', post_type : post_type, post_status : post_status, term : request.term, name : name, order : order, post__not_in : post__not_in }, function( data ){
				response(data);
			}, 'json' );
		},
		search: function( event, ui ) {			
			max				= $( this ).data( 'max' );
			post_type		= $( this ).data( 'post_type' );
			post_status		= $( this ).data( 'post_status' );
			name 			= $( this ).data( 'name' );
			$list			= $( this ).next();
			order 			= $( "li", $list ).size()+1;
			post__not_in 	= [];			
			post__not_in.push( $( "#post_ID" ).val() );
			$( 'li .post_id', $list ).each( function(){
				post__not_in.push( $( this ).val() );
			});
		},
		minLength:	2,
		select: function( event, ui ) {			
			event.preventDefault();
			console.log( ui.item );
			if( max > 0 && $( 'li', $list ).length >= max ){ 
				alert( tify_taboox_related_posts.maxAttempt );
				$(this).val('');
			} else {
				var item 	= ui.item;
				$list.append( item.display );
				$(this).val('');
			}			
		}
	});
	if( $( '.tify_taboox_related_posts-search' ).size() )
		$( '.tify_taboox_related_posts-search' ).each( function(){
			$(this).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li>" )
				.append( item.render )
				.appendTo( ul );
			};
		});
	// Ordonnacement des items
	$( ".tify_taboox_related_posts-list" ).sortable({
		placeholder: "ui-sortable-placeholder",
		update : function( event, ui ){
			$('input.order',  $(this) ).each( function( u, v ){
				$(this).val( $(this).parent().index()+1 );
			});
		}
	});
	$( ".tify_taboox_related_posts-list" ).disableSelection();
	// Suppression d'un items
	$(document).on('click', '.tify_taboox_related_posts-list .remove', function(e){
		e.preventDefault();	
		$(this).parent().fadeOut( function(){
			$(this).remove();
			$(this).closest( '.tify_taboox_related_posts-list' ).find( 'input.order' ).each( function( u, v ){
				$(this).val( $(this).parent().index()+1 );
			});
		});
	});
});