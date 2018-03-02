jQuery(document).ready(function($){
	$( '[data-role="tiFyModal"][data-type="video"]' ).on( 'show.bs.modal', function(e){
		var $modal = $(this),
			attr = $modal.data( 'video' );
		
		if( attr.poster )
			$( '.modal-body', $modal ).html( '<img src="'+ attr.poster +'" class="img-responsive"/>' );		

		$.post( 
			tify_ajaxurl, 
			{ action : 'tiFyVideoGetEmbed', 'attr' : attr }, 
			function( resp ){
				$( '.modal-body', $modal ).load( attr.src, function(){
					$(this).html( resp );
				});				
			}
		);	
	});
	$( '[data-role="tiFyModal"][data-type="video"]' ).on( 'hidden.bs.modal', function(e){
		$( '.modal-body', $( this ) ).empty();
	});
});
