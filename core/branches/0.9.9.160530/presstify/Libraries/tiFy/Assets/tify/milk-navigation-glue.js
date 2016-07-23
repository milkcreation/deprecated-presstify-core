!( function( $, doc, win, undefined ){
	var $window = $(window), $moveable = $( '#primary-navigation' ), $glue = $( '#jumbotron' ); 
	
	var fixedTop = false, fixedBottom = false, moveable = false;
	
	$(window).scroll( function (e) {
        if ( $(this).scrollTop() >  $glue.position().top ){      	
			if( fixedTop )return false;
			fixedTop = true, fixedBottom = false, moveable = false;
			$moveable.css({ top:0, bottom: 'auto', 'position' : 'fixed' });
			// console.log( 'FixedTop' );
		} else if ( $(this).scrollTop() <  $glue.position().top - $(this).height() + $moveable.height() ) {
			if( fixedBottom )return false;
			fixedTop = false, fixedBottom = true, moveable = false;
			$moveable.css({ top:'auto', bottom:0, position:'fixed' });
			// console.log( 'FixedBottom' );
		} else {
			fixedTop = false, fixedBottom = false, moveable = true;
			$moveable.css({ top:'auto', bottom:'auto', position:'absolute' });
			// console.log( 'move' );
		}
    });
})( jQuery, document, window, undefined );