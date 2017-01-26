jQuery( document ).ready( function( $ ){	
	$( '#tiFyTemplatesExport-Submit' ).on( 'click', function(e){
		e.preventDefault();
		
		var data = 'action=tiFyTemplatesExport_exportItems&'+ $( '#tiFyTemplatesExport-Form').serialize();
		$( '#tiFyTemplatesExport-Progress' ).addClass( 'active' );
		$( '.tify_control-progress-bar', '#tiFyTemplatesExport-Progress' ).css( 'background-position', '0 0' );
		
		tiFyTemplatesExport( data );			
	});
	
	var tiFyTemplatesExport = function( data )
	{
		$.ajax({
			url 		: tify_ajaxurl,
			data 		: data,	
			success 	: function( resp ){
				if( resp.data.paged < resp.data.total_pages ){
					tiFyTemplatesExport( resp.data.query_args );					
					$( '.tify_control-progress-bar', '#tiFyTemplatesExport-Progress' ).css( 'background-position', '-'+ Math.ceil( ( resp.data.paged / resp.data.total_pages ) * 100 ) +'% 0' );
				} else {
					$( '#tiFyTemplatesExport-Progress' ).removeClass( 'active' );
					$( '#tiFyTemplatesExport-DownloadFile' ).html( '<a href="'+ resp.data.url +'/'+ resp.data.file +'">'+ resp.data.file +'</a>' );
				}
				
			}
		});	
	}
});