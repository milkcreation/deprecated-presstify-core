jQuery(document).ready( function($){
	/* = METABOXES DE SAUVEGARDE FIXÉE = */
	if( $( '#submitdiv' ).length ){
		var submitDivPosTop = $( '#submitdiv' ).position().top + $( '#submitdiv' ).outerHeight();
		$( window ).scroll( function(){
			if( $( window ).scrollTop() > submitDivPosTop )
				$( '#submitdiv' ).css( { 'position' : 'fixed', 'z-index' : 99, 'width': $( '#side-sortables' ).width(), 'top' : '60px' });
			else
				$( '#submitdiv' ).css( { 'position' : 'relative', 'z-index' : 'auto', width: 'auto', 'top' : 'auto' });			
		});
	}
});