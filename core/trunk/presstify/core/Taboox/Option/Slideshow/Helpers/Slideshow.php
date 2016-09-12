<?php
/** 
 * USAGE :
 * - Méthode 1 (rapide et simple: les scripts sont chargés automatiquement)
 * 		<?php echo do_shortcode( 'tify_taboox_slideshow' );?>
 * - Méthode 2
 * 		<?php tify_taboox_slideshow_display();?>
 */
namespace tiFy\Core\Taboox\Option\Slideshow\Helpers;

use tiFy\Core\Taboox\Helpers;

class Slideshow extends Helpers
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array( 
		'wp_loaded'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'wp_loaded' => 11	
	);
	
	// Identifiant des fonctions
	protected $ID 				= 'slideshow';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );
		
	// Configuration
	public static $Config		= array();
	
	// Vignettes
	public static $Slides		= array();
	
	// Instance
	public static $Instance		= 0;
	
	/* = DECLENCHEURS = */
	final public function wp_loaded()
	{
		wp_register_style( 'tify_taboox_slideshow', tify_style_get_src( 'tify-slideshow' ), tify_style_get_attr( 'tify-slideshow', 'deps' ), '160603' );
		wp_register_script( 'tify_taboox_slideshow', tify_script_get_src( 'tify-slideshow' ), tify_script_get_attr( 'tify-slideshow', 'deps' ), '160603', true );
		
		add_shortcode( 'tify_taboox_slideshow', function( $atts ){
			wp_enqueue_style( 'tify_taboox_slideshow' );
			wp_enqueue_script( 'tify_taboox_slideshow' );
			
			return static::Display( $atts, false );		
		});
	}	
	
	/* = DÉFINITION DE LA CoNFIGURATION = */
	public static function setConfig( $args = array() )
	{
		if( ! empty( static::$Config ) )
			return static::$Config;		
				
		return static::$Config = static::parseArgs( $args );
	}
	
	/* = TRAITEMENT DES ARGUMENTS = */
	public static function parseArgs( $args = array() )
	{
		static::$Instance++;
		
		$defaults = array(
			'id'				=> 'tify_taboox_slideshow-'. static::$Instance,
			'class'				=> '',
			'name'				=> 'tify_taboox_slideshow',
			// Nombre de vignette maximum
			'max'				=> -1,
			// Attribut des vignettes
			'attrs'				=> array( 'title', 'link', 'caption', 'planning' ),
			// Options
			'options'		=> array(
				// Moteur d'affichage
				'engine'			=> 'tify',
				// Résolution du slideshow
				'ratio'				=> '16:9',				
				// Taille des images
				'size' 				=> 'full',
				// Navigation suivant/précédent
				'nav'				=> true,
				// Vignette de navigation
				'tab'				=> true,
				// Barre de progression
				'progressbar'		=> false
			)
		);		
		$args = wp_parse_args( $args, $defaults );
		
		// Traitement des options
		if( ( $db = get_option( $args['name'], false ) ) && isset( $db['options'] ) )
			$args['options'] = wp_parse_args( $db['options'], $args['options'] );
		
		foreach( (array) $defaults['options'] as $k => $v ) :
			if( ! isset( $args['options'][$k] ) )
				$args['options'][$k] = $v;
		endforeach;
		
		// Classe
		$args['class'] = 'tify_slideshow tify_taboox_slideshow' .( $args['class'] ? ' '.$args['class'] : '' );
		
		// Traitement du moteur d'affichage
		if( is_string( $args['options']['engine'] ) )
			$args['options']['engine'] = array( $args['options']['engine'], array() );
		
		return $args;
	}
	
	/* = RECUPERATION D'UN ARGUMENTS = */
	public static function getConfig( $attr = null )
	{
		if( is_null( $attr ) )
			return static::$Config;		
		elseif( isset( static::$Config[$attr] ) )		
			return static::$Config[$attr];
	}
		
	/* = VÉRIFICATION = */
	public static function Has( $args = array() )
	{
		return static::Get( $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $args = array() )
	{
		$args = static::setConfig( $args );	
			
		if( ! $slideshow = get_option( $args['name'], false ) )
			return array( 'options' => $args['options'], 'slide' => array() );
		
		$slide = isset( $slideshow['slide'] ) ? $slideshow['slide'] : array();
		if( ! empty( $slide ) )
			$slide = mk_multisort( $slide );
		
		foreach( (array) $slide as $i => &$s ) :
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
									
			$slide[$i] = wp_parse_args( 
				$s, 
				array(
					'post_id'		=> 0,
					'title'			=> '',
					'caption'		=> '',
					'attachment_id'	=> 0,
					'clickable' 	=> 0,
					'url' 			=> '',
					'planning'		=> array(
						'from'	 		=> 0,
						'start'			=> '',
						'to'			=> 0,
						'end'			=> '',
					)
				)
			);
		endforeach;
		
		static::$Slides = $slide;		
		$options = $args['options'];
			
		return compact( 'options', 'slide' );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $args = array(), $echo = true )
	{			
		// Bypass
		if( ! $slideshow = static::Get( $args ) )
			return;			
		
		$conf = static::getConfig();	
			
		$class = 'tify_taboox_slideshow';
		if( ! empty( $conf['class'] ) )
			$class .= " ". $conf['class']; 
					
		$output  = "";
		$output .= "\n<div id=\"{$conf['id']}\" class=\"{$conf['class']}\" data-tify=\"slideshow\"";
		foreach( (array) $conf['options']['engine'][1] as $k => $v )
			$output .= " data-{$k}=\"{$v}\"";
		$output .= ">";
		$output .= static::{$conf['options']['engine'][0]}();
		$output .= "\n</div>";
				
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = MOTEURS = */
	public static function tify()
	{	
		$options 	= static::getConfig( 'options' );
		$slides 	= static::$Slides;
		
		// Calcul du ratio
		if( $options['ratio'] ) :
			list( $w, $h ) = preg_split( '/:/', $options['ratio'] );
			$percent = ceil( 100/$w * $h );
		endif;

		$output  = "";
		$output .= "<div class=\"viewer\">\n";		
		$output .= "\t<ul class=\"roller\">\n";
		// Vignettes
		foreach( (array) $slides as $slide ) :
			$output .= "\t\t<li>";
			
			// Dimensionneur
			if( $options['ratio'] )
				$output .= "\t\t\t<span style=\"display:block;content:'';padding-top:{$percent}%;\"></span>\n";
			
			// Lien
			if( in_array( 'link', static::getConfig( 'attrs' ) ) && ! empty( $slide['clickable'] ) )	
				$output .= "\t\t\t<a href=\"{$slide['url']}\"></a>\n";
			
			// Image
			$output .= "\t\t\t<figure";
			if( $options['ratio'] ) :
				$image =  wp_get_attachment_image_src( $slide['attachment_id'], $options['size'] );
				$output .= " style=\"background-image:url(".$image[0].")\">";
			else :
				$output .= ">". wp_get_attachment_image( $slide['attachment_id'], $options['size'], false, array( 'class' => 'item-image') );
			endif;			
			$output .= "\t\t\t</figure>\n";
			
			// Cartouche
			if( 
				( in_array( 'title', static::getConfig( 'attrs' ) ) && ! empty( $slide['title'] ) ) ||
				( in_array( 'caption', static::getConfig( 'attrs' ) ) && ! empty( $slide['caption'] ) )	
			) :
				$output .= "\t\t\t<section>\n";
				// Titre
				if( in_array( 'title', static::getConfig( 'attrs' ) ) && ! empty( $slide['title'] ) )
					$output .= "<h3 class=\"title\">". $slide['title'] ."</h3>";				
				// Légende
				if( in_array( 'caption', static::getConfig( 'attrs' ) ) && ! empty( $slide['caption'] ) )
					$output .= "<div class=\"caption\">". $slide['caption'] ."</div>";
				$output .= "\t\t\t</section>\n";
			endif;	
			$output .= "\t\t</li>\n";
		endforeach;
			
		$output .= "\t</ul>\n";
	
		// Navigation suivant/précédent
		if( $options['nav'] ) :
			$output .= "\t<a href=\"#\" class=\"navi prev\">&larr;</a>\n";
			$output .= "\t<a href=\"#\" class=\"navi next\">&rarr;</a>\n";
		endif;

		// Navigation tabulation
		if( $options['tab'] ) :
			reset( $slides );
			$output .= "\t<ul class=\"tabs\">";
			foreach( (array) $slides as $slide )
				$output .= "\t\t<li class=\"tab\"><a href=\"#\">".($slide['order'])."</a></li>\n";
			$output .= "\t</ul>";
		endif;
		
		if( $options['progressbar'] )
			$output .= "\t<div class=\"progressbar\"><span></span></div>\n";

		$output .= "</div>\n";
		
		return $output;
	}
}