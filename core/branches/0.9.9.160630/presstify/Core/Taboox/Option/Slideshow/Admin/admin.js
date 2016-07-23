var tify_taboox_slideshow_media_frame;

jQuery(document).ready( function($){
	// 
	var editoroptions_normal = {
	    inline: 	true,
	    toolbar: 	"bold italic",
	    menubar:	false
	};
	
	// 
	var initItem = function( $item ){
		$( '.tinymce-editor', $item ).tinymce( editoroptions_normal );
		
		$( '[data-hide_unchecked]', $item ).not( ':checked' ).each( function(){
			var target = $(this).data( 'hide_unchecked' );
			$(this).closest( '.input-fields' ).find( target ).each( function(){
				$(this).hide();
			});
		});
	};
	
	// 
	var getItem = function( target, post_id ){
		var $target		= $( target ),
			$container	= $target.closest( '.tify_taboox_slideshow' );
	
		var count = $( '.items > ul > li', $container ).size();
		
		if( ( tiFyTabooxOptionSlideshowAdmin.max > 0 ) && ( count == tiFyTabooxOptionSlideshowAdmin.max ) ){
			alert( tiFyTabooxOptionSlideshowAdmin.l10nMax );
			return false;
		}		
		
		$.ajax({
			url 		: tify_ajaxurl,
			data 		: { action : 'tify_taboox_slideshow_item', post_id : post_id, order : parseInt( count +1 ) },
			dataType 	: 'html',
			type 		: 'post',
			beforeSend : function(){
				$( '.items > .overlay', $container ).show();
			},
			complete	: function( resp ){
				$( '.items > .overlay', $container ).hide();
			},
			success		: function( resp ){
				$( '.items > ul', $container ).prepend( resp );
				var $item = $( '.items > ul > li:eq(0)', $container );
				initItem( $item );
				orderItem( $container );
				$( document ).trigger( 'tify_taboox_slideshow_item_load', $item );
			}
		});		
		return false;
	};
	
	// Mise à jour de l'ordre des items
	var orderItem = function( $container ){
		$( '.items > ul > li', $container ).each( function(){
			$(this).find( '.order-value').val( parseInt( $(this).index()+1 ) );
		});
	};
	
	$( '.tify_taboox_slideshow > .items > ul > li' ).each( function(){
		initItem( $(this) );
	});
	
	// 
	$( document ).on( 'change', '.tify_taboox_slideshow > .items > ul > li > .input-fields [data-hide_unchecked]', function(e){
		var target = $(this).data( 'hide_unchecked' );
		if( $(this).is(':checked' ) ){
			$(this).closest( '.input-fields' ).find( target ).each( function(){
				$(this).show();
			});
		} else {
			$(this).closest( '.input-fields' ).find( target ).each( function(){
				$(this).hide();
			});
		}
	});
	
	// Autocomplete
	/// Modification de l'autocomplete pour éviter les doublons		
	$( '.tify_taboox_slideshow > .selectors > .suggest > .tify_taboox_slideshow-suggest[data-duplicate=""] > .ui-autocomplete-input' ).on( "autocompletesearch", function( e, ui ) {
		var $input		= $( e.target ),
			$container	= $input.closest( '.tify_taboox_slideshow' ),
			$suggest 	= $input.closest( '.tify_control_suggest' );
		
		var action	 		= $suggest.data( 'tify_control_suggest' ),
			query_args		= $suggest.data( 'query_args' ),
			elements		= $suggest.data( 'elements' ),
			extras			= $suggest.data( 'extras' ),
			post__not_in 	= [];
		
		$( '.items > ul > li > .input-fields .post_id', $container ).each( function(){
			post__not_in.push( $( this ).val() );
		});
		
		if( post__not_in )
			query_args = $.extend( query_args, {post__not_in : post__not_in } );
				
		$( e.target ).autocomplete( 'option', 'source', function( request, response ){
			$.post(	tify_ajaxurl, { action : action, term : request.term,  query_args : query_args, elements : elements, extras : extras }, function( data ){
				( ! data.length ) ? response({ 'label':'X', 'value':'Y', 'render':'' }) : response(data);
			}, 'json' );
		});
		
	});
	
	/// Modification de la selection de l'autocomplete
	$( '.tify_taboox_slideshow > .selectors > .suggest > .tify_taboox_slideshow-suggest > .ui-autocomplete-input' ).on( "autocompleteselect", function( e, ui ) {
		e.preventDefault;
		
		getItem( e.target, ui.item.id );
	});
		
	// Bouton d'ajout d'un contenu du site à la liste
	$( '#add-slideshow_post, #add-custom_link' ).click( function(e){
		e.preventDefault();
		
		getItem( e.target, 0 );
	});
	
	// Suppression d'un élément de la liste
	$( document ).on( 'click', '.tify_taboox_slideshow > .items > ul > li .remove', function(e){
		e.preventDefault();
		var $container = $(this).closest( 'li' );
		
		$container.fadeOut( function(){
			$container.remove();
			orderItem();
		});
	});
	
	// Trie
	$( '.tify_taboox_slideshow > .items > ul' ).sortable({
		axis: "y",
		update : function( event, ui ){
			var container = $(this).closest( '.tify_taboox_slideshow' );
			orderItem( container );
		},
		handle: ".tify_handle_sort"
	});
	
	// Selection de l'image représentative
	$( document ).on( 'click', '.tify_taboox_slideshow > .items > ul > li .image-select', function( e ){
	 	e.preventDefault();
		
	 	var $container = $(this);
	 	
		var index 	= $container.data( 'index' ),
			name	= $container.data( 'name' );
		
		tify_taboox_slideshow_media_frame = wp.media.frames.file_frame = wp.media({
			title: 		$container.data( 'uploader_title' ),
			editing:    true,
			multiple: 	false
		});
		 
		tify_taboox_slideshow_media_frame.on( 'select', function() {
			attachment = tify_taboox_slideshow_media_frame.state().get('selection').first().toJSON();
			$container.html( '<img src="'+ attachment.sizes['thumbnail'].url +'" /><input type="hidden" name="'+ name +'['+ index +'][attachment_id]" value="'+ attachment.id +'" />' );
		});
		
		tify_taboox_slideshow_media_frame.open();
	});
});