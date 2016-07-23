jQuery( document ).ready( function($){
	$(document).on( 'click', '.tify_calendar a[data-toggle_date]', function(event){
		event.preventDefault();
		var $closest 	= $(this).closest( '.tify_calendar' ),
			action 		= $closest.data( 'action' ),
			date		= $(this).data( 'toggle_date' );
			
		$closest.addClass( 'load' );
		$.post( tify_ajaxurl, { action : 'tify_calendar_'+ action, date : date }, function( resp ){			
			$closest.replaceWith( resp );
		});
	});
});
