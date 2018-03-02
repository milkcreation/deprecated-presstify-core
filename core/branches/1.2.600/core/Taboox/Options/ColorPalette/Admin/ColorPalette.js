/**
Dependencies: jquery, jquery-ui-sortable, tify_controls-colorpicker
*/
jQuery(document).ready(function($){
	$(document).on('click', '.tify_taboox-color_palette .tify_theme_color-add', function( e ){
		e.preventDefault();
		var $container 	= $(this).closest( '.tify_taboox-color_palette' );
		var index		= $( '> ul > li', $container ).length;
		var name		= $container.data( 'name' );
		$.post( tify_ajaxurl, { action : 'tify_taboox_color_palette', name : name, index : index }, function(resp){
			$( '> ul', $container ).append( resp );
			$( document ).trigger( 'tify_control.colorpicker.init', $( '> ul > li:last', $container ).find( '.tify_colorpicker > input[name="'+ name +'[colors]['+ index +'][hex]"]' ) );
		});
	});
	$(document).on('click', '.tify_taboox-color_palette > ul > li > .delete', function( e ){
		e.preventDefault();
		$(this).closest( 'li' ).fadeOut( function(){
			$(this).remove();
		});
	});
	// Ordonnacement des fichiers
	$( ".tify_taboox-color_palette > ul" ).sortable({
		placeholder : "ui-sortable-placeholder",
		handle		: ".handle"
	});
});