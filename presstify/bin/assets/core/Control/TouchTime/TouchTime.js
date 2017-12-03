jQuery( document ).ready( function($){
	$( document ).on( 'change.tify.control.touch_time', '[data-tify_control="touch_time"] .tify_control_touch_time-handler', function( e ){
		e.preventDefault();

		var $closest = $( this ).closest( '[data-tify_control="touch_time"]' );
		var value = "", dateFormat = "";
		if( $( '.tify_control_touch_time-handler-yyyy', $closest ).length ){
			value += $( '.tify_control_touch_time-handler-yyyy', $closest ).val();
			dateFormat += "YYYY";
		}
		if( $( '.tify_control_touch_time-handler-mm', $closest ).length ){
			value += "-"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-mm', $closest ).val(), 10 ) ).slice(-2);
			if( dateFormat )
				dateFormat += "-";
			dateFormat += "MM";
		}
		if( $( '.tify_control_touch_time-handler-dd', $closest ).length ){	
			value += "-"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-dd', $closest ).val(), 10 ) ).slice(-2);
			if( dateFormat )
				dateFormat += "-";
			dateFormat += "DD";
		}
		if( $( '.tify_control_touch_time-handler-hh', $closest ).length ){
			value += " "+ ("0" + parseInt( $( '.tify_control_touch_time-handler-hh', $closest ).val(), 10 ) ).slice(-2);
			if( dateFormat )
				dateFormat += " ";	
			dateFormat += "hh";
		}
		if( $( '.tify_control_touch_time-handler-ii', $closest ).length ){	
			value += ":"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-ii', $closest ).val(), 10 ) ).slice(-2);
			if( dateFormat )
				dateFormat += ":";
			dateFormat += "mm";
		}
		if( $( '.tify_control_touch_time-handler-ss', $closest ).length ){	
			value += ":"+ ("0" + parseInt( $( '.tify_control_touch_time-handler-ss', $closest ).val(), 10 ) ).slice(-2);
			if( dateFormat )
				dateFormat += ":";
			dateFormat += "ss";
		}
			
		// Test d'intégrité
		if( moment( value, dateFormat, true).isValid() ){
			$closest.removeClass( 'invalid' );
		} else {			
			$closest.addClass( 'invalid' );
		}
		
		$( '.tify_control_touch_time-input', $closest ).val( value );
		
		$closest.trigger( 'tify_touch_time_change' );
	});
});