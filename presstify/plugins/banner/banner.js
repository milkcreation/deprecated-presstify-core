jQuery( document ).ready( function($){
	$( 'a[data-tify_banner="close"]').click( function(e){
		e.stopPropagation();
		e.preventDefault();

		$closest = $(this).closest( '.tify_banner' );
		$this = $(this);
		var name = $closest.data( 'cookie_name' ), expire = $closest.data( 'cookie_expire' );
		$.post( tify_ajaxurl, { action: 'tify_banner_set_cookie', name : name, expire : expire  }, function( resp ) { $this.trigger( 'tify_banner_close' ); window.location = $this.attr( 'href' ); } );	
	});	
});