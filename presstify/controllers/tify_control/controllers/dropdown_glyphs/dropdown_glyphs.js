jQuery( document ).ready( function($){
	$( 'html, body' ).click( function( ) {
		$( '.tify_dropdown_glyphs' ).each( function(){
			$(this).removeClass( 'active' );
		});
	});

	$( document ).on( 'change', '.tify_dropdown_glyphs > ul > li > ul > li > label > input[type="radio"]', function(e){		
		e.preventDefault();
		e.stopPropagation();
		
		$parent = $( this ).closest( '.tify_dropdown_glyphs' );
		$( 'li.checked', $parent ).each( function(){ $(this).removeClass('checked'); });
		$( 'input[type="radio"]', $parent ).not(this).each( function(){ $(this).prop( 'checked', false ); });
		$( this ).closest( 'li' ).addClass('checked');
		$clone = $( this ).closest( 'label' ).find('span').clone();
		$parent.removeClass('active').find( '.selected > b').html( $clone );
			
		$( this ).trigger( 'tify_dropdown_glyphs_change' );					

		return false;
	});
	
	$( document ).on( 'click', '.tify_dropdown_glyphs:not(.disabled) > .selected', function(e){
		e.stopPropagation();

		$( '.tify_dropdown_glyphs' ).not( $( this ).closest( '.tify_dropdown_glyphs' ) ).removeClass( 'active' );
		$( this ).closest( '.tify_dropdown_glyphs' ).toggleClass('active');
		
		return false;
	});
});