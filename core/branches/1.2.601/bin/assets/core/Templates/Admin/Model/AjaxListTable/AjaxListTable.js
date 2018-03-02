var AjaxListTable;

jQuery( document ).ready( function($){
    // Désactivation des action de masquage des colonnes natif de Wordpress
    $( '.hide-column-tog' ).unbind();

    $.extend( 
        $.fn.dataTable.defaults, 
        {
            // Liste des colonnes
            columns:                tiFyTemplatesAdminAjaxListTable.columns,
            
            // Nombre d'éléments par page
            iDisplayLength:         parseInt( tiFyTemplatesAdminAjaxListTable.per_page ),
            
            // Tri par défaut
            order:                  [],  
            
            // Traduction
            language:               tiFyTemplatesAdminAjaxListTable.language,
            
            // Interface
            dom: 'rt'
        }
    );

    var $table  = $( '.wp-list-table' ),
        filters = {},
        o       = {
            // Activation de l'indicateur de chargement 
            processing:     true,
            
            // Activation du chargement Ajax
            serverSide:     true,
            
            // Désactivation du chargement Ajax à l'initialisation 
            deferLoading:   [ tiFyTemplatesAdminAjaxListTable.total_items, tiFyTemplatesAdminAjaxListTable.per_page ],
                        
            // Attributs de la requête de traitement Ajax
            ajax:            
            {
               url:         tify_ajaxurl,
               
               data:        function ( d ) {
                    d = $.extend(d, filters, { action: tiFyTemplatesAdminAjaxListTable.action_prefix +'_get_items' });
                    /**
                     * Ajout dynamique d'arguments passés dans la requête ajax de récupération d'éléments
                     * @see tiFy\Core\Templates\Admin\Model\AjaxListTable\AjaxListTable::hidden_fields();
                     * $( '#ajaxDatatablesData' ).val( encodeURIComponent( JSON.stringify( resp.data ) ) );
                     */
                    if( $( '#ajaxDatatablesData' ).val() )
                    {
                        var ajax_data = JSON.parse( decodeURIComponent( $( '#ajaxDatatablesData' ).val() ) );
                        d = $.extend( d, ajax_data );
                    }
                    
                    return d;
                },
                
                dataType:   'json', 
                
                method:     'GET',
                
                dataSrc:    function(json)
                {
                    if( ! $( '.search-box' ).length ){
                        $( json.search_form ).insertBefore( '.tablenav.top' );
                    }
                    
                    $( ".tablenav-pages" ).each( function(){
                        $(this).replaceWith( json.pagination );
                    });
                    
                    return json.data;
                }
            },
            
            drawCallback:     function( settings ) {
                var api = this.api();    
                //console.log( api.ajax.params() );
            },
            
            // Initialisation
            initComplete:     function( settings, json ) 
            {
                $.each( AjaxListTable.columns().visible(), function( u, v ){
                    var name = AjaxListTable.settings()[0].aoColumns[u].name;
                    $( '.hide-column-tog[name="'+ name +'-hide"]' ).prop( 'checked', v );
                });
                
                // Affichage/Masquage des colonnes
                $( '.hide-column-tog' ).change( function(e){
                    e.preventDefault();
                    var $this = $( this );
        
                    var column = AjaxListTable.column( $this.val()+':name' );
                      column.visible( ! column.visible() );
                    
                    return false;
                });
                
                // Soumission du formulaire
                $( 'form#adv-settings' ).submit( function(e){
                    e.preventDefault();
                    
                    var value = parseInt( $( '.screen-per-page', $(this) ).val() )
                    
                    $.post( tify_ajaxurl, { action: tiFyTemplatesAdminAjaxListTable.action_prefix +'_per_page', per_page: value }, function(){
                        $( '#show-settings-link' ).trigger( 'click' );
                    });
                    
                    AjaxListTable.
                        page.len( value )
                        .draw();
                        
                    return false;
                });
                
                // Filtrage
                $( '#table-filter' ).submit( function(e){
                    e.preventDefault();
                    
                    filters = {};
                                    
                    $.each( $( this ).serializeArray(), function(u,v){
                        if( ( v.name === '_wpnonce' ) || ( v.name === '_wp_http_referer' ) || ( v.name === 's' )  || ( v.name === 'paged' )  )
                            return true;
                        filters[v.name] = v.value;
                    });
                    
                    AjaxListTable.draw(true);
                    
                    return false;
                });
                
                // Pagination
                $( document ).on( 'click', '.tablenav-pages a', function(e){
                    e.preventDefault();
                    
                    var page = 0;
                    if( $(this).hasClass( 'next-page' ) ){
                        page = 'next';
                    } else if( $(this).hasClass( 'prev-page' ) ){
                        page = 'previous';
                    } else if( $(this).hasClass( 'first-page' ) ){
                        page = 'first';    
                    } else if( $(this).hasClass( 'last-page' ) ){
                        page = 'last';
                    } 
                    
                    AjaxListTable
                        .page( page )
                        .draw( 'page' );
                    
                    return false;
                });
                
                // Champ de recherche
                $( document).on( 'click', '.search-box #search-submit', function(e){
                    e.preventDefault();
                    
                    var value = $(this).prev().val();
                    
                    AjaxListTable
                        .search( value )
                        .draw();
                    
                    return false;
                });
            }
        };
    o = $.extend(o, tiFyTemplatesAdminAjaxListTable.options );

    AjaxListTable = $table
        .DataTable(o);
});