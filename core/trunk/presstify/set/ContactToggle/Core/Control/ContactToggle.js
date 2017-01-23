jQuery(document).ready(function($){
	$( '[data-tify_control="contact_toggle"]' ).click( function(e){
		e.preventDefault();
				
		var	_ajax_nonce = $( this ).data( 'nonce' ),
			query_args 	= $( this ).data( 'query_args' ),
			target 		= $( this ).data( 'target' );

		$.ajax({
			url:		tify_ajaxurl,
			data:		{ action: 'tify_control_contact_toggle', _ajax_nonce : _ajax_nonce, query_args : query_args },
			type:		'post',
			success:	function( resp )
			{
				if( resp.success )
					$( '[data-role="tiFyModal"][data-id="'+ target +'"' )
						.find( '.modal-body' ).html( resp.data ).end()
						.modal( 'show' );
			}
		});
	});
});