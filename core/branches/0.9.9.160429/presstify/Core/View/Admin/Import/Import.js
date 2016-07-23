jQuery( document ).ready( function( $ ){
	/* = ARGUMENTS = */
	/** == Classe == **/
	var id		= $( "#tify_adminview_import-id" ).val();		
	/** == Upload == **/
	var files;
	/** ==  Import == **/
	var filename, header = 0, offset = 0, limit = -1, per_pass = 10, passed = 0, options = {};
	
	/* = FICHIER D'EXEMPLE = */
	/*$( '#tify_adminview_import-download_sample' ).click( function(e){
		e.stopPropagation();
    	e.preventDefault();
    	$.post( tify_ajaxurl, { action : 'tify_csv_download_sample_'+ id }, function( resp ){ });
	});*/
	
	/* = TELECHARGEMENT DU FICHIER D'IMPORT = */
	/** == Déclenchement == **/		
	$( '#tify_adminview_import-uploadfile_button' ).on( 'change', function(e){
		e.stopPropagation();
    	e.preventDefault();
    	var $button = $(this);
    	
    	// Affichage du spinner    	
		$button.next( '.spinner' ).addClass( 'is-active' );
		
		// Suppression du formulaire d'inport actif
		$( "#tify_adminview_import-table_preview" ).fadeOut( );
				
    	files = e.target.files;
    	
	    var data = new FormData();
	    $.each( files, function( key, value ){
	        data.append(key, value);
	    });	    
	   	data.append( 'header', ( $( "#tify_adminview_import-header" ).attr('checked') ? 1 : 0 ) );
	   	
	    $.ajax({
	        url			: tify_ajaxurl +'?action=tify_adminview_import_upload_'+ id,
	        type		: 'POST',
	        data		: data,
	        cache		: false,
	        dataType	: 'json',
	        processData	: false,
	        contentType	: false, 
	        success		: function( resp, textStatus, jqXHR ){
	        	// Masquage du spinner
	        	$button.next( '.spinner' ).removeClass( 'is-active' );
	        	if( resp.success ){
	        		$( "#tify_adminview_import-table_preview" ).html( resp.data.table ).fadeIn();
	        		$( "#tify_adminview_import-options_form" ).html( resp.data.options );
	      			$( '#tify_adminview_import-import' ).addClass( 'active' );
	        	}	      		
	        }
	    });
	});
	
	/* =  IMPORT = */	
	/** == Déclenchement == **/
	$( document ).on( 'submit', '#tify_adminview_import-options_form > form', function(e){
		e.preventDefault();
		
		// Récupération des option d'import
		filename 	= $( "#tify_adminview_import-filename" ).val();
	   	header 		= parseInt( $( "#tify_adminview_import-hasheader" ).val() );
		offset 		= parseInt( $( "#tify_adminview_import-offset" ).val() )-1+header;		
		limit 		= ( $( "#tify_adminview_import-limit" ).val() > -1 ) ? parseInt( $( "#tify_adminview_import-limit" ).val() ) : parseInt( $( "#tify_adminview_import-total" ).val() ); 
		per_pass 	= 10;
		if( per_pass > limit )
			per_pass = limit;
		
		$.each( $( '#tify_adminview_import-options_form > form' ).serializeArray(), function( i, j ){
	    	options[j.name] = j.value;
	    });
			
		tify_csv_import();
		$( '#tify_progress, #tify_backdrop' ).addClass( 'active' );
	});
	function tify_csv_import(){		
		$( '#tify_progress .progress-bar' ).css( 'width', parseInt( ( ( passed/limit )*100 )+1 )+'%' );
		if( passed < limit ){
			$.ajax({
		        url			: tify_ajaxurl,
		        type		: 'POST',
		        data		: { action : 'tify_adminview_import_handle_'+ id, filename : filename, header : header, offset : offset, per_pass : per_pass, options : options },
		        dataType	: 'json', 
		        success		: function( resp, textStatus, jqXHR ){
		        	$.each( resp, function( u, v ){
	        			$( "#the-list > tr" ).eq( u ).addClass( 'imported' ).find( 'td.'+ id +'_tify_adminview_import_result' ).html( v );
		        	});		        		
		        	offset += per_pass;
		        	passed += per_pass;
		        	tify_csv_import();      		        	       	
		        }
		    });
		} else {
			filename = undefined, header = 0, offset = 0, limit = -1, per_pass = 10, passed = 0, options = {};
			$( '#tify_adminview_import-import' ).fadeOut( function( ){ $this.remove(); });
    		$( '#tify_progress, #tify_backdrop' ).removeClass('active');
		}
	}
});
