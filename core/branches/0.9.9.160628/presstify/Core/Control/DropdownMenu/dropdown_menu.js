jQuery( document ).ready( function($){
	$( 'html, body' ).click( function( ) {
		$( '[data-tify_control="dropdown_menu"]' ).each( function(){
			$(this).removeClass( 'active' );
		});
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown_menu"]:not(.disabled) > .selected', function(e){
		e.stopPropagation();

		$( '[data-tify_control="dropdown_menu"]' ).not( $( this ).closest( '[data-tify_control="dropdown_menu"]' ) ).removeClass( 'active' );
		$( this ).closest( '[data-tify_control="dropdown_menu"]' ).toggleClass('active');
		
		return false;
	});	
});