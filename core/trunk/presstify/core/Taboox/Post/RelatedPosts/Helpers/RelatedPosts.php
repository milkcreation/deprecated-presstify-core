<?php
namespace tiFy\Core\Taboox\Post\RelatedPosts\Helpers;

use tiFy\Core\Taboox\Helpers;

class RelatedPosts extends Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'related_posts';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );
	
	// Attributs par défaut
	public static $DefaultAttrs	= array(
		'name'			=> '_tify_taboox_related_posts',
		'post_type' 	=> 'any',
		'post_status' 	=> 'publish',
		'max'			=> -1
	);
	
	/* = VÉRIFICATION = */
	public static function Has( $post = 0, $args = array() )
	{
		return static::Get( $post, $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $post = 0, $args = array() )
	{
		if( ! $post = get_post( $post ) )
			return;
		
		// Traitement des arguments
		$args = \wp_parse_args( $args, static::$DefaultAttrs );
		
		return \get_post_meta( $post->ID, $args['name'], true );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $post = 0, $args = array(), $echo = true )
	{		
		// Bypass
		if( ! $related_posts = static::Get( $post, $args ) )
			return;
			
		static $instances = 0; $instances++;
		
		$args = \wp_parse_args( $args, static::$DefaultAttrs );
		
		$output  = "";	
		$related_query = new \WP_Query( array( 'post_type' => 'any', 'post__in' => $related_posts, 'posts_per_page' => $args['max'], 'orderby' => 'post__in' ) );
		if( $related_query->have_posts() ) :
			$output .= "\n<div class=\"tify_taboox_related_posts\">";	
			$output .= "\n\t<ul class=\"roller\">";
			while( $related_query->have_posts() ): $related_query->the_post();
				$item  = "\n\t\t<li>";
				$item .= "\n\t\t\t<a href=\"".get_permalink()."\">";
				$item .= \get_the_post_thumbnail( get_the_ID(), 'thumbnail' );
				$item .= "\n\t\t\t\t<h3>".get_the_title( get_the_ID() )."</h3>";
				$item .= "\n\t\t\t</a>";
				$item .= "\n\t\t</li>";
				$output .= \apply_filters( 'tify_taboox_related_posts_item', $item, get_the_ID() );
			endwhile; ;
			$output .= "\n\t</ul>";
			$output .= "\n</div>";
		endif; \wp_reset_query();
		
		if( $echo )
			echo $output;
		
		return $output;	
	}
}