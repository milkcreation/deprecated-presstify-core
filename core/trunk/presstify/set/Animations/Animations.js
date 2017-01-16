jQuery( document ).ready( function($) {
	/* Détection de l'élément dans la zone visible */
	function inViewport($ele) {	
	    var lBound = $(window).scrollTop(),
	        uBound = lBound + $(window).height(),
	        top = $ele.offset().top,
	        bottom = top + $ele.outerHeight(true);

	    return (top > lBound && top < uBound)
	        || (bottom > lBound && bottom < uBound)
	        || (lBound >= top && lBound <= bottom)
	        || (uBound >= top && uBound <= bottom);
	}
	
	/* Récupération de la cible */
	function getScrollTarget($ele) {
		if ( typeof $ele.data('animate-scroll-target') === 'string' &&  $( $ele.data('animate-scroll-target') ) ) {
			return $( $ele.data('animate-scroll-target') );
		} else if ( typeof $ele.data('animate-scroll-position') === 'number' ) {
			return $ele.data('animate-scroll-position');
		} else {
			return $ele;
		}
	}
	
	/* Vérifie si la cible est atteinte au scroll */
	function isScrollTargetReached($ele) {
		var value;
		switch(typeof $ele) {
			case 'object':
				value = inViewport($ele);
				break;
			case 'number' :
				value = ($(window).scrollTop() >= $ele);
				break;
			default:
				value = false;
				break;
		}
		return value;
	}
	
	/* Lancement des animations au scroll */
	$( window ).scroll(function() {
		// Animations tiFy
		$('.tiFy-animate--scroll:not(.tiFy-isAnimated)').each(function(index) {
			var $target = getScrollTarget($(this));
			if ( isScrollTargetReached($target) ) {
				$( this ).addClass('tiFy-isAnimated');
			}
		});
		// Animations Animate.css
		$('.animateCSS-scroll:not(.animated)').each(function(index) {
			var $target = getScrollTarget($(this)),
				animation = ( typeof $(this).data('scroll-animation') === 'string' ) ? $(this).data('scroll-animation') : '';

			if ( isScrollTargetReached($target) ) {					
				$(this).addClass('animated'+' '+ animation);
			}
		});
	});
	
	/* Initialisation du lancement des animations au scroll */
	$( window ).load( function(){
		$(this).trigger( 'scroll');
	});
});