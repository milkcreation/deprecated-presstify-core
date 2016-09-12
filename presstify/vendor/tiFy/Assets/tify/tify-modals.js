jQuery( document ).ready( function($){
	$( '.tify_modal.modal' ).each( function(){
		$( this ).modal( );
	});
	$( '[data-toggle="tify_modal"]' ).click( function(e){
		e.preventDefault();
		$( '#'+ $( this ).data( 'target' ) ).modal( 'show' );
	});
});