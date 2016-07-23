jQuery( document ).ready( function($){
	$( 'html, body' ).click( function( ) {
		$( '[data-tify_control="dropdown"]' ).each( function(){
			$(this).removeClass( 'active' );
		});
	});

	$( document ).on( 'change.tify.control.dropdown', '[data-tify_control="dropdown"] > ul > li > label > input[type="radio"]', function(e){		
		e.preventDefault();
		e.stopPropagation();
		
		$(this).prop( 'checked', true ); 
		$closest = $( this ).closest( '[data-tify_control="dropdown"]' );
		$( 'input[type="radio"]', $closest ).not(this).each( function(){ 
			$(this).prop( 'checked', false ); 
		});		
		$( this ).closest( 'li' ).addClass('checked').siblings().removeClass('checked');
		$closest.removeClass('active').find( '.selected > b' ).html( $( this ).closest( 'label' ).find('span').text() );
			
		$( this ).trigger( 'tify_dropdown_change' );					

		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown"]:not(.disabled) > .selected', function(e){
		e.stopPropagation();

		$( '[data-tify_control="dropdown"]' ).not( $( this ).closest( '[data-tify_control="dropdown"]' ) ).removeClass( 'active' );
		$( this ).closest( '[data-tify_control="dropdown"]' ).toggleClass('active');
		
		return false;
	});
});