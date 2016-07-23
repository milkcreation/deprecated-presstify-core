jQuery( document ).ready( function($){
	$( document ).on( 'change.tify.control.touch_time', '[data-tify_control="touch_time"] .tify_control_touch_time-handler', function( e ){
		e.preventDefault();

		var $closest = $( this ).closest( '[data-tify_control="touch_time"]' );
		var value = "";
		if( $( '.tify_control_touch_time-handler-yyyy', $closest ).size() )
			value += $( '.tify_control_touch_time-handler-yyyy', $closest ).val();
		if( $( '.tify_control_touch_time-handler-mm', $closest ).size() )	
			value += "-"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-mm', $closest ).val(), 10 ) ).slice(-2);
		if( $( '.tify_control_touch_time-handler-dd', $closest ).size() )	
			value += "-"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-dd', $closest ).val(), 10 ) ).slice(-2);
		if( $( '.tify_control_touch_time-handler-hh', $closest ).size() )
			value += " "+ ("0" + parseInt( $( '.tify_control_touch_time-handler-hh', $closest ).val(), 10 ) ).slice(-2);
		if( $( '.tify_control_touch_time-handler-ii', $closest ).size() )	
			value += ":"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-ii', $closest ).val(), 10 ) ).slice(-2);
		if( $( '.tify_control_touch_time-handler-ss', $closest ).size() )	
			value += ":"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-ss', $closest ).val(), 10 ) ).slice(-2);
		
		$( '.tify_control_touch_time-input', $closest ).val( value );
		
		$closest.trigger( 'tify_touch_time_change' );
	});
});