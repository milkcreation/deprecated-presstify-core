jQuery( document ).ready( function($){	
	/**
	 * BASCULE D'AFFICHAGE
	 */	
	$( document ).on( 'click', '[data-tify_control_crypted_data="toggle-mask"]', function(e){
		e.preventDefault();
		
		var $this		= $(this); 
			$closest 	= $this.closest( '[data-tify_control="crypted_data"]' ),
			$input		= $( '.tiFyControlCryptedData-input', $closest );
		
		if( $closest.hasClass( 'masked' ) )
		{
			$closest.addClass( 'load' );
			$input.prop( 'disabled', true );
			data = JSON.parse( decodeURIComponent( $closest.data( 'transport' ) ) );

			$.post( 
				tify_ajaxurl, 
				{ 
					action : 		'tiFyControlCryptedData_decrypt', 
					value : 		$( '.tiFyControlCryptedData-cypher', $closest ).val(),
					decrypt_cb :	$closest.data( 'decrypt_cb' ),
					data :			JSON.parse( decodeURIComponent( $closest.data( 'transport' ) ) )
				}, 
				function( resp ){
					$input
						.val( resp.data )
						.prop( 'disabled', false )
						.attr( 'type', 'text' );
					$closest.removeClass( 'masked load' );
			});
		} else {
			$input
				.val( $closest.data( 'mask' ) )
				.prop( 'disabled', false )
				.attr( 'type', 'password' );
				
			$closest.addClass( 'masked' );
		}
	});	
	
	/**
	 * SAISIE
	 */
	var xhr = undefined;
	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	
	$( document ).on( 'keyup change', '[data-tify_control_crypted_data="input"]', function(e){
		e.preventDefault();
		
		if( xhr !== undefined )
			xhr.abort();
		
		var $closest 	= $(this).closest( '[data-tify_control="crypted_data"]' )
			value		= $(this).val();
		
		$closest.addClass( 'load' );
		delay( function(){
			xhr = $.post( 
				tify_ajaxurl, 
				{ 
					action: 		'tiFyControlCryptedData_encrypt',
					value : 		value,
					encrypt_cb :	$closest.data( 'encrypt_cb' ),
					data :			JSON.parse( decodeURIComponent( $closest.data( 'transport' ) ) )
				}, 
				function( resp ){
					$closest.removeClass( 'load' );
					$( '.tiFyControlCryptedData-cypher', $closest ).val( resp.data );
			});
		}, 300 );		
		
		return false;
	});
	
	/**
	 * GENERATEUR
	 */
	$( document ).on( 'click', '[data-tify_control_crypted_data="generate"]', function(e){
		e.preventDefault();
		
		var $closest 	= $(this).closest( '[data-tify_control="crypted_data"]' ),
			$input		= $( '.tiFyControlCryptedData-input', $closest );
		
		$closest.addClass( 'load' );
		$input.prop( 'disabled', true );

		$.post( 
			tify_ajaxurl, 
			{ 
				action : 		'tiFyControlCryptedData_generate',
				generate_cb :	$closest.data( 'generate_cb' ),
				data :			JSON.parse( decodeURIComponent( $closest.data( 'transport' ) ) )
			}, 
			function( resp ){
				$input
					.val( resp.data )
					.prop( 'disabled', false )
					.attr( 'type', 'text' );
				$closest.removeClass( 'masked load' );

				$input.trigger( 'keyup' );
		});		
	});
});
