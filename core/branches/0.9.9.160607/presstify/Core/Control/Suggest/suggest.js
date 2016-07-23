jQuery( document ).ready( function($){
	$( '[data-tify_control_suggest]' ).each( function(){
		var action	 			= $(this).data( 'tify_control_suggest' ),
			appendTo 			= '#'+ $(this).find( '.tify_control_suggest_response' ).attr( 'id' ),
			$spinner			= $(this).find( '.tify_spinner' );
			$selected_target 	= ( $(this).data( 'selected_target' ) ) ? $( $(this).data( 'selected_target' ) ) : false;
			query_args			= $(this).data( 'query_args' ),
			elements			= $(this).data( 'elements' ),
			extras				= $(this).data( 'extras' );
			
		$( 'input[type="text"]', $(this) )
			.autocomplete({
				source		: function( request, response ){
					$.post(	tify_ajaxurl, { action : action, term : request.term,  query_args : query_args, elements : elements, extras : extras }, function( data ){
						( ! data.length ) ? response({ 'label':'X', 'value':'Y', 'render':'' }) : response(data);
					}, 'json' );
				},
				appendTo	: appendTo,
				minLength	: 2,
				change		: function( event, ui ){
					// $( 'autocompleteselector' ).on( "autocompletechange", function( event, ui ) {} );
				},
				close		: function( event, ui ){
					$(this).val('');
					// $( 'autocompleteselector' ).on( "autocompleteclose", function( event, ui ) {} );
				},
				create		: function( event, ui ){
					// $( 'autocompleteselector' ).on( "autocompletecreate", function( event, ui ) {} );
				},
				focus		: function( event, ui ){
					// $( 'autocompleteselector' ).on( "autocompletefocus", function( event, ui ) {} );
				},
				open		: function( event, ui ){
					// $( 'autocompleteselector' ).on( "autocompleteopen", function( event, ui ) {} );
				},
				response 	: function( event, ui ) {
					$spinner.removeClass( 'active' );
					// $( 'autocompleteselector' ).on( "autocompletereponse", function( event, ui ) {} );
				},
				search		: function( event, ui ) {
					$spinner.addClass( 'active' );
					// $( 'autocompleteselector' ).on( "autocompletesearch", function( event, ui ) {} );
				},
				select		: function( event, ui ){
					// $( 'autocompleteselector' ).on( "autocompleteselect", function( event, ui ) {} );					
				}
			})
			.data( 'ui-autocomplete' )._renderItem = function(ul, item){
				return $( "<li>" )
					.append( item.render )
					.appendTo( ul );
			};
	});		
});