var tify_infinite_scroll_xhr, tify_infinite_scroll;
!( function( $, doc, win, undefined )
{	
	tify_infinite_scroll = function( handler, target )
	{
		
		$target = ( ! target ) ? $( handler ).prev() : $( target );
		$( window ).scroll( function( e ) {
			if( ( tify_infinite_scroll_xhr === undefined ) && ! $(this).hasClass( 'ty_iscroll_complete' ) && isScrolledIntoView( $( handler ) ) )
				 $( handler ).trigger( 'click' );
		});
	
		$( handler ).click( function(e){
			if( $(this).hasClass( 'ty_iscroll_complete' ) )
				return false;
			
			$target.addClass( 'ty_iscroll_load' );
			$( handler ).addClass( 'ty_iscroll_load' );
				
			var action		= $(this).data( 'action' ),
				query_args 	= $(this).data( 'query_args' ),
				before		= $(this).data( 'before' ),
				after		= $(this).data( 'after' ),
				per_page 	= $(this).data( 'per_page' ),
				template 	= $(this).data( 'template' ),
				from 		= $( '> *', $target ).size();				
				
			tify_infinite_scroll_xhr = $.post( 
				tify_ajaxurl,
				{ action: action, query_args : query_args, before : before, after : after , per_page : per_page, template: template, from : from },
				function( resp ){
					$target.removeClass( 'ty_iscroll_load' );
					$( handler ).removeClass( 'ty_iscroll_load' );	
						
					$target.append( resp );
					var complete = resp.match(/<!-- tiFy_Infinite_Scroll_End -->/);
					if( complete ){
						$target.addClass( 'ty_iscroll_complete' );
						$( handler ).addClass( 'ty_iscroll_complete' );
					}
					
					tify_infinite_scroll_xhr.abort();
					tify_infinite_scroll_xhr = undefined;
				}
			);
		});
	}
	
	function isScrolledIntoView( elem ) {
		var docViewTop = $(window).scrollTop();
		var docViewBottom = docViewTop + $(window).height();
		var elemOffset = 0;
		  
		if( elem.data('offset') != undefined )
			elemOffset = elem.data( 'offset' );
	
		var elemTop = $(elem).offset().top;
		var elemBottom = elemTop + $(elem).outerHeight(true);
	  
		if( elemOffset != 0 )
			if(docViewTop - elemTop >= 0)
				elemTop = $(elem).offset().top + elemOffset;
			else
				elemBottom = elemTop + $(elem).outerHeight(true) - elemOffset;
	  
		if( ( elemBottom <= docViewBottom ) && ( elemTop >= docViewTop ) )
			return true;
	}
})( jQuery, document, window, undefined );