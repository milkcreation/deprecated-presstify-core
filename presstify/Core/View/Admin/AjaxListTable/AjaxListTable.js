jQuery( document ).ready( function($){
	$( '.hide-column-tog' ).unbind();
	
	$.extend( 
		$.fn.dataTable.defaults, 
		{
			// Liste des colonnes
	    	columns:			tiFy_View_Admin_AjaxListTable.columns,
	    	// Nombre d'éléments par page
	    	iDisplayLength:		parseInt( tiFy_View_Admin_AjaxListTable.per_page ),
			// Tri par défaut
			order: 				[],    	
			// Traduction
			language:			tiFy_View_Admin_AjaxListTable.language,
			// Interface
			dom: 'rt'
		}
	);

	var $table = $( '.wp-list-table' );
	var oTable = $table
			.DataTable({
				// Activation de l'indicateur de chargement 
				processing: 	true,
		        // Activation du chargement Ajax
				serverSide: 	true,
				// Désactivation du chargement Ajax à l'initialisation 
				deferLoading: [ $table.data( 'total' ), $table.data( 'length' ) ],
		        // Traitement Ajax
		        ajax:			
		        {
		    	    url: 		tify_ajaxurl,
		    	    data: 		function ( d ) {
		    	    	d.action = $table.data( 'action' ) +'_get';	    	    		    	    	
		    	        return d;
		    	    },
		    	    dataType: 	'json', 
		    	    method: 	'GET',
		    	    dataSrc:	function( json )
		    	    {
		    	    	$( ".tablenav-pages" ).each( function(){
		    	    		$(this).replaceWith( json.pagination );
		    	    	});
		    	    
		    	    	return json.data;
		    	    }
		    	},
		    	// Initialisation
		    	initComplete: 	function( settings, json ) 
		    	{
		    		$.each( oTable.columns().visible(), function( u, v ){
		    			var name = oTable.settings()[0].aoColumns[u].name;
		    			$( '.hide-column-tog[name="'+ name +'-hide"]' ).prop( 'checked', v );
		    		});
		    		
		    		// Affichage/Masquage des colonnes
		    		$( '.hide-column-tog' ).change( function(e){
		    			e.preventDefault();
		    			var $this = $( this );
		    
		    			var column = oTable.column( $this.val()+':name' );
		      			column.visible( ! column.visible() );
		    			
		    			return false;
		    		});
		    		
		    		// Soumission du formulaire
		    		$( 'form#adv-settings' ).submit( function(e){
		    			e.preventDefault();
		    			
		    			var value = parseInt( $( '.screen-per-page', $(this) ).val() )
		    			
		    			$.post( tify_ajaxurl, { action: $table.data('action') +'_per_page', per_page: value }, function(){
		    				$( '#show-settings-link' ).trigger( 'click' );
		    			});
		    			
		    			oTable.
		    				page.len( value )
		    				.draw();
		    				
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
		    			
		    			oTable
		    				.page( page )
		    				.draw( 'page' );
		    			
		    			return false;
		    		});
		    		
		    		// Champ de recherche
		    		$( '.search-box #search-submit' ).click( function(e){
		    			e.preventDefault();
		    			
		    			var value = $(this).prev().val();
		    			
		    			oTable
		    		    	.search( value )
		    		    	.draw();
		    			
		    			return false;
		    	    });
		        }
			}); 
});