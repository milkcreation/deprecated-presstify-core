jQuery( document ).ready( function( $ ) {
	var tiFySidebar = {}
	
	$( window ).resize(function() {
		$( '.tiFySidebar' ).each( function(){
			var width = $(this).outerWidth();	
			var pos = $(this).data( 'pos' );
			var sign;
			
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
			
			$( '.tiFySidebar-body--'+ pos +'Opened .tiFySidebar-pushed' ).css({
				'-webkit-transform' : 'translateX('+ sign + width +'px)',
				'-moz-transform'    : 'translateX('+ sign + width +'px)',
				'-ms-transform'     : 'translateX('+ sign + width +'px)',
				'-o-transform'      : 'translateX('+ sign + width +'px)',
				'transform' 		: 'translateX('+ sign + width +'px)' 
			});		
			
			$( '.tiFySidebar-body--'+ pos +'Closed .tiFySidebar-pushed' ).css({
				'-webkit-transform' : 'translateX(0px)',
				'-moz-transform'    : 'translateX(0px)',
				'-ms-transform'     : 'translateX(0px)',
				'-o-transform'      : 'translateX(0px)',
				'transform' 		: 'translateX(0px)' 
			});
		});
	}).trigger( 'resize' );
	
	$( document ).on( 'click.tify_sidebar', '[data-toggle="tiFySidebar"]', function(e){
		e.preventDefault();
		
		var pos = $(this).data('target');
		var width = tiFySidebar[pos].width;
		var sign = tiFySidebar[pos].sign;
		
		if( $( document.body ).hasClass( 'tiFySidebar-body--'+ pos +'Opened' ) ) {		
			$( document.body ).removeClass( 'tiFySidebar-body--'+ pos +'Opened' ).addClass( 'tiFySidebar-body--'+ pos +'Closed' );
			
			$( '.tiFySidebar-body--'+ pos +'Closed .tiFySidebar-pushed' ).css({
				'-webkit-transform' : 'translateX(0px)',
				'-moz-transform'    : 'translateX(0px)',
				'-ms-transform'     : 'translateX(0px)',
				'-o-transform'      : 'translateX(0px)',
				'transform' 		: 'translateX(0px)' 
			});
		} else {
			$( document.body ).removeClass( 'tiFySidebar-body--'+ pos +'Closed' ).addClass( 'tiFySidebar-body--'+ pos +'Opened' );
		
			$( '.tiFySidebar-body--'+ pos +'Opened .tiFySidebar-pushed' ).css({
				'-webkit-transform' : 'translateX('+ sign + width +'px)',
				'-moz-transform'    : 'translateX('+ sign + width +'px)',
				'-ms-transform'     : 'translateX('+ sign + width +'px)',
				'-o-transform'      : 'translateX('+ sign + width +'px)',
				'transform' 		: 'translateX('+ sign + width +'px)' 
			});
		}
	});
});