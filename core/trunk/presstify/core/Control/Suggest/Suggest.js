jQuery( document ).ready( function($){
	$( document ).on( 'click', '[data-tify_control_suggest] .tify_control_suggest_delete', function(e){
		e.preventDefault();
		var $closest	= $(this).closest( '[data-tify_control_suggest]' ),
			select		= $closest.data( 'select' );
		
		if( ! select )
			return false;
		
		$closest.removeClass( 'selected' ).html(); 
		$( '> input[type="text"]', $closest ).val('').prop( 'readonly', false );
		$( '.tify_control_suggest_select_value', $closest ).val('');
	});			
			
	$( document ).on( 'keydown.autocomplete','[data-tify_control_suggest]',function(e){
		$( this ).each( function(){
			var $this				= $(this),
				action	 			= $this.data( 'tify_control_suggest' ),
				select				= $this.data( 'select' ),
				$spinner			= $this.find( '.tify_spinner' );
			
		var		query_args			= $this.data( 'query_args' ),	
				elements			= $this.data( 'elements' ),
				extras				= $this.data( 'extras' ),
				options				= $this.data( 'options' ),
				picker				= $this.data( 'picker' ),
				defaults = {
					source		: function( request, response ){
					    $spinner.addClass( 'active' );
						$.post(	
						    tify_ajaxurl, 
						    { action : action, term : request.term,  query_args : query_args, elements : elements, extras : extras }, 
						    function( data ){
						        if( data.length ) {
						            response(data);
						        } else {
						            response( [{ value:'', label:'', render: tiFyControlSuggest.noResultsFound }] );
						        }
						        $spinner.removeClass( 'active' );
						        return;
						    }, 
						    'json' 
					    );
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
						// $( 'autocompleteselector' ).on( "autocompletereponse", function( event, ui ) {} );
					},
					search		: function( event, ui ) {						
						// $( 'autocompleteselector' ).on( "autocompletesearch", function( event, ui ) {} );
					},
					select		: function( event, ui ){
						if( select ) {
							event.preventDefault();
							$this.addClass( 'selected' );
							$( '> input[type="text"]', $this ).val( $( "<div/>" ).html( ui.item.label ).text() ).prop( 'readonly', true );
							$( '.tify_control_suggest_select_value', $this ).val( ui.item.value );
						}
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