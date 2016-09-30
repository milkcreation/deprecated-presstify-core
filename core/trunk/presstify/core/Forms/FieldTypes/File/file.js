jQuery( document ).ready( function( $ ){
	/* = ARGUMENTS = */
	/** == Upload == **/
	var files;	
	
	/* = TELECHARGEMENT DU FICHIER D'IMPORT = */
	/** == Déclenchement == **/		
	$( '#tify_csv-uploadfile_button' ).on( 'change', function(e){
		e.stopPropagation();
    	e.preventDefault();
    	var $button = $(this);
				
    	files = e.target.files;
    	
	    var data = new FormData();
	    $.each( files, function( key, value ){
	        data.append(key, value);
	    });
		    
	    $.ajax({
	        url			: tify_ajaxurl +'?action=tify_forms_fieldtype_file_upload',
	        type		: 'POST',
	        data		: data,
	        cache		: false,
	        dataType	: 'json',
	        processData	: false,
	        contentType	: false, 
	        success		: function( resp, textStatus, jqXHR ){
	        	console.log( resp );
	        }
	    });
	});
});