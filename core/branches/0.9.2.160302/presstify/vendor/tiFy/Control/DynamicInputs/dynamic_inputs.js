jQuery( document ).ready( function($){
	// Ajout d'un élément
	$( document ).on( 'click.tify.control.dynamic_inputs.add', '[data-tify_control="dynamic_inputs"] .tify_control_dynamic_inputs-add_button', function(e){
		e.stopPropagation();
		e.preventDefault();
	
		$new 	= $( $(this).prev() ).clone();
		$list 	= $( '> ul', $(this).closest( '[data-tify_control="dynamic_inputs"]') );
		var new_html = $new.html().replace( /%%index%%/g, $( '> li', $list ).length ).replace( /%%name%%/g, $(this).data( 'name' ) );
		var $el = $( '<li>'+ new_html +'<a href="#tify_control_dynamic_inputs-remove_button" class="tify_button_remove"></a></li>' );
		$list.append( $el );
		
		$el.trigger( 'tify_dynamic_inputs_added' );					
	});
	// Suppression d'un élément
	$( document ).on( 'click.tify.control.dynamic_inputs.remove', '[data-tify_control="dynamic_inputs"] > ul > li > .tify_button_remove', function(){
		$( this ).closest( 'li' ).fadeOut( function(){
			$( this ).remove();
		});
	});
});