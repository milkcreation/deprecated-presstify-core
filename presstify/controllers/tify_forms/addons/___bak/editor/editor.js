jQuery(document).ready( function($){
	// Main Contents
	$selector = $( '#type-selector' );
	$editor = $( '#field-editor' );
	$previewer = $( '#form-preview' );	
		
	// Selector
	$( '.type-select', $selector ).click( function(e){
		e.preventDefault();
		var type =  $(this).data('type');
		$.ajax({
			url : ajaxurl,
		 	data : { action:'load_field_editor', type:type },
		 	type : 'POST',
		 	dataType: 'html',
		 	beforeSend 	: function( resp ){
		 		show_loader( $editor );
		 	},
		 	complete	: function( resp ){
		 		hide_loader( $editor );
		 	},
		 	success : function( resp ){
		 		$( '#field-edit-form', $editor ).replaceWith( resp );
		 		$( '.field-preview', $previewer ).removeClass('active');
		 	}
		});
		return false;
	});
	
	// Editor
	$(document.body).on( 'click', '.wp-tab-bar li a', function( e ){
		e.preventDefault();
		$(this).closest('li').addClass('wp-tab-active').siblings().removeClass('wp-tab-active');
		$(this).closest('.inside').find( '.wp-tab-panel').removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
		$(this).closest('.inside').find( $(this).attr('href') ).addClass('tabs-panel-active').removeClass('tabs-panel-inactive');
	});
	
	$(document.body).on( 'click', '.button-save', function(e){
		e.preventDefault();
		var data = $( 'input[name^="_mkcfield"], textarea[name^="_mkcfield"], select[name^="_mkcfield"]' ).serialize()+'&action=load_field_preview'
		var slug = $( 'input[name="_mkcfield[slug]"]').val();
		$.ajax({
			url 		: ajaxurl,
		 	data 		: data,
		 	type 		: 'POST',
		 	dataType	: 'html',
		 	beforeSend 	: function( resp ){
		 		show_loader( $previewer );
		 	},
		 	complete	: function( resp ){
		 		hide_loader(  $previewer );
		 	},
		 	success 	: function( resp ){
		 		if( $( '.inside #field-preview-'+slug, $previewer ).length )
		 			$( '.inside #field-preview-'+slug, $previewer ).replaceWith(resp);
		 		else
		 			$( '.inside', $previewer ).append( resp );
		 	}		 	
		});
		return false;
	});
		
	// Previewer	
	/// Toolbox
	//// Move 
	$( '.inside', $previewer ).sortable({
		axis		: 'y',
		handle		: '.toolbox .move',
		containment	: 'parent',
		placeholder	: 'ui-sortable-placeholder'
		/*stop	: function( event, ui ){
			$( '.field-order' ).each( function( u, i ){
				$(this).val( $(this).closest('.field-wrapper').index()+1 );
			})
		}*/
	});
	//// Edit 
	$(document.body).on( 'click', '.toolbox .edit', function(e){
		e.preventDefault();
		
		$(this).parents('.inside').find( '.field-preview' ).removeClass('active');
		
		$container = $(this).closest('.field-preview');
		$container.addClass( 'active');	
							
		var slug = $(this).data('slug');
		var prefix = $(this).data('prefix');
		var data = $( 'input[name^="'+prefix+'"], textarea[name^="'+prefix+'"], select[name^="'+prefix+'"]', $container ).serialize()+'&action=load_field_editor&slug='+slug+'&prefix='+prefix;
	
		$.ajax({
			url 		: ajaxurl,
		 	data 		: data,
		 	type 		: 'POST',
		 	dataType	: 'html',
		 	beforeSend 	: function( resp ){
		 		show_loader( $editor );
		 	},
		 	complete	: function( resp ){
		 		hide_loader( $editor );
		 	},
		 	success 	: function( resp ){
		 		$( '#field-edit-form', $editor ).replaceWith( resp );
		 	}		 	
		});
		return false;
	});
	//// Delete 
	$(document.body).on( 'click', '.toolbox .delete', function(e){
		e.preventDefault();
		$(this).closest( '.field-preview').fadeOut( function(){
			$(this).remove();
		});
	});
	
	// Global
	/// Inputs clone
	$(document.body).on( 'click', '.addinput', function(){
		$newoption = $(this).parent().clone();
		$('input[type="text"]', $newoption ).val('');
		$(this).parent().after( $newoption );			
	});
	/// Inputs remove
	$(document.body).on( 'click', '.delinput', function(){
		$(this).parent().fadeOut();
	});
	/// Preloader Show
	function show_loader( $container ){
		$( '.overlay, .spinner', $container ).show();
	}
	/// Preloader Hide
	function hide_loader( $container ){
		$( '.overlay, .spinner', $container ).fadeOut();
	}	
});