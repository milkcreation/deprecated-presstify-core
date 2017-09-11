jQuery(document).ready(function($){
    var // Nombre de ligne à traiter lors d'un import
        import_rows = 0,
        
        // Processus actif
        process = false;

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
    $( document ).on( 'click', '.tiFyTemplatesImport-submit', function(e){
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
                    show :      true,
                    info :      '<span style="color:#2980b9;">'+tiFyTemplatesAdminImport.prepare+'</span>',
                    close :     function( event, ui )
                    {
                        // Information d'annulation de l'import
                        ui.infos( '<span style="color:#f1c40f;">'+tiFyTemplatesAdminImport.cancel+'</span>' );
                        
                        // Désactivation du processus d'import
                        process = false;
                        
                        // Attend de la fin de l'import en court pour fermer l'interface
                        $( document ).on( 'tiFyTemplatesImport.complete', function(){
                            ui.close();
                        });
                    }  
                }
            );
        
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
        if( ! import_rows || ! process ){
            $( document ).trigger( 'tiFyTemplatesImport.complete' );
            return;
        }
        
        // Traitement des données d'import
        var // Détermine la ligne de données à traiter
            row_key = $( '.tiFyTemplatesImport-RowImport', $row ).data( 'item_index_key' ),
            row_value = $( '.tiFyTemplatesImport-RowImport', $row ).data( 'item_index_value' ),
            
            // Si le traitement concerne la dernière ligne pour un passage à la page suivante
            next = $row.is( ':last-child' ) ? true : false,
            data = {};
        
        data[row_key] = row_value;   
        
        if( import_data = JSON.parse( decodeURIComponent( $( '#ajaxImportData' ).val() ) ) ){
            data = $.extend( data, import_data );
        }

        if( datatables_data = JSON.parse( decodeURIComponent( $( '#ajaxDatatablesData' ).val() ) ) ){
            data = $.extend( data, datatables_data );
        }
        
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
                // Information de résultat de l'import
                if( ! resp.success ) {
                    $( '#tiFyTemplatesImport-ProgressBar' ).tiFyProgress( 'infos', '<span style="color:#c0392b;">'+resp.data.message+'</span>' );
                } else if( process ){
                    $( '#tiFyTemplatesImport-ProgressBar' ).tiFyProgress( 'infos', '<span style="color:#1abc9c;">'+resp.data.message+'</span>' );
                }
                
                // Incrémentation de la barre de progression
                $( '#tiFyTemplatesImport-ProgressBar' ).tiFyProgress( 'increase' );
                
                // Le traitement est complet
                if( ! --import_rows ){
                    AjaxListTable.draw( 'page' );
                    
                    $( document )
                        .on( 'draw.dt.tiFyTemplatesImport', function ( e, settings, json, xhr ) {                
                            $( '#tiFyTemplatesImport-ProgressBar' ).tiFyProgress( 'close' );
                            // Désactivation du processus actif
                            process = false; importRow( 0 );
                            
                            $(this).unbind( 'draw.dt.tiFyTemplatesImport' );
                        });                   
                    
                    return;
                    
                // Le traitement suivant est sur la même page    
                } else if( ! next ){
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
