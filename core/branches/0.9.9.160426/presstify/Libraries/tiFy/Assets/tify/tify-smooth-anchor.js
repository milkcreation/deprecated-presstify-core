jQuery(document).ready( function($){
	$(document).on( 'click', "[data-smooth-anchor]", function(e){
		e.preventDefault();

		var full_url = $( this ).attr( 'href' );

		var parts = full_url.split("#");
		var trgt = parts[1];

		var target_offset = $("#"+trgt).offset();
		var addOffset = -30;
		if( $(this).data( 'additionnal-offset' ) )
			addOffset = $(this).data( 'additionnal-offset' );
			
		var target_top = target_offset.top+addOffset;

		$('html, body').animate({scrollTop:target_top}, 1500, 'easeInOutExpo');
		
		return false;
	});
});