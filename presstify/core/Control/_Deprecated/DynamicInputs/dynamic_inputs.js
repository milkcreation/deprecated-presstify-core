
jQuery( document ).ready( function($){
	// Ajout d'un élément
	$( document ).on( 'click.tify.control.dynamic_inputs.add', '[data-tify_control="dynamic_inputs"] .tify_control_dynamic_inputs-add_button', function(e){
		e.stopPropagation();
		e.preventDefault();
		
		var $closest 	= $( this ).closest( '[data-tify_control="dynamic_inputs"]' ),
			$list 		= $( '> ul', $closest );
		var count		= $( '> li', $list ).length,
			max			= $( '.dynamic_inputs-max', $closest ).val();

		if( ( max > 0 ) && ( count >= max ) ) {
			alert( tyctrl_dinputs.MaxAttempt );
			return false;
		}
		
		$new 	= $( $(this).prev() ).clone();
		index = getUniqIndex( $list );
		var new_html = $new.html().replace( /%%index%%/g, index ).replace( /%%name%%/g, $(this).data( 'name' ) );
		var $el = $( '<li data-index="'+index+'">'+ new_html +'<a href="#tify_control_dynamic_inputs-remove_button" class="tify_button_remove"></a></li>' );
		$list.append( $el );
		
		$el.trigger( 'tify_dynamic_inputs_added' );					
	});
	// Suppression d'un élément
	$( document ).on( 'click.tify.control.dynamic_inputs.remove', '[data-tify_control="dynamic_inputs"] > ul > li > .tify_button_remove', function(){
		$( this ).closest( 'li' ).fadeOut( function(){
			$( this ).remove();
		});
	});
	// Obtention d'un index unique
	function getUniqIndex( $list ) {
		index = $( '> li', $list ).length;
		$( '> li', $list ).each( function() {
			if( $( this ).data( 'index' ) === index )
				index++;
		});
		return index;
	}
});