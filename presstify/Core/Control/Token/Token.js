jQuery( document ).ready( function($){
	$( document ).on( 'click', '[data-tify_control_token="keygen"]', function(e){
		var target		= $(this).data( 'target' ),
			length 		= $(this).data( 'length' ),
			public_key	= $(this).data( 'public' ),
			private_key	= $(this).data( 'private' );
		
		$closest = $( target );
		
		$closest.addClass( 'load' );		
		$( '.tify_control_token-wrapper > input.tify_control_token-plain', target ).prop( 'disabled', 'disabled' );
		
		$.post( tify_ajaxurl, { action: 'tify_control_token_keygen', length : length, public_key: public_key, private_key: private_key }, function( resp ){
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', target ).prop( 'disabled', false );
			$closest.removeClass( 'load' );
			
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', target ).val( resp.data.plain );
			$( '.tify_control_token-wrapper > input.tify_control_token-hash', target ).val( resp.data.hash );
		});
		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control_token="unmask"]', function(e){
		var $this		= $(this),
			target		= $this.data( 'target' );			
		
		if( $this.hasClass( 'active' ) )
		{
			var mask		= $this.data( 'mask' );
			
			$this.removeClass( 'active' );
			
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', target )
				.attr( 'type', 'password' )
				.val( mask );
			
			return false;
		} else {					
			var $closest = $( target ),
				hash		= $( '.tify_control_token-wrapper > input.tify_control_token-hash', target ).val(),
				public_key	= $this.data( 'public' ),
				private_key	= $this.data( 'private' );
			
			$closest.addClass( 'load' );
			$( '.tify_control_token-wrapper > input.tify_control_token-plain', target ).prop( 'disabled', 'disabled' );
			
			$.post( tify_ajaxurl, { action: 'tify_control_token_unmask', hash : hash, public_key: public_key, private_key: private_key }, function( resp ){
				$( '.tify_control_token-wrapper > input.tify_control_token-plain', target ).prop( 'disabled', false );
				$closest.removeClass( 'load' );
				
				$this.addClass( 'active' );
				
				$( '.tify_control_token-wrapper > input.tify_control_token-plain', target )
					.attr( 'type', 'text' )
					.val( resp.data.plain );
			});
			return false;
		}
	});
});