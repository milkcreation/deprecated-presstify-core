jQuery( document ).ready( function($){
	$( '#tifyhookarchive-term-select input[data-term_id]' ).each( function(){
		var term_id = $(this).data( 'term_id');
		if( ! term_id )
			return;
		if( $(this).prop( 'checked' ) )
		{
			$( '#tifyhookarchive-permalink-select .permalink_term-'+ term_id ).show();
		} else {
			$( '#tifyhookarchive-permalink-select .permalink_term-'+ term_id ).hide();
		}
		permalinkAutoCheck();
	});
	$( '#tifyhookarchive-term-select input[data-term_id]' ).change( function(){
		var term_id = $(this).data( 'term_id');
		if( ! term_id )
			return;
		if( $(this).prop( 'checked' ) )
		{
			$( '#tifyhookarchive-permalink-select .permalink_term-'+ term_id ).fadeIn();
		} else {
			$( '#tifyhookarchive-permalink-select .permalink_term-'+ term_id ).fadeOut();
		}
		permalinkAutoCheck();
	});
	
	function permalinkAutoCheck()
	{
		var checked = $( '#tifyhookarchive-permalink-select input[type="radio"]:checked' ).val();
		if( checked > 0 ){
			if( ! $( '#tifyhookarchive-term-select input[data-term_id="'+ checked +'"]' ).is( ':checked' ) )
				$( '#tifyhookarchive-permalink-select input[type="radio"][value="-1"]' ).prop( 'checked', true );
		}
	}
});