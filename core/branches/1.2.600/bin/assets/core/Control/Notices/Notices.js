jQuery(document).ready(function($){
	$( 'body' ).after().on( 'click', '[data-dismiss="tiFyNotice"]', function(e){
		e.preventDefault();
		
		$(this).trigger( 'tiFyControl.Notices.Click' );
	});
	$( 'body' ).on( 'tiFyControl.Notices.Click', function(e){
		$( e.target ).closest( '.tiFyNotice' ).fadeOut();
	});
});