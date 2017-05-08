jQuery(document).ready(function($){
    var // Préfixe des actions ajax
        ajax_action_prefix = $( '#ajaxActionPrefix' ).val(),

        // Nombre de ligne à traiter lors d'un import
        import_rows = 0,
        
        // Processus actif
        process = false;

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
        
        // Empêche l'execution si un processus est actif
        if( process )
        	return;
        
        // Active le processus d'import
        process = true;
        
        var $row = $(this).closest( 'tr' );        
        import_rows = 1;
        
        importRow( $row );        
    });    

    /**
     * Lancement de l'import complet du fichier
     */
    $( document ).on( 'submit', '.tiFyTemplatesImport-Form--import', function(e){
        e.preventDefault();
        
        // Empêche l'execution si un processus est actif
        if( process )
        	return;
        
        // Active le processus d'import
        process = true;
        
        // Définie le nombre de ligne à traiter
        var info = AjaxListTable.page.info();
        import_rows = info.recordsDisplay;
        
        $( '#tiFyTemplatesImport-ProgressBar' )
            .tiFyProgress( 
                'option', 
                { 
                    value :     0, 
                    max :       import_rows, 
                    close :     function( event, el ){
                        // Désactivation du processus d'import
                        process = false;
                        // Fermeture de l'indicateur de progression
                        el.hide();
                    }  
                }
            )
            .show();
        
        if( info.page ){
            AjaxListTable.page( 0 ).draw( 'page' );
        } else {
           AjaxListTable.draw( false );
        }

        $( document )
            .on( 'draw.dt.tiFyTemplatesImport', function ( e, settings, json, xhr ) {                
                var $row = $( AjaxListTable.row(':eq(0)', { page: 'current' }).node() );
                importRow( $row );
                
                $(this).unbind( 'draw.dt.tiFyTemplatesImport' );
            });
    });
    
    /**
     * Import d'un ligne de donnée
     */
    var importRow = function( $row ) {
        // Bypass
        if( ! import_rows || ! process )
            return;
        
        // Traitement des données d'import
        var // Détermine la ligne de données à traiter
            import_index = $( '.tiFyTemplatesImport-RowImport', $row ).data( 'import_index' ),
            // Si le traitement concerne la dernière ligne pour un passage à la page suivante
            next = $row.is( ':last-child' ) ? true : false,
            data = { action: ajax_action_prefix +'_import', import_index: import_index };
        
        if( ajax_data = JSON.parse( decodeURIComponent( $( '#datatablesAjaxData' ).val() ) ) ){
            data = $.extend( data, ajax_data );
        }
        
        // Bypass
        if( ! data.filename )
            return;  

        // Indicateur de traitement (overlay + animation du bouton)
        $row.addClass( 'active' ); 
        $( 'td', $row ).each( function(){
            $(this).append( '<div class="tdOverlay"/>' );
        });
                
        $.ajax({
            url:            tify_ajaxurl,
            type:           'POST',
            data:           data,            
            dataType:       'json', 
            success:        function( resp, textStatus, jqXHR )
            {       	
                if( ! resp.success )
                    console.log( resp.data );
                
                $( '#tiFyTemplatesImport-ProgressBar' ).tiFyProgress( 'increase' );
                
                // Le traitement est complet
                if( ! --import_rows ){
                    AjaxListTable.draw( 'page' );
                    // Désactivation du processus actif
                    process = false;
                    return;
                // Le traitement suivant est sur la même page    
                } if( ! next ){
                    var i = $row.next().index();
                    AjaxListTable.draw( 'page' );            
                // Le traitement suivant implique de passer à la page suivante    
                } else {
                    var i = 0;
                    AjaxListTable.page( 'next' ).draw( 'page' );  
                }
                
                $( document )
                    .on( 'draw.dt.tiFyTemplatesImport', function ( e, settings, json, xhr ) {                        
                        var $next = $( AjaxListTable.row(':eq('+ i +')', { page: 'current' }).node() );
                        importRow( $next );
                        $(this).unbind( 'draw.dt.tiFyTemplatesImport' );
                    });
            }
        });
    }
});
