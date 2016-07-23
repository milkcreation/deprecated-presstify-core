jQuery(document).ready( function($){
	$( "#tify_cookie_policy-accept" ).click( function( e ){
		e.preventDefault();
		$.post( tify_ajaxurl, { action : 'tify_cookie_policy_set_cookie' } );
		$( "#tify_cookie_policy" ).fadeOut( 500 );	
	});
	$( "#tify_cookie_policy-close" ).click( function( e ){
		$( "#tify_cookie_policy" ).fadeOut( 500 );
	});
});