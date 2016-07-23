jQuery( document ).ready( function( $ ){
	var total = 0, per_page = 100, paged = 1, data = {};
	var container = '#tify_forms-export_form';
	
	$( container ).on( "submit", function( e ) {
		e.preventDefault();
		
		// Initialisation de la barre de progression
		$( "#progressbar", container ).progressbar({
			value: 0,
		}).hide().fadeIn();		 
		$( "#progressbar .progress-label", container ).text( tify_forms_export.progressLoading );
		// Masquage du bouton de téléchargement de fichier
		$( "#download-csv", container  ).hide();
		
		data = { 'action': 'tify_forms_records_count' };
		// Champs de vérification
		data['ajax_nonce'] = $( '#_wpnonce', $(this) ).val();
		// Récupération des attributs
		var attrs = $( '.export-attrs', container ).serializeArray();
		data['attrs'] = {};
		$.each( attrs, function( u, v ){
			data.attrs[v.name] = v.value;
		});		
		// Récupération des options
		var options = $( '.export-options', container ).serializeArray();
		data['options'] = {};
		$.each( options, function( u, v ){
			data.options[v.name] = v.value;
		});
		// Récupération des champs
		var fields = $( '.export-fields', container ).serializeArray();	
		data['fields'] = [];
		$.each( fields, function( u, v ){
			data['fields'].push( v.value );
		});
		
		$.ajax({
			url 		: tify_ajaxurl, 
			data 		: data,
			type		: 'post',
			dataType	: 'json',
			success		: function( resp ){
				total = parseInt( resp );
				$( "#progressbar", container ).progressbar( "option", { max: 100 } );				
				records_export();			
			}
		});		
	});
	
	function records_export(){
		var offset = ( ( paged-1 )* per_page );
		var percent = offset/total*100;
		
		$( "#progressbar", container ).progressbar({ value: percent });
		$( "#progressbar .progress-label", container ).text( parseInt(percent)+'%' );
		
		if( offset < total ){ 				
			$.ajax({
				url 		: tify_ajaxurl, 
				data 		: { action : 'tify_forms_records_export', filename : data.attrs.filename, form_id : data.attrs.form_id, per_page : per_page, paged : paged, options : data['options'], fields : data['fields'], offset : offset, total : total },
				type		: 'post',
				success		: function( resp ){
					++paged;
					records_export();
				}
			});
		} else {
			$( "#progressbar .progress-label", container ).text( tify_forms_export.progressComplete );
			$( "#download-csv", container ).fadeIn();
			total = 0, per_page = 100, paged = 1, data = {};
		}
	}
});