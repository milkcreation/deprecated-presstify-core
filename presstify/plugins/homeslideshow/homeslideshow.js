var slide_file_frame;
var tinyMCE;

jQuery(document).ready( function($){
	var $container = $('#slideshow_post-list');
	
	var editoroptions_normal = {
	    inline: true,
	    toolbar: "bold italic",
	    menubar:false
	};
	$('.editable').tinymce(editoroptions_normal);
	// Autocomplétion du champs de recherche
	var post_type = $( '#search-slideshow_post' ).data('post_type') || 'any';

	$( '#search-slideshow_post' ).autocomplete({
		source:		tify_ajaxurl + '?action=tify_autocomplete&post_type='+post_type,
		delay:		500,
		minLength:	2,
		position:	( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open:		function() { $(this).addClass('open'); },
		close:		function() { $(this).removeClass('open'); },
		select:		function(u,v){ 
			$('#add-slideshow_post').data( 'post_id', ''+v.item.id ).removeClass('button-primary-disabled');
		}
	});
	if( $( '#search-slideshow_post' ).size() )
		$( '#search-slideshow_post' ).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
				return $( "<li>" )
				.append( "<a style=\"display:block; min-height:50px;\">" + item.ico + "" + item.label + "<br><em style=\"font-size:0.8em;\"><strong>" + item.type + "</strong></em></a>" )
				.appendTo( ul );
		};
	// Bouton d'ajout d'un contenu du site à la liste
	$( '#add-slideshow_post, #add-custom_link' ).click( function(e){
		e.preventDefault();
	
		var button = $(this);
		var post_id = button.data( 'post_id' ) || 0,
			order = parseInt( $('#list-slideshow_post li').length +1 ),
			items = $( '#list-slideshow_post > li' ).size();
		
		if( items == tify.max ){
			alert( tify.l10nMax );
			return false;
		}
		
		$.ajax({
			url 		: tify_ajaxurl,
			data 		: { action:'mk_home_slideshow_get_item_html', post_id : post_id, order : order },
			dataType 	: 'html',
			type 		: 'post',
			beforeSend : function(){
				$('#slideshow_post-list .overlay').show();
				$container.find('.editable').each(function(){
			       $(this).tinymce().remove();
			    });
			},
			complete	: function(){
				$('#slideshow_post-list .overlay').hide();
				 $container.find('.editable').each(function(){
					$(this).tinymce(editoroptions_normal);
			    });
			},
			success		: function( resp ){	
				$('#list-slideshow_post').prepend( resp );
				if( 'post_id' ){
					button.data( 'post_id', '' ).addClass( 'button-primary-disabled' );
					$( '#search-slideshow_post' ).val('');
				}
				slideshow_item_reorder();
			}
		});		
		return false;
	});
	/* Suppression d'un élément de la liste */
	$( document ).on( 'click', '#list-slideshow_post li .remove', function(e){
		e.preventDefault();
		$container = $(this).closest( 'li' );
		$container.fadeOut( function(){
			$container.remove();
			slideshow_item_reorder();
		});
	});
	$( '#list-slideshow_post').sortable({
		axis: "y",
		update : function( event, ui ){
			slideshow_item_reorder();
		},
		handle: ".handle",
		start: function(e, ui){
		    $(this).find('.editable').each(function(){
		       $(this).tinymce().remove();
		    });
		},
		stop: function(e,ui) {
		    $(this).find('.editable').each(function(){
		        $(this).tinymce(editoroptions_normal);
		    });
		},
		refresh : function(){
	
		}
	});
	// Mise à jour de l'ordre des items
	function slideshow_item_reorder(){
		$( '#list-slideshow_post li' ).each( function(){
			$(this).find( '.order-value').val( parseInt( $(this).index()+1 ) );
		});
	}
	slideshow_item_reorder();
	
	// Affichage du selecteur de jaquette
	$(document).on('click', '.add-slideshow-image', function( e ){
	 	e.preventDefault();
		
		var index = $( this ).data( 'index' );
		slide_file_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			editing:    true,
			multiple: false
		});
		 
		slide_file_frame.on( 'select', function() {
			attachment = slide_file_frame.state().get('selection').first().toJSON();
			$( '#slideshow-image-'+index ).html('<img src="'+attachment.sizes['thumbnail'].url+'" /><input type="hidden" name="mk_home_slideshow_items['+index+'][attachment_id]" value="'+attachment.id+'" />');
		});	
		slide_file_frame.open();
	});
	
	

});