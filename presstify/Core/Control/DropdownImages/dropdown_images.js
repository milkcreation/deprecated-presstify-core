jQuery( document ).ready( function($) {	
	$( 'html, body' ).click( function(e){
		if( ! $( e.target ).closest( '.dropdown_images-picker.active' ).length && ! $( e.target ).closest( '[data-tify_control="dropdown_images"].active' ).length  )
			$( '.dropdown_images-picker.active, [data-tify_control="dropdown_images"].active' ).each( function(){
				$(this).removeClass( 'active' );
			});
	});
	
	$( document ).on( 'change', '.dropdown_images-picker.active > ul > li > ul > li > label > .selection > input[type="radio"]', function(e) {		
		e.stopPropagation();
		
		var $selector 	= $( $(this).closest( '.dropdown_images-picker.active' ).data( 'selector' ) );	
		var $picker 	= $( this ).closest( '.dropdown_images-picker.active' );
		$( 'li.checked', $picker ).each( function(){ $(this).removeClass('checked'); });
		$( this ).closest( 'li' ).addClass('checked');
		
		$clone = $( this ).closest( 'li' ).find( '.selection' ).clone();
		$( '.selection', $selector ).replaceWith( $clone );
		$picker.removeClass( 'active' );
		
		$( this ).trigger( 'tify_dropdown_images_change' );					

		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown_images"]:not(.disabled) > .selected', function(e) {
		e.stopPropagation();
		
		var $closest 	= $(this).closest( '[data-tify_control="dropdown_images"]' );				
		var pickerID	= $closest.data( 'picker' );		
		var $picker 	=  $( '#'+ pickerID );
		
		if( $closest.next().is( $picker ) ){
			var $clone = $picker;
			$picker.remove();
			$( 'body' ).append( $clone );
		}
			
		var offset = getOffset( $picker,  $(this) );	
		$picker.css( offset ).toggleClass('active');
		$closest.toggleClass('active');

		
		return false;
	});
	
	function getOffset( picker, input ) {
        var extraY 		= ( $('body').hasClass('wp-admin') ) ? /*$( '#wpadminbar' ).outerHeight()*/ 0 : 0;
        var dpWidth 	= picker.outerWidth();
        var dpHeight	= picker.outerHeight();
        var inputHeight = input.outerHeight();
        var doc 		= picker[0].ownerDocument;
        var docElem 	= doc.documentElement;
        var viewWidth 	= docElem.clientWidth + $(doc).scrollLeft();
        var viewHeight	= docElem.clientHeight + $(doc).scrollTop();
        var offset 		= input.offset();
        offset.top += inputHeight;

        offset.left -=
            Math.min(offset.left, (offset.left + dpWidth > viewWidth && viewWidth > dpWidth) ?
            Math.abs(offset.left + dpWidth - viewWidth) : 0);

        offset.top -=
            Math.min(offset.top, ((offset.top + dpHeight > viewHeight && viewHeight > dpHeight) ?
            Math.abs(dpHeight + inputHeight + extraY) : extraY));

        return offset;
    }
});