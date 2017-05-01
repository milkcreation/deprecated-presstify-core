jQuery( document ).ready( function($){
	$( document ).on( 'click', '[data-tify_control_token="keygen"]', function(e){
		var $closest	= $(this).closest( '[data-tify_control="token"]' ),
			length 		= $closest.data( 'length' ),
			public_key	= $closest.data( 'public' ),
			private_key	= $closest.data( 'private' );
				
		$closest.addClass( 'load' );		
		$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest ).prop( 'disabled', 'disabled' );
		
		$.post( tify_ajaxurl, { action: 'tify_control_token_keygen', length : length, public_key: public_key, private_key: private_key }, function( resp ){
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest ).prop( 'disabled', false );
			$closest.removeClass( 'load' );
			
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest ).val( resp.data.plain );
			$( '.tify_control_token-wrapper > input.tify_control_token-hash', $closest ).val( resp.data.hash );
		});
		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control_token="unmask"]', function(e){
		var $this		= $(this); 
			$closest 	= $this.closest( '[data-tify_control="token"]' )		
		
		if( $this.hasClass( 'active' ) )
		{
			var mask		= $closest.data( 'mask' );
			
			$this.removeClass( 'active' );
			
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest )
				.attr( 'type', 'password' )
				.val( mask );
			
			return false;
		} else {					
			var hash		= $( '.tify_control_token-wrapper > input.tify_control_token-hash', $closest ).val(),
				public_key	= $closest.data( 'public' ),
				private_key	= $closest.data( 'private' );
			
			$closest.addClass( 'load' );
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest ).prop( 'disabled', 'disabled' );
			
			$.post( tify_ajaxurl, { action: 'tify_control_token_unmask', hash : hash, public_key: public_key, private_key: private_key }, function( resp ){
				$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest ).prop( 'disabled', false );
				$closest.removeClass( 'load' );
				
				$this.addClass( 'active' );
				
				$( '.tify_control_token-wrapper > input.tify_control_token-plain', $closest )
					.attr( 'type', 'text' )
					.val( resp.data.plain );
			});
			return false;
		}
	});

	var xhr = undefined;
	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	
	$( document ).on( 'keyup', '[data-tify_control_token="input"]', function(e){
		if( xhr !== undefined )
			xhr.abort();
		
		var $closest 	= $(this).closest( '[data-tify_control="token"]' )
			plain		= $(this).val(),
			public_key	= $closest.data( 'public' ),
			private_key	= $closest.data( 'private' );
		
		$closest.addClass( 'load' );
		delay( function(){
			xhr = $.post( tify_ajaxurl, { action: 'tify_control_token_encrypt', plain : plain, public_key: public_key, private_key: private_key }, function( resp ){
				$closest.removeClass( 'load' );
				$( '.tify_control_token-hash', $closest ).val( resp.data.hash );
			});
		}, 300 );		
		
		return false;
	});
});
