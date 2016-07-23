jQuery( document ).ready( function( $ ){
	/* = Sauvegarde de l'onglet courant = */
	$( 'a[data-toggle="tab"]', '.taboox-container' ).on( 'shown.bs.tab', function(e){		
    	if( $(this).data('current') )
    		$.post( tify_ajaxurl, { action : 'tify_taboox_current_tab', current : $(this).data('current') } );
	});
	/* = Affichage de l'onglet courant = */
	$( 'a[data-toggle="tab"]', '.taboox-container' ).each( function(){		
		if( ! ( $(this).closest('li').hasClass('active') ) && ! ( $(this).closest('li').siblings().hasClass('active') ) ){
			$(this).parents( 'ul' ).find( '> li:first' ).addClass( 'active' );
			$( $(this).parents( 'ul' ).find( '> li:first > a' ).attr( 'href' ) ).addClass( 'active' );
		}
		if( $(this).hasClass( 'current_tab' ) )
			$(this).tab('show');			
	});
});