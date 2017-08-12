jQuery( document ).ready( function($){
	/** == RESPONSIVE == **/
	$(window).resize( function(){
		var w = $(window).innerWidth(), h = $(window).innerHeight();
		$( '#tify-debugbar .responsive .size .width' ).html( w );
		$( '#tify-debugbar .responsive .size .height' ).html( h );
	}).trigger( 'resize' );
	$( '#tify-debugbar .responsive > .context > li > a' ).click( function(e){
		e.preventDefault();
		
		var w = $(this).data( 'width' );
		
		if( $(this).closest( 'li' ).hasClass( 'active' ) ){
			$( '#tify-debugbar-resize .overlay' ).show();
			$( '#tify-debugbar-resize .overlay > i' ).css( 'margin-left', w+'px' );
			$( '#tify-debugbar-resize > iframe' ).attr( 'src', $( '#tify-debugbar-resize > iframe' ).attr('src') );
		} else {		
			
			var fullscreen = $(this).hasClass( 'fullscreen' ) ? true : false ;
			
			$(this).closest( 'li' ).addClass( 'active' ).siblings().removeClass('active');
			
			if( ! fullscreen ){
				$( '#tify-debugbar-resize').show();
				$('html, body' ).css({ height: '100%', overflow :'hidden'});
			}
			 
			$( '#tify-debugbar-resize > iframe' ).animate( { 'width': w }, function(){
				if( fullscreen ){
					$( '#tify-debugbar-resize').hide();
					$('html, body' ).css({ height: 'auto', overflow :'auto'});
				}
			});
		}			
	});
	$( '#tify-debugbar-resize > iframe' ).load(function(){
		$( '#tify-debugbar-resize .overlay' ).fadeOut();
    });
});