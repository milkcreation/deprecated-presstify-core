jQuery( document ).ready( function($){
	$( 'html, body' ).click( function( ) {
		$( '[data-tify_control="dropdown_colors"]' ).each( function(){
			$(this).removeClass( 'active' );
		});
	});

	$( document ).on( 'change', '[data-tify_control="dropdown_colors"] > ul > li > label > input[type="radio"]', function(e){		
		e.preventDefault();
		e.stopPropagation();
		
		$closest = $( this ).closest( '[data-tify_control="dropdown_colors"]' );
		$( 'input[type="radio"]', $closest ).not(this).each( function(){ $(this).prop( 'checked', false ); });		
		$( this ).closest( 'li' ).addClass('checked').siblings().removeClass('checked');
		$closest.removeClass('active').find( '.selected > b').html( $( this ).closest( 'label' ).find('.value').html() );
			
		$( this ).trigger( 'tify_dropdown_change' );					

		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown_colors"]:not(.disabled) > .selected', function(e){
		e.stopPropagation();

		$( '[data-tify_control="dropdown_colors"]' ).not( $( this ).closest( '[data-tify_control="dropdown_colors"]' ) ).removeClass( 'active' );
		$( this ).closest( '[data-tify_control="dropdown_colors"]' ).toggleClass('active');
		
		return false;
	});
});