jQuery( document ).ready( function($){	
	$( 'html, body' ).click( function(e)
	{
		if( ! $( e.target ).closest( '[data-tify_control="dropdown-picker"].active' ).length && ! $( e.target ).closest( '[data-tify_control="dropdown"].active' ).length )
			$( '[data-tify_control="dropdown-picker"].active, [data-tify_control="dropdown"].active' ).each( function(){
				$(this).removeClass( 'active' );
			});
	});	
	
	/* = Modification de la selection dans la liste de selection de traitement = */
	$( document ).on( 'change', '[data-tify_control="dropdown-handler"]', function( e ){		
		var index 		= $( '> option:selected', $(this) ).index();
		var selector 	= $(this).data( 'selector' ); 

		return change( index, selector );
	});
	
	var change = function ( index, selector )
	{		
		var $selector	= $( selector );
		var picker		= $selector.data( 'picker' );
		var $picker 	= $( '#'+ picker.id );
		var $handler	= $( $selector.data( 'handler' ) );
		var $selected	= $( '> ul > li:eq('+ index +')', '#'+ picker.id );
				
		$selected.addClass( 'checked' ).siblings().removeClass( 'checked' );
		$( '.selected', selector ).html( $selected.html() );	
			
		return $handler.val();		
	};

	/* = Choix d'un élément dans la liste de sélection enrichie = */
	$( document ).on( 'click', '[data-tify_control="dropdown-picker"] > ul > li', function(e) 
	{		
		e.stopPropagation();
		
		var $picker		= $( $(this).closest( '[data-tify_control="dropdown-picker"]' ) );
		var $selector	= $( $( $picker.data( 'selector' ) ) );
		var $handler	= $( $( $picker.data( 'handler' ) ) );
		
		$handler.prop( 'selectedIndex', $(this).index() ).change();	
		$picker.removeClass( 'active' );
		$selector.removeClass( 'active' );
			
		$selector.trigger( 'tify_control.dropdown.change', $handler.val() );
		
		return false;
	});
	
	$( document ).on( 'click', '[data-tify_control="dropdown"]:not(.disabled)', function(e) 
	{
		e.stopPropagation();
		
		var $closest 	= $(this);
		var picker		= $closest.data( 'picker' );
		var $picker 	=  $( '#'+ picker.id );

		if( $closest.next().is( $picker ) ){
			var $clone = $picker;
			$picker.remove();
			
			$( '.tify_control_dropdown-picker[data-selector="#'+ $closest.attr('id') +'"]' ).each( function(){
				$(this).remove();
			});
			
			$( picker.append ).append( $clone );
		}
			
		var offset = getOffset( picker, $(this) );
		$picker.css( offset ).toggleClass('active');
		$picker.outerWidth( $closest.outerWidth() );
		$closest.toggleClass('active');
		
		return false;
	});
	
	function getOffset( picker, input ) 
	{
		var $picker 	=  $( '#'+ picker.id );
        var extraY 		= ( $('body').hasClass('wp-admin') ) ? /*$( '#wpadminbar' ).outerHeight()*/ 0 : 0;
        var dpWidth 	= $picker.outerWidth();
        var dpHeight	= $picker.outerHeight();
        var inputHeight = input.outerHeight();
        var $append 	= $( picker.append );
        var viewWidth 	= $append.outerWidth() + $append.scrollLeft();
        var viewHeight	= $append.outerHeight() + $append.scrollTop();
        var offset 		= input.offset();

        offset.top 	+= inputHeight /*+ parseInt( input.closest('[data-tify_control="dropdown"]').css( "border-top-width" ) )*/;
        offset.left -= parseInt( input.closest('[data-tify_control="dropdown"]').css( "border-left-width" ) );

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
	
	$( window ).on( 'scroll.tify_control.dropdown', function(e){		
		$( '[data-tify_control="dropdown"].active' ).each( function(){
			var $closest 	= $(this);	
			var picker		= $closest.data( 'picker' );
			var $picker 	=  $( '#'+ picker.id );
			var offset 		= getOffset( picker, $(this) );	

			$picker.css( offset );
			$picker.outerWidth( $closest.outerWidth() );
		});
	});
});