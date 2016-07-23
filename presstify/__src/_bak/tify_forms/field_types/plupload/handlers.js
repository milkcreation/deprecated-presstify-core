jQuery( document).ready( function($){  
   var uploader, uploader_init;
   
   // Nettoyage de l'interface
   function reset( up, container ){
   		$container = $( '#'+ container );
   		if( ! up.settings.multi_selection )
   				$( '.file-items', $container ).empty();
   		
   		$( '.tify-forms-plupload-error', $container ).empty();
   		$( '.preview', $container ).empty();
   }
      
   uploader_init = function(){
   		var container, $container;
   		
   		// Initialisation
   		uploader = new plupload.Uploader( tifyFormsUploaderInit );
		
		// Callbacks
   		uploader.bind( 'Init', function( up ){
   			container 	= up.settings.container;
   			$container = $( '#'+ container );
   		});
   		uploader.init();
   		
   		uploader.bind( 'FilesAdded', function( up, files ){
   			reset( up, container );
   				
			$.each( files, function( u, v ){
				$( '<li class="file-item">' )
					.attr( 'id', 'file-item-' + v.id )
					.append( $( '<div class="original_filename">').text( ' ' + v.name ), '<div class="progress"><div class="progress-bar">0%</div></div>' )
					.appendTo( $( '.file-items', $container ) );
			});

			up.refresh();
			up.start();
		});
		
		
		uploader.bind( 'UploadFile', function( up, file ) {

		});
		
		uploader.bind( 'UploadProgress', function( up, file ) {
			var item = $( '#file-item-' + file.id, $container );
			$('.progress-bar', item )
				.width( ( $( '.progress', item ).width() * file.loaded ) / file.size )
				.html( file.percent + '%' );
		});		

		uploader.bind('Error', function( up, err ) {
			switch (err.code) {
				case plupload.FAILED:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.upload_failed );
					break;
				case plupload.FILE_EXTENSION_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.invalid_filetype);
					break;
				/*case plupload.FILE_SIZE_ERROR:
					uploadSizeError(uploader, fileObj);
					break;*/
				case plupload.IMAGE_FORMAT_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.not_an_image);
					break;
				case plupload.IMAGE_MEMORY_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.image_memory_exceeded);
					break;
				case plupload.IMAGE_DIMENSIONS_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.image_dimensions_exceeded);
					break;
				case plupload.GENERIC_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.upload_failed);
					break;
				case plupload.IO_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.io_error);
					break;
				case plupload.HTTP_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.http_error );
					break;
				/*case plupload.INIT_ERROR:
					jQuery('.media-upload-form').addClass('html-uploader');
					break;*/
				case plupload.SECURITY_ERROR:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.security_error );
					break;
				/*case plupload.UPLOAD_ERROR.UPLOAD_STOPPED:
				case plupload.UPLOAD_ERROR.FILE_CANCELLED:

					break;*/
				default:
					$( '.tify-forms-plupload-error', $container).html( pluploadL10n.default_error );
					break;
			}
		});

		uploader.bind( 'FileUploaded', function( up, file, response ) {
			var resp = $.parseJSON( response.response ); 
			$( '.preview', $container ).html( resp.preview );
			$( '#file-item-'+ file.id ).addClass( 'uploaded' ).html( resp.output );
		});

		uploader.bind('UploadComplete', function() {

		});  		
   };
   
   if ( typeof( tifyFormsUploaderInit ) == 'object' )
		uploader_init();
});