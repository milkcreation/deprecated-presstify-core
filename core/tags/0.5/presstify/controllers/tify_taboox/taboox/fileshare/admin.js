/**
Dependencies: jquery, jquery-ui-sortable
*/
var mkpbx_fileshare_frame;
jQuery(document).ready(function($){
	/**
	 * Selection de fichier à partager
	 */
	// Ajout
	$(document).on('click', '.add-fileshare', function( e ){
	 	e.preventDefault();
	 	var item_name = $( this ).data( 'item_name' );
	 	var target = $( this ).data( 'target' );
	 	var max = $( this ).data( 'max' );
	 	
	 	if( max > 0 && $( 'li', target ).length >= max ){ 
			alert( 'Nombre maximum de fichier atteint' );
			return false;
		}
	 	
		wp_media_args = { title: $( this ).data( 'uploader_title' ), editing : true, multiple : true };
		
		if( $( this ).data( 'type' ) )
			$.extend( wp_media_args, { library:{ type: $( this ).data( 'type' ) } } );
		
		mkpbx_fileshare_frame = wp.media.frames.mkpbx_fileshare_frame = wp.media(	wp_media_args );
		 
		mkpbx_fileshare_frame.on( 'select', function() {
			var selection = mkpbx_fileshare_frame.state().get('selection'); 			
			selection.map( function( attachment ) {
			 	attachment = attachment.toJSON();
				var html = '';
				var uid = uniqid();
				var order = $( 'li', target ).size()+1;
				html += '<li>';
				html += '<span class="icon"><img src="'+attachment.icon+'" /></span>';
				html += '<span class="title">'+attachment.title+'</span>';
				html += '<span class="mime">'+attachment.mime+'</span>';
				html += '<a href="#" class="remove tify_button_remove"></a>';	
				html += '<input type="hidden" name="mkpbx_postbox[multi]['+item_name+']['+uid+']" value="'+attachment.id+'" />';
				html += '<input type="hidden" class="order" name="mkpbx_postbox[single]['+item_name+'_order][]" value="'+attachment.id+'" />';
				html += '</li>';
				
				$( target ).append( html );
			});
		});	

		mkpbx_fileshare_frame.open();
	});
	// Suppression
	$(document).on('click', '.fileshare-list .remove', function( e ){
		e.preventDefault();
		$(this).closest('li').fadeOut( function(){
			$(this).remove();
		});
	});
	//
	// Ordonnacement des fichiers
	$( ".fileshare-list" ).sortable({
		placeholder: "ui-sortable-placeholder",
		axis: 'y'
	});

	var uniqid = function(prefix, more_entropy) {
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +    revised by: Kankrelune (http://www.webfaktory.info/)
	  // %        note 1: Uses an internal counter (in php_js global) to avoid collision
	  // *     example 1: uniqid();
	  // *     returns 1: 'a30285b160c14'
	  // *     example 2: uniqid('foo');
	  // *     returns 2: 'fooa30285b1cd361'
	  // *     example 3: uniqid('bar', true);
	  // *     returns 3: 'bara20285b23dfd1.31879087'
	  if (typeof prefix === 'undefined') {
		prefix = "";
	  }

	  var retId;
	  var formatSeed = function (seed, reqWidth) {
		seed = parseInt(seed, 10).toString(16); // to hex str
		if (reqWidth < seed.length) { // so long we split
		  return seed.slice(seed.length - reqWidth);
		}
		if (reqWidth > seed.length) { // so short we pad
		  return Array(1 + (reqWidth - seed.length)).join('0') + seed;
		}
		return seed;
	  };

	  // BEGIN REDUNDANT
	  if (!this.php_js) {
		this.php_js = {};
	  }
	  // END REDUNDANT
	  if (!this.php_js.uniqidSeed) { // init seed with big random int
		this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
	  }
	  this.php_js.uniqidSeed++;

	  retId = prefix; // start with prefix, add current milliseconds hex string
	  retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
	  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
	  if (more_entropy) {
		// for more entropy we add a float lower to 10
		retId += (Math.random() * 10).toFixed(8).toString();
	  }

	  return retId;
	};
});