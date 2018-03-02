jQuery( document ).ready( function($){
	$( '.tiFy_ArchiveFilter-items ul li:has(ul.children) > input[type="checkbox"]' ).change( function(e){
		if( $(this).is( ':checked' ) )
			$(this).parent().find( '> ul.children > li > input[type="checkbox"]' ).prop( 'checked', true );
		else
			$(this).parent().find( '> ul.children > li > input[type="checkbox"]' ).prop( 'checked', false );
	});
});