<?php
/** 
 * USAGE :
 * - Méthode 1 (rapide et simple: les scripts sont chargés automatiquement)
 * 		<?php echo do_shortcode( 'tify_taboox_slideshow' );?>
 * - Méthode 2
 * 		<?php tify_taboox_slideshow_display();?>
 */
namespace tiFy\Core\Taboox\Option\Slideshow\Helpers;

class Slideshow extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array( 
		'wp_loaded'
	);
	// Ordres de priorité d'exécution des actions
	protected $tFyAppActionsPriority	= array(
		'wp_loaded' => 11	
	);
	
	// Identifiant des fonctions
	protected $ID 				= 'slideshow';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );
		
	// Attributs de configuration
	public static $Attrs		= array();
	
	// Vignettes
	public static $Slides		= array();
	
	// Instance
	public static $Instance		= 0;
	
	/* = DECLENCHEURS = */
	final public function wp_loaded()
	{
	  	add_shortcode( 'tify_taboox_slideshow', function( $atts ){		    			
			return static::Display( $atts, false );		
		});
	}	
	
	/* = DÉFINITION DE LA CONFIGURATION = */
	public static function setAttrs( $args = array() )
	{
		if( ! empty( static::$Attrs ) )
			return static::$Attrs;		
				
		return static::$Attrs = static::parseArgs( $args );
	}
	
	/* = TRAITEMENT DES ARGUMENTS = */
	public static function parseArgs( $args = array() )
	{
		static::$Instance++;
		
		$defaults = array(			
			'name'				=> 'tify_taboox_slideshow',
		    // ID HTML
		    'id'				=> 'tify_taboox_slideshow-'. static::$Instance,
			// Class HTML
		    'class'				=> '',		    
		    // Taille des images
			'size' 				=> 'full',
		    // Nombre de vignette maximum
			'max'				=> -1,
			// Attribut des vignettes
			'attrs'				=> array( 'title', 'link', 'caption', 'planning' ),
			// Options
			'options'		=> array(
			    'engine'             => 'tify',
				// Résolution du slideshow
				'ratio'				=> '16:9',			
				// Navigation suivant/précédent
				'arrow_nav'			=> true,
				// Vignette de navigation
				'tab_nav'			=> true,
				// Barre de progression
				'progressbar'		=> false
			)
		);		
				
		// Traitement des options
		$name = isset( $args['name'] ) ? $args['name'] : $defaults['name'];
	  	if( ! isset( $args['options'] ) && ( $db = get_option( $name, false ) ) && isset( $db['options'] ) )
			$args['options'] = $db['options'];
			
		foreach( (array) $defaults['options'] as $k => $v ) :
			if( ! isset( $args['options'][$k] ) )
				$args['options'][$k] = $v;
		endforeach;
		
		$args = wp_parse_args( $args, $defaults );

		// Classe
		$args['class'] = 'tify_taboox_slideshow' .( $args['class'] ? ' '.$args['class'] : '' );
		
		// Traitement du moteur d'affichage
		if( is_string( $args['options']['engine'] ) )
			$args['options']['engine'] = array( $args['options']['engine'], array() );
		
		return $args;
	}
	
	/* = RECUPERATION D'UN ARGUMENTS = */
	public static function getAttrs( $attr = null )
	{
		if( is_null( $attr ) )
			return static::$Attrs;		
		elseif( isset( static::$Attrs[$attr] ) )		
			return static::$Attrs[$attr];
	}
		
	/* = VÉRIFICATION = */
	public static function Has( $args = array() )
	{
		return static::Get( $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $args = array() )
	{
	    $args = static::setAttrs( $args );	

		if( ! $slideshow = get_option( $args['name'], false ) )
			return array( 'options' => $args['options'], 'slide' => array() );
		
		$slide = isset( $slideshow['slide'] ) ? $slideshow['slide'] : array();

		if( ! empty( $slide ) ) :
			$slide = mk_multisort( $slide );
		endif;
		
		$slides = array();
		
		foreach( (array) $slide as $i => $s ) :
			if( empty( $s['attachment_id'] ) ) :
				unset( $slide[$i] );
				continue;
			endif;
			
			if( in_array( 'planning', $args['attrs'] ) ) :
				if( ! empty( $s['planning']['from'] ) && ( current_time( 'U' ) < mysql2date( 'U', $s['planning']['start'] ) ) ) :
					unset( $slide[$i] );
					continue;
				endif;
				if( ! empty( $s['planning']['to'] ) && ( current_time( 'U' ) > mysql2date( 'U', $s['planning']['end'] ) ) ) :
					unset( $slide[$i] );
					continue;
				endif;
			endif;
									
			$slides[] =	wp_parse_args( 
			    array(
    				'src'		    => wp_get_attachment_image_url( $s['attachment_id'], $args['size'] ),
    			    'alt'           => esc_attr( ( $alt = get_post_meta( $s['attachment_id'], '_wp_attachment_image_alt', true ) ) ? $alt : get_the_title( $s['attachment_id'] ) ),
    			    'url' 			=> ( in_array( 'link', $args['attrs'] ) && ! empty( $s['clickable'] ) && ! empty( $s['url'] ) ) ? $s['url'] : '',
    				'title'			=> ( in_array( 'title', $args['attrs'] ) && ! empty( $s['title'] ) ) ? $s['title'] : '',
    				'caption'		=> ( in_array( 'caption', $args['attrs'] ) && ! empty( $s['caption'] ) ) ? $s['caption'] : '',				
    			),
			    $s
		    );
		endforeach;
		
		static::$Slides = $slide;		
		
		$id = $args['id'];
	    $class = $args['class'];
		$options = $args['options'];
		
		return compact( 'id', 'class', 'slides', 'options' );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $args = array(), $echo = true )
	{			
		// Bypass
		if( ! $slideshow = static::Get( $args ) )
			return;			
						
		$output = tify_control_slider( $slideshow, false );
	
		if( $echo )
			echo $output;

		return $output;
	}
}