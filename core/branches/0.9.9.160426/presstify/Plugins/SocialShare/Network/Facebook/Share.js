jQuery(document).ready( function($){
	// Partage de la page courante
	$( '[data-action="tify-fb-api_share_button"]' ).click( function(e){
		e.preventDefault();
		
		FB.ui({ method: 'share', href: window.location.href });
		
		return false;
	});
});