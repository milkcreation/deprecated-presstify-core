<?php
namespace tiFy\Set\Animations;

/**
 * 
 * Usage :
 * 		1) Pour les animations au scroll :
 * 			- data-animate-scroll-target="#IdentifiantDeLaCible" sur le tag html concerné par l'animation si le déclenchement 
 * 			  de l'animation doit avoir lieu lors de la détection de la cible dans la zone visible de la fenêtre.
 * 			- data-animate-scroll-position="PositionEnPixels" (ex: 300) sur le tag html concerné par l'animation si le déclenchement 
 * 			  de l'animation doit avoir lieu à une position de scroll précise.
 * 			- Si aucun des 2 précédents paramètres n'est configuré, l'animation se déclenche lors de la détection de l'élément concerné
 * 			  dans la zone visible de la fenêtre.
 * 		1) Animations tiFy :
 * 			  - Ajouter les classes sur le tag html concerné par l'animation (exemple : tiFy-animate + tiFy-animate--hover + tiFy-scaleUp) 
 * 				produira un effet de grossissement de l'élément d'une durée d'une seconde au survol de celui-ci.
 * 			  - Si l'animation se passe au scroll, ajouter la classe "tiFy-animate--scroll".
 * 		2) Animations Animate.css :
 * 				@see https://github.com/daneden/animate.css
 * 			  - Ajouter les classes sur le tag html concerné par l'animation.
 * 			  - Si l'animation se passe au scroll, ajouter la classe "animateCSS-scroll" et déclarer sur le tag concerné l'attribut
 * 				data-scroll-animation="ClasseDeLAnimation".
 * @author TB Digital
 * Hover
 * @see https://github.com/IanLunn/Hover
 */
class Animations extends \tiFy\Set\Factory
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'wp_enqueue_scripts',
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'wp_enqueue_scripts' => 25
	);
	
	/* = DECLENCHEURS = */
	/** == Mise en file des scripts de l'interface utilisateur == **/
	public function wp_enqueue_scripts()
	{
		wp_enqueue_style( 'animate-css' );
		wp_enqueue_style( 'ThemetiFySetAnimations', static::getUrl( get_class() ) ."/Animations.css", array(), '170112' );
		wp_enqueue_script( 'ThemetiFySetAnimations', static::getUrl( get_class() ) ."/Animations.js", array( 'jquery' ), '170112', true );
			
		$output = "";
		foreach( range( 0, 5000, 100 ) as $time ) :
			$output .= ".tiFy-animateDuration--{$time}ms{-webkit-animation-duration:{$time}ms;animation-duration:{$time}ms;-webkit-transition-duration:{$time}ms;transition-duration:{$time}ms;} .tiFy-animateDelay--{$time}ms{-webkit-animation-delay:{$time}ms;animation-delay:{$time}ms;-webkit-transition-delay:{$time}ms;transition-delay:{$time}ms;}";		
		endforeach;
		
		wp_add_inline_style( 
			'ThemetiFySetAnimations', 
			$output
		);
	}
}