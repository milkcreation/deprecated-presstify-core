<?php
namespace tiFy\Components\Lightbox;

use \tiFy\Environment\Component;

final class Lightbox extends Component
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'wp_enqueue_scripts'
	);
		
	/* = DECLENCHEURS = */
	public function wp_enqueue_scripts()
	{
		$args = wp_parse_args(
			self::getConfig(),
			array(
				'theme'				=> 'base',
				'overlay'			=> true,	// Couleur de fond
				'spinner'			=> true,	// Indicateur de chargement
				'close_button'		=> true,	// Bouton de fermeture
				'caption'			=> true,	// Légende (basé sur le alt de l'image)
				'navigation'		=> true,	// Flèche de navigation suivant/précédent 
				'tabs'				=> true,	// Onglets de navigation
				
				'keyboard'      	=> true,
				'overlay_close' 	=> false, 
				'animation_speed'	=> 250
			)
		);	
		
		if( $args['theme'] === 'base' ) :
			wp_enqueue_style( 'tiFyComponentsLightboxThemeBase', self::getUrl() .'/theme/base.css', array(), '160902' );
		elseif( file_exists( self::getDirname() .'/theme/'. $args['theme'] .'.css' ) ) :
			wp_enqueue_style( 'tiFyComponentsLightboxTheme'. ucfirst( $args['theme'] ), self::getUrl() .'/theme/'. $args['theme'] .'.css', array(), '160902' );
		elseif( ! empty( $args['theme'] ) ):
			
		endif;
		
		wp_enqueue_script( 'tiFyComponentsLightbox', self::getUrl() .'/Lightbox.js', array( 'imagelightbox' ), '160902', true );
		wp_localize_script( 
			'tiFyComponentsLightbox', 
			'tiFyLightbox', 
			$args
		);
	}
}