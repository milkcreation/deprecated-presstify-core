jQuery(document).ready( function($){
	var callback_attrs = {};
	$('[data-action="tify-fb-api_share_button"]').click(function(e){
		e.preventDefault();

		elem = $(this);
		var	title			= elem.data( 'title' ),
			description 	= elem.data( 'desc' ),
			url				= elem.data( 'url' ),
			image			= elem.data( 'image' );			
		callback_attrs 		= elem.data( 'callback_attrs' );
		
		tify_fb_post2feed( title, description, url, image );
		
		return false;
	});
	
	function tify_fb_post2feed_callback( response ){
		$.post( tify_ajaxurl, { action : 'tify_fb_post2feed_callback', response : response, attrs : callback_attrs }, function( resp ){ console.log( resp ); });
	};
	
	function tify_fb_post2feed( title, desc, url, image ){
		var obj = { method: 'feed', link: url, picture: image, name: title, description: desc };
		
		FB.ui( obj, tify_fb_post2feed_callback );
	};	
});