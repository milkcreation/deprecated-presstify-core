jQuery(document).ready( function($){
	/* = Connection à partir de Facebook = */
	$( 'a[data-tify_facebook_sdk_login]' ).click( function(e){
		e.preventDefault();
		
		var permissions = $(this).attr( 'scope' );
		$(this).addClass( 'process' );
		tify_facebook_login( permissions, $(this) );
		
		return false;
	});
});

function tify_facebook_login( permissions, $link ){
	if( typeof FB === "undefined" )
		return;
    FB.login( function( response ) {											
		if( response.authResponse ){
            FB.api( '/me?fields=email', function( response ){
            	$( '#fb-connect_errors').fadeOut();
				$.post( tify_ajaxurl, { action : 'tify_facebook_sdk_login', response : response }, function( resp ){
					if( ! resp.success ){
						$( $link.data('error') ).html( resp.data );
						$link	.addClass( 'error' )
            					.removeClass( 'process' );
					} else {
						location.reload();
						$link	.unbind( 'click' )
            					.addClass( 'complete' )
            					.removeClass( 'process' );
					}	
							
				}, 'json');
            });									
        } else {
            $link	.append( 'Votre demande d\'authentification a été rejeté par Facebook' )
            		.removeClass( 'process' );											
        }
    }, { scope: permissions });
}