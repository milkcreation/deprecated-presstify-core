jQuery(document).ready( function($){
	$( "#tiFy_CookieLaw-accept" ).click( function(e){
		e.preventDefault();
		$.post( tify_ajaxurl, { action : 'tiFy_CookieLaw' }, function(resp){ });
		$( "#tiFy_CookieLaw" ).fadeOut( 500 );	
	});
	$( "#tiFy_CookieLaw-close" ).click( function(e){
		$( "#tiFy_CookieLaw" ).fadeOut( 500 );
	});
});