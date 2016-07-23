jQuery( document ).ready( function($){
	$( '#ConfigConvertor > form' ).submit( function(e){
		e.preventDefault();
		
		$.post( tify_ajaxurl, $(this).serialize(), function(resp){
			$( '#ConfigConvertor textarea[name="output"]' ).html( resp );
		});
	});
});