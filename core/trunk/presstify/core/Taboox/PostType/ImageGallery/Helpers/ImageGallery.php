<?php
namespace tiFy\Core\Taboox\PostType\ImageGallery\Helpers;

class ImageGallery extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'image_gallery';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );
    
	// Instance
	protected static $Instance;	
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
	    parent::__construct();	    
	    static::$Instance++;
	}
	
	/* = VÉRIFICATION = */
	public static function Has( $post_id = null, $args = array() )
	{
		return static::Get( $post_id, $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $post_id = null, $args = array() )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		// Traitement des arguments
		$defaults = array(			
			'name'				=> '_tify_taboox_image_gallery',
		    // ID HTML
		    'id'				=> 'tify_taboox_image_gallery-'. static::$Instance++,
			// Class HTML
		    'class'				=> '',		    
		    // Taille des images
			'size' 				=> 'full',
		    // Nombre de vignette maximum
			'max'				=> -1,
			// Attribut des vignettes
			'attrs'				=> array( 'title', 'link', 'caption' ),
			// Options
			'options'		    => array() 
		);		
		$args = wp_parse_args( $args, $defaults );
		
		
		if( ! $attachment_ids = get_post_meta( $post_id, $args['name'], true ) )
		    return;
		
		$slides = array();    
		foreach( $attachment_ids as $attachment_id ) :
		  if( ! $attachment_id )
		      continue;
		  if( ! $post = get_post( $attachment_id ) )
		      continue;
		      
		  $slides[] =	array(
    			'src'		    => wp_get_attachment_image_url( $attachment_id, $args['size'] ),
    		    'alt'           => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
    		    'url' 			=> in_array( 'link', $args['attrs'] ) ? wp_get_attachment_image_url( $attachment_id, 'full' ) : '',
    			'title'			=> in_array( 'title', $args['attrs'] ) ? get_the_title( $attachment_id ) : '',
    			'caption'		=> in_array( 'caption', $args['attrs'] ) ? $post->post_content : ''			
          );    
		      
		endforeach;      
			
		$id = $args['id'];
		$class = $args['class'];
		$options = wp_parse_args( 
		    $args['options'], 
		    array(
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
				
		return compact( 'id', 'class', 'options', 'slides' );		    
	}
	
	/* = AFFICHAGE = */
	public static function Display( $post_id = null, $args = array(), $echo = true )
	{			
		// Bypass
		if( ! $slideshow = static::Get( $post_id, $args ) )
			return;			
				
		$output = tify_control_slider( $slideshow, false );
	
		if( $echo )
			echo $output;

		return $output;
	}
}