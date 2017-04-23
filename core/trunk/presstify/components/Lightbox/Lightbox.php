<?php
namespace tiFy\Components\Lightbox;

use \tiFy\Environment\Component;

final class Lightbox extends Component
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts'
	);
	
	// Filtres à déclencher
	protected $CallFilters				= array(
		//'the_content'
	);
		
	/* = DECLENCHEURS = */
	/** == == **/
	public function init()
	{		
		// Déclaration des thèmes
		foreach( glob( self::getDirname().'/theme/*.css' ) as $filename ) :
			wp_register_style( 'tiFyComponentsLightboxTheme'. ucfirst( basename( $filename, '.css' ) ), self::getUrl( get_class() ) .'/theme/'. basename( $filename ), array(), '160902' );
		endforeach;

		wp_register_script( 'tiFyComponentsLightbox', self::getUrl( get_class() ) .'/Lightbox.js', array( 'imageLightbox' ), '160902', true );
	}
	
	/** == == **/
	public function wp_enqueue_scripts()
	{
		$args = wp_parse_args(
			self::getConfig(),
			array(
				'theme'				=> 'dark',
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
		wp_enqueue_style( 'tiFyComponentsLightboxTheme'. ucfirst( $args['theme'] ) );
		wp_enqueue_script( 'tiFyComponentsLightbox' );
		wp_localize_script( 
			'tiFyComponentsLightbox', 
			'tiFyLightbox', 
			$args
		);
	}
	
	/** == == **/
	public function the_content( $content )
	{
		// Ajout via php de l'attribut de gestion des images des articles 
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		$document = new \DOMDocument();
		libxml_use_internal_errors(true);
		$document->loadHTML( utf8_decode( $content )	);
			
		foreach( $document->getElementsByTagName('a') as $link ) :
			if( ! $src = $link->getAttribute('href') )
				continue;
			if(  !preg_match( '/\.(?:jpe?g|png|gif)$/', $src ) )
				continue;			
			
			foreach( $link->getElementsByTagName('img') as $img ) :
				$link->setAttribute( 'data-role', 'tiFyLightbox-image' );
			endforeach;
		endforeach;
	      		
		return $document->saveHTML();
	}
}