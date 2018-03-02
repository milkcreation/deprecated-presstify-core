jQuery(document).ready( function($){
	/* = Affichage/Masquage de l'interface = */
	$(document).on( 'change', '#tiFyComponentsCustomFieldsPostTypePermalink-active', function(e){
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-selectors' ).toggle();
	});
	
	/* = Liste de selection d'éléments prédéfinis = */
	$(document).on( 'tify_control.dropdown.change', '#tiFyComponentsCustomFieldsPostTypePermalink-dropdown', function( e, value ){
		e.preventDefault();
		
		var $this = $(this);
		
		$( '#sample-permalink' ).css('opacity', '0.5' );
		$.post( tify_ajaxurl, { action: 'tiFyComponentsCustomFieldsPostTypePermalink', key: value }, function( data ){
			setData( data );
			$( $this.data('handler') ).val('').change();		
		});
		
		return false;
	});
	
	/* = Champ de recherche par autocomplétion = */
	$(document).on( 'autocompleteselect', '#tiFyComponentsCustomFieldsPostTypePermalink-suggest', function( e, ui ){
		e.preventDefault();
		
		var $this = $(this);
		
		$( '#sample-permalink' ).css('opacity', '0.5' );
		$.post( tify_ajaxurl, { action: 'tiFyComponentsCustomFieldsPostTypePermalink', post_id: ui.item.id }, function( data ){
			setData( data );
			$( 'input', $this ).val( '' );
		});
		
		return false;
	});
	
	/* = Saisie de lien personnalisé = */
	$(document).on( 'click', '#tiFyComponentsCustomFieldsPostTypePermalink-custom > a', function( e ){
		e.preventDefault();
		
		var $this = $(this);
	
		$( '#sample-permalink' ).css('opacity', '0.5' );
		$.post( tify_ajaxurl, { action: 'tiFyComponentsCustomFieldsPostTypePermalink', url: $this.prev().val() }, function( data ){
			setData( data );
			$this.prev().val( '' );
		});
		
		return false;
	});
	
	/* = Suppression du lien personnalisé = */
	$(document).on( 'click', '#tiFyComponentsCustomFieldsPostTypePermalink-cancel', function( e ){
		e.preventDefault();
		
		var $this = $(this);
		$( '#sample-permalink' ).css('opacity', '0.5' );
		$.post( tify_ajaxurl, { action: 'tiFyComponentsCustomFieldsPostTypePermalink', cancel: $( '#post_ID' ).val() }, function( data ){
			console.log( data );
			cancelData( data );
		});
		
		return false;
		
	});
	
	setData = function( data )
	{
		$( '#sample-permalink' ).html( '<a href="'+ data.url +'">'+ data.url +'</a>' ).css('opacity', '1' );
		$( '#edit-slug-buttons' ).hide();
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-selected' ).val( data.selected );
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-cancel' ).show();
	};
	
	cancelData = function( data )
	{
		$( '#edit-slug-box' ).html( data.url );
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-selected' ).val( '' );
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-cancel' ).hide();
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-selected' ).prop( 'checked', false );
		$( '#tiFyComponentsCustomFieldsPostTypePermalink-notice' ).hide();
	}
	
});