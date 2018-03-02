jQuery( document ).ready( function( $ ) {
	var tiFySidebar = {}
	var pushedElementCss = function( $el, status, pos, sign, width ){
		var ratio = $el.data('ratio') ? $el.data('ratio') : 1;
		
		if( status === 'opened' ){
			$el.css({
				'-webkit-transform' : 'translateX('+ sign + ( width*ratio ) +'px)',
				'-moz-transform'    : 'translateX('+ sign + ( width*ratio ) +'px)',
				'-ms-transform'     : 'translateX('+ sign + ( width*ratio ) +'px)',
				'-o-transform'      : 'translateX('+ sign + ( width*ratio ) +'px)',
				'transform' 		: 'translateX('+ sign + ( width*ratio ) +'px)' 
			});
		} else if( status === 'closed' ){		
			$el.css({
				'-webkit-transform' : 'translateX(0px)',
				'-moz-transform'    : 'translateX(0px)',
				'-ms-transform'     : 'translateX(0px)',
				'-o-transform'      : 'translateX(0px)',
				'transform' 		: 'translateX(0px)' 
			});
		}
	};
	
	$( window ).resize(function() {
		$( '.tiFySidebar' ).each( function(){
			var width = $(this).outerWidth(),
				pos = $(this).data( 'pos' ),
				sign, status;
			
			tiFySidebar[pos] = { width: width };
			switch( pos ){
				case 'left' :
					tiFySidebar[pos]['sign'] = '+';
					sign = '+';
					break;
				case 'right' :
					tiFySidebar[pos]['sign'] = '-';
					sign = '-';
					break;
			}
			
			if( $( document.body ).hasClass( 'tiFySidebar-body--'+ pos +'Opened' ) ) {		
				status = 'opened';
			} else {
				status = 'closed';
			}
			
			$( '.tiFySidebar-pushed' ).each( function(){
				pushedElementCss( $(this), status, pos, sign, width );
			});
		});
	}).trigger( 'resize' );
	
	$( document ).on( 'click.tify_sidebar', '[data-toggle="tiFySidebar"]', function(e){
		e.preventDefault();
		
		var pos = $(this).data('target'),
			width = tiFySidebar[pos].width,
			sign = tiFySidebar[pos].sign,
			status;
		
		if( $( document.body ).hasClass( 'tiFySidebar-body--'+ pos +'Opened' ) ) {		
			$( document.body ).removeClass( 'tiFySidebar-body--'+ pos +'Opened' ).addClass( 'tiFySidebar-body--'+ pos +'Closed' );
			status = 'closed';
		} else {
			$( document.body ).removeClass( 'tiFySidebar-body--'+ pos +'Closed' ).addClass( 'tiFySidebar-body--'+ pos +'Opened' );
			status = 'opened';
		}
		
		$( '.tiFySidebar-pushed' ).each( function(){
			pushedElementCss( $(this), status, pos, sign, width );
		});		
	});
	
	$( document ).on( 'click', function(e) {
		var pos = $( '.tiFySidebar' ).data( 'pos' );
		
		if( ( ! $( e.target ).closest( '.tiFySidebar' ).length ) && ! $( e.target ).closest( '[data-toggle="tiFySidebar"]' ).length && ( $( e.target ).data( 'toggle' ) != 'tiFySidebar' ) && $( 'body' ).hasClass( 'tiFySidebar-body--'+ pos +'Opened' ) ){			
			$( 'body' ).removeClass( 'tiFySidebar-body--'+ pos +'Opened' ).addClass( 'tiFySidebar-body--'+ pos +'Closed' );
			$( '.tiFySidebar-pushed' ).each( function(){
				pushedElementCss( $(this), 'closed', pos, '+', 0 );
			});	
		}
		return true;
	});
});