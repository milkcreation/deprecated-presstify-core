jQuery( document ).ready( function($){
	$( 'html, body' ).click( function(e){
		if( ! $( e.target ).closest( '[data-tify_control="dropdown_menu-picker"].active' ).length && ! $( e.target ).closest( '[data-tify_control="dropdown_menu"].active' ).length )
			$( '[data-tify_control="dropdown_menu-picker"].active, [data-tify_control="dropdown_menu"].active' ).each( function(){
				$(this).removeClass( 'active' );
			});
	});

	$( document ).on( 'click', '[data-tify_control="dropdown_menu"]:not(.disabled)', function(e)
	{
		e.stopPropagation();
		
		var $closest 	= $(this);
		var picker		= $closest.data( 'picker' );
		var $picker 	=  $( '#'+ picker.id );

		if( $closest.next().is( $picker ) ){
			var $clone = $picker;
			$picker.remove();
			
			$( '.tify_control_dropdown_menu-picker[data-selector="#'+ $closest.attr('id') +'"]' ).each( function(){
				$(this).remove();
			});
			
			$( picker.append ).append( $clone );
		}
			
		var offset = getOffset( picker, $(this) );	
		$picker.css( offset ).toggleClass('active');
		$picker.outerWidth( $closest.outerWidth() );
		$closest.toggleClass('active');

        $closest.trigger('tify_control.dropdown_menu.change');

		return false;
	});
	
	function getOffset( picker, input ) 
	{
		var $picker 	=  $( '#'+ picker.id );
        var extraY 		= ( $('body').hasClass('admin-bar') ) ? $( '#wpadminbar' ).outerHeight() : 0;
        var dpWidth 	= $picker.outerWidth();
        var dpHeight	= $picker.outerHeight();
        var inputHeight = input.outerHeight();
        var $append 	= $( picker.append );
        var viewWidth 	= $append.outerWidth() + $append.scrollLeft();
        var viewHeight	= $append.outerHeight() + $append.scrollTop();
        var offset 		= input.offset();
        
        offset.top 	+= inputHeight /*+ parseInt( input.closest('[data-tify_control="dropdown"]').css( "border-top-width" ) )*/;
        offset.left -= parseInt( input.closest('[data-tify_control="dropdown_menu"]').css( "border-left-width" ) );
        
        offset.left -=
            Math.min(offset.left, (offset.left + dpWidth > viewWidth && viewWidth > dpWidth) ?
            Math.abs( offset.left + dpWidth - viewWidth ) : 0 );
        
        switch( picker.position )
        {
        	default:
        		offset.top -= extraY;        		
        		break;
        	case 'top':
        		offset.top -= Math.abs( dpHeight + inputHeight + extraY );
        		break;
        	case 'clever' :
        		offset.top -=
                    Math.min(offset.top, ((offset.top + dpHeight > viewHeight && viewHeight > dpHeight) ?
                    Math.abs(dpHeight + inputHeight + extraY) : extraY));
        		break;
        }        

        return offset;
    }
	
	$( window ).on( 'scroll.tify_control.dropdown_menu', function(e){		
		$( '[data-tify_control="dropdown_menu"].active' ).each( function(){
			var $closest 	= $(this);	
			var picker		= $closest.data( 'picker' );
			var $picker 	=  $( '#'+ picker.id );
			var offset 		= getOffset( picker, $(this) );	

			$picker.css( offset );
			$picker.outerWidth( $closest.outerWidth() );
		});
	});
});