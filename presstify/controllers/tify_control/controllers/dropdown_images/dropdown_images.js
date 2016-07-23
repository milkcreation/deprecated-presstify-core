jQuery( document ).ready( function($){
	$( 'html, body' ).click( function( ) {
		$( '[data-tify_control="dropdown_images"]' ).each( function(){
			$(this).removeClass( 'active' );
		});
	});

	$( document ).on( 'change', '[data-tify_control="dropdown_images"] > ul > li > ul > li > label > input[type="radio"]', function(e){		
		e.preventDefault();
		e.stopPropagation();
		
		$parent = $( this ).closest( '[data-tify_control="dropdown_images"]' );
		$( 'li.checked', $parent ).each( function(){ $(this).removeClass('checked'); });
		$( this ).closest( 'li' ).addClass('checked');
		$clone = $( this ).closest( 'label' ).find('img').clone();
		$parent.removeClass('active').find( '.selected > b').html( $clone );
			
		$( this ).trigger( 'tify_dropdown_images_change' );					

		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown_images"]:not(.disabled) > .selected', function(e){
		e.stopPropagation();

		$( '[data-tify_control="dropdown_images"]' ).not( $( this ).closest( '[data-tify_control="dropdown_images"]' ) ).removeClass( 'active' );
		$( this ).closest( '[data-tify_control="dropdown_images"]' ).toggleClass('active');
		
		return false;
	});
});