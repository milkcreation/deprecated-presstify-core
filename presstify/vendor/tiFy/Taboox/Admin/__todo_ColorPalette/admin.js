/**
Dependencies: jquery, jquery-ui-sortable, tify_controls-colorpicker
*/
jQuery(document).ready(function($){
	$(document).on('click', '.tify_color_palette_taboox .tify_theme_color-add', function( e ){
		e.preventDefault();
		var $container 	= $(this).closest( '.tify_color_palette_taboox' );
		var index		= $( '> ul > li', $container ).size();
		var name		= $container.data( 'name' );
		$.post( tify_ajaxurl, { action : 'tify_color_palette_taboox_add_item', name : name, index : index }, function(resp){
			$( '> ul', $container ).append( resp );
			$( document ).trigger( "tify_controls.colorpicker.init", $( '.tify_colorpicker > input[name="'+ name +'['+ index +'][hex]"]' ) );
		});
	});
	$(document).on('click', '.tify_color_palette_taboox > ul > li > .delete', function( e ){
		e.preventDefault();
		$(this).closest( 'li' ).fadeOut( function(){
			$(this).remove();
		});
	});
	// Ordonnacement des fichiers
	$( ".tify_color_palette_taboox > ul" ).sortable({
		placeholder : "ui-sortable-placeholder",
		handle		: ".handle"
	});
});