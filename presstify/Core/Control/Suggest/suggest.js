jQuery( document ).ready( function($){
	$( document ).on( "keydown.autocomplete","[data-tify_control_suggest]",function(e){
		$( this ).each( function(){
			var action	 			= $(this).data( 'tify_control_suggest' ),
				$spinner			= $(this).find( '.tify_spinner' );
			
		var		query_args			= $(this).data( 'query_args' ),	
				elements			= $(this).data( 'elements' ),
				extras				= $(this).data( 'extras' ),
				options				= $(this).data( 'options' ),
				picker				= $(this).data( 'picker' ),
				defaults = {
					source		: function( request, response ){
						$.post(	tify_ajaxurl, { action : action, term : request.term,  query_args : query_args, elements : elements, extras : extras }, function( data ){
							( ! data.length ) ? response({ 'label':'X', 'value':'Y', 'render':'' }) : response(data);
						}, 'json' );
					},
					change		: function( event, ui ){
						// $( 'autocompleteselector' ).on( "autocompletechange", function( event, ui ) {} );
					},
					close		: function( event, ui ){
						//$(this).val('');
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
				};	
			
			options = $.extend( options, defaults );

			$( 'input[type="text"]', $(this) )
				.autocomplete( options )
				.data( 'ui-autocomplete' )._renderItem = function(ul, item){
					ul.addClass( 'tify_control_suggest-picker '+ picker );
					
					return $( "<li>" )
						.append( item.render )
						.appendTo( ul );
				};
		});
	});
});