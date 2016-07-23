jQuery(document).ready( function($){
	$( '#tiFy_CustomFields_PostType_Permalink input[type="checkbox"]' ).change( function(e){
		$(this).closest( '#tiFy_CustomFields_PostType_Permalink' ).find( '#tiFy_CustomFields_PostType_Permalink-dropdown' ).toggle();
	});
});