jQuery(document).ready(function($){    
    var ajax_action_prefix = $( '#ajaxActionPrefix' ).val(); 
    /*

	$( '#tify_adminview_import-download_sample' ).click( function(e){
		e.stopPropagation();
    	e.preventDefault();
    	window.location.href = tify_ajaxurl + '?action=tiFyCoreAdminModelImport_download_sample_' + id;
    	//$.post( tify_ajaxurl, { action : 'tiFyCoreAdminModelImport_download_sample_'+ id }, function( resp ){ });
	});*/
	    
    /**
     * TELECHARGEMENT DU FICHIER D'IMPORT
     */	
	$( '.tiFyTemplatesImportUploadForm-FileInput' ).on('change', function(e) {
		e.stopPropagation(); e.preventDefault();
    	
		$closest = $(this).closest('.tiFyTemplatesImport-Form--upload' );
		$spinner = $( '.tiFyTemplatesImportUploadForm-Spinner', $closest );
		
    	// Affichage du spinner    	
		$spinner.addClass( 'is-active' );
		
		// Traitement des données
    	files = e.target.files;
    	var data = new FormData();
	    $.each( files, function( key, value ){
	        data.append( key, value );
	    });	
	    data.append( 'action', ajax_action_prefix +'_upload' );
	   	
	    $.ajax({
	        url:           tify_ajaxurl,
	        type:          'POST',
	        data:          data,
	        cache:         false,
	        dataType:      'json',
	        processData:   false,
	        contentType:   false, 
	        success:       function( resp, textStatus, jqXHR )
	        {
	            $( '#datatablesAjaxData' ).val( encodeURIComponent( JSON.stringify( resp.data ) ) );
	            AjaxListTable.draw(true);
	            
	            // Masquage du spinner 
	        	$spinner.removeClass( 'is-active' );     		
	        }
	    });
	});
	
	/**
	 * IMPORT DES DONNEES
	 */
	/**
	 * Lancement de l'import d'une ligne 
	 */
	$( document ).on( 'click', '.tiFyTemplatesImport-RowImport', function(e){
	    e.preventDefault();
	    
	    $tr = $(this).closest( 'tr' );
	    var import_index = $( this ).data( 'import_index' );
	    
	    $tr.addClass( 'active' ); 
	    
	    
	    $( 'td', $tr ).each( function(){
	        $(this).append( '<div class="tdOverlay"/>' );
	    });

	    importData( import_index );	    
    });
	
	/**
	 * Lancement de l'import complet du fichier
	 */
	$( document ).on( 'submit', '.tiFyTemplatesImport-Form--import', function(e){
		e.preventDefault();	
		
		importData();
	});
	
	/**
	 * Import d'un ligne de donnée
	 */
	var importData = function( import_index = 0 ) {
	    var data = { action: ajax_action_prefix +'_import', import_index: import_index };
	    
	    if( ajax_data = JSON.parse( decodeURIComponent( $( '#datatablesAjaxData' ).val() ) ) )
	        data = $.extend( data, ajax_data );

	    $.ajax({
            url: tify_ajaxurl,
            type:           'POST',
            data:           data,            
            dataType:       'json', 
            success:        function( resp, textStatus, jqXHR )
            {
                AjaxListTable.draw( 'page' );
            }
        });
	}
});
