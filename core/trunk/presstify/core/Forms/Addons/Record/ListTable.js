jQuery(document).ready( function($){
	$( document ).on( 'click', '#the-list .row-actions a.inline-preview', function(e){
		e.preventDefault();
		
		var vars = {}; 
		$(this).attr('href').replace( 
			/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
			function( m, key, value ) { // callback
				vars[key] = value !== undefined ? value : '';
			}
		);
		if( vars['ID'] === undefined )
			return;
		var record_id = parseInt( vars['ID'] );
		
		var $parent = $(this).closest( 'tr' );
		
		if( $parent.next().attr('id') != 'inline-preview-'+ record_id ){
			// Création de la zone de prévisualisation
			$previewRow = $( '#inline-preview' ).clone(true);
			$previewRow.attr( 'id', 'inline-preview-'+record_id );
			$parent.after( $previewRow );
			// Récupération des éléments de formulaire
			$.post( tify_ajaxurl, { action: 'tiFyCoreFormsAddonsRecordListTableInlinePreview', record_id: record_id }, function( data ){
				$( '> td', $previewRow ).html(data);			
			});					
		} else {
			$previewRow = $parent.next();
		}	
		$parent.closest('table');
		$previewRow.toggle();	
				
		return false;
	});
});