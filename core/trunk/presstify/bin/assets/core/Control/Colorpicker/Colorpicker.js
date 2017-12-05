jQuery( document ).ready( function( $ ){	
	$( document ).on( 'tify_control.colorpicker.init', function( event, obj ){		
		$( obj ).spectrum( $( obj ).data('options') );
	});
	$( '.tify_colorpicker > input' ).each( function(){
		$( document ).trigger( 'tify_control.colorpicker.init', $( this ) );
	});
});