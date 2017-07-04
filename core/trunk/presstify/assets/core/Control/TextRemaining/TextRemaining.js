jQuery( document ).ready( function($){
	$( document ).on( 'tify_control.text_remaining.init', function( event, obj ){		
		var $feedback = $( $( obj ).data( 'feedback_area' ) );
		var text_max = $feedback.data( 'max-length' );
		var text_remaining = text_max - $( obj ).val().length;
		// Initialisation
    	$feedback.html( feedback_text( text_remaining ) );
		
		// Ecoute
   		$( obj ).keyup( function() {
	        var text_length = $(this).val().length;
	        var text_remaining = text_max - text_length;	
			$feedback.html( feedback_text( text_remaining ) );
			if( text_remaining < 0 )
				$feedback.addClass( 'reached' );
			else
				$feedback.removeClass( 'reached' );
		}).trigger('keyup');
	});	
	$( '[data-tify_control="text_remaining"]' ).each( function(){
		$( document ).trigger( 'tify_control.text_remaining.init', $( this ) );
	});
});

function feedback_text( text_remaining ){
	if( ( text_remaining > 1 ) || ( text_remaining < -1 ) ){
		return '<b>'+ text_remaining +'</b> '+ tifyTextRemaining.plural;
	} else if( ( text_remaining == 0 ) ){
		return tifyTextRemaining.none;
	} else {
		return '<b>'+ text_remaining +'</b> '+ tifyTextRemaining.singular;
	}	
}
