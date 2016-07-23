<?php
namespace tiFy\Core\Taboox\Helpers\Option;

use tiFy\Core\Taboox\Helpers;

class Slideshow extends Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'slideshow';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Display' );
		
	// Attributs
	public static $Attrs		= array();
	
	// Instance
	public static $Instance		= 0;
	
	/* = ATTRIBUTS PAR DEFAUT = */
	public static function getAttrs()
	{
		if( ! empty( self::$Attrs ) )
			return self::$Attrs;
		
		self::$Instance++;	
		return self::$Attrs	= array(
			'id'			=> 'tify_taboox_slideshow-'. self::$Instance,
			'class'			=> '',
			'name'			=> 'tify_taboox_slideshow',
			// Moteur
			'engine'		=> 'tify',
			// Options d'affichage
			'options'		=> array(
				// Nombre de vignette maximum
				'max'				=> -1,
				// Attribut des vignettes
				'attrs'				=> array( 'title', 'link', 'caption', 'planning' ),
				// Taille des images
				'size' 				=> 'full',
				// Format des images
				'background-image'	=> true,
				// Navigation suivant/précédent
				'nav'				=> true,
				// Vignette de navigation
				'tab'				=> true,
				// Barre de progression
				'progressbar'		=> false
			),
				
			'echo' 			=> 1
		);
	}
		
	/* = VÉRIFICATION = */
	public static function Has( $args = array() )
	{
		return self::Get( $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $args = array() )
	{
		$attrs = wp_parse_args( $args, self::getAttrs() );	
			
		if( ! $slideshow =  get_option( $attrs['name'], false ) )
			return array( 'options' => $attrs['options'], 'slide' => array() );
		
		$slide = isset( $slideshow['slide'] ) ? $slideshow['slide'] : array();
		if( ! empty( $slide ) )
			$slide = mk_multisort( $slide );
		
		foreach( (array) $slide as $i => &$s ) :
			if( empty( $s['attachment_id'] ) ) :
				unset( $slide[$i] );
				continue;
			endif;
			
			if( in_array( 'planning', $attrs['options']['attrs'] ) ) :
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

		$options = wp_parse_args( maybe_unserialize( $slideshow['options'] ), $attrs['options'] );
			
		return compact( 'options', 'slide' );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $args = array() )
	{	
		// Bypass
		if( ! $slideshow = self::Get( $args ) )
			return;			
			
		$attrs = self::getAttrs();		
				
		$class = 'tify_taboox_slideshow';
		if( ! empty( $attrs['class'] ) )
			$class .= " ". $attrs['class'];
		
		$output  = "";
		$output .= "\n<div id=\"{$attrs['id']}\" class=\"{$class}\">";
		$output .= self::{$attrs['engine']}( $slideshow['slide'], $slideshow['options'] );
		$output .= "\n</div>";
		
		$output = apply_filters( 'tify_taboox_slideshow_display', $output, $slideshow, $attrs );
		
		if( $attrs['echo'] )
			echo $output;
		else
			return $output;
	}
	
	/* = MOTEURS = */
	public static function tify( $slides, $options )
	{	
		$output  = "";
		$output .= "\n\t<div class=\"viewer\">";
		
		$output .= "\n\t\t<ul class=\"roller\">";
		// Vignettes
		foreach( (array) $slides as $slide ) :
			$output .= "\n\t\t\t<li>";
			
			if( in_array( 'link', $options['attrs'] ) && ! empty( $slide['clickable'] ) )	
				$output .= "\n\t\t\t<a href=\"{$slide['url']}\">";
			
			if( $options['background-image'] ) :
				$image =  wp_get_attachment_image_src( $slide['attachment_id'], $options['size'] );
				$output .= "<div class=\"item-image\" style=\"background-image:url(".$image[0].")\"></div>";
			else :
				$output .= wp_get_attachment_image( $slide['attachment_id'], $options['size'], false, array( 'class' => 'item-image') );
			endif;
			
			if( in_array( 'link', $options['attrs'] ) && ! empty( $slide['clickable'] ) )
				$output .= "\n\t\t\t\t</a>";
			
			if( in_array( 'title', $options['attrs'] ) && ! empty( $slide['title'] ) )
				$output .= "<h3 class=\"title\">". $slide['title'] ."</h3>";
			
			// Légende de la vignette
			if( in_array( 'caption', $options['attrs'] ) && ! empty( $slide['caption'] ) )
				$output .= "<div class=\"caption\">". $slide['caption'] ."</div>";
				
			$output .= "\n\t\t\t</li>";
		endforeach;
			
		$output .= "\n\t\t</ul>";
	
		// Navigation suivant/précédent
		if( $options['nav'] ) :
			$output .= "\n\t\t<a href=\"#\" class=\"nav prev\">&larr;</a>";
			$output .= "\n\t\t<a href=\"#\" class=\"nav next\">&rarr;</a>";
		endif;

		// Navigation tabulation
		if( $options['tab'] ) :
			reset( $slides );
			$output .= "\n\t\t<ul class=\"tabs\">";
			foreach( (array) $slides as $slide )
				$output .= "\n\t\t<li class=\"tab\"><a href=\"#\">".($slide['order'])."</a></li>";
			$output .= "\n\t\t</ul>";
		endif;
		
		if( $options['progressbar'] )
			$output .= "\n\t\t<div class=\"progressbar\"><span></span></div>";

		$output .= "\n\t</div>";
		
		$output = apply_filters( 'tify_taboox_slideshow_engine_tify', $output, $slides, $options );
		
		return $output;
	}
}