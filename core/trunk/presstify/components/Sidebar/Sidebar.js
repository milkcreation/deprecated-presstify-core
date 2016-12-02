jQuery( document ).ready( function( $ ) {
	$( document ).on( 'click.tify_sidebar', '[data-toggle="tify_sidebar"]', function(e){
		e.preventDefault();
		var dir = $(this).data('dir');
		$( this ).toggleClass( 'active' );
		$( document.body ).toggleClass( 'tify_sidebar-'+dir+'_active' );	
	});
});