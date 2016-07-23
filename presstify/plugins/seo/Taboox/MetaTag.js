jQuery(document).ready( function($){
	/** == Saisie du texte du bouton == **/
	$( '[data-fill_out]' ).keyup( function(){
		if( $( this ).val() )
			$( $( this ).data('fill_out') ).html( $( this ).val() );
		else
			$( $( this ).data('fill_out') ).html( $( $( this ).data('fill_out') ).data( 'original' ) );
	});
});