jQuery(document).ready(function($){
    var // Préfixe des actions ajax
        ajax_action_prefix = $( '#ajaxActionPrefix' ).val();

    /**
     * GENERATION DU FICHIER D'EXEMPLE A TELECHARGER
     */
    /*
    $( '#tify_adminview_import-download_sample' ).click( function(e){
        e.stopPropagation();
        e.preventDefault();
        window.location.href = tify_ajaxurl + '?action=tiFyCoreAdminModelImport_download_sample_' + id;
        //$.post( tify_ajaxurl, { action : 'tiFyCoreAdminModelImport_download_sample_'+ id }, function( resp ){ });
    });
    */

    /**
     * TELECHARGEMENT DU FICHIER D'IMPORT
     */    
    $( '.tiFyTemplatesFileImportUploadForm-FileInput' ).on('change', function(e) {
        e.stopPropagation(); e.preventDefault();

        $closest = $(this).closest('.tiFyTemplatesFileImport-Form--upload' );
        $spinner = $( '.tiFyTemplatesFileImportUploadForm-Spinner', $closest );
        
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
});