<?php
namespace tiFy\Core\Taboox\Post\TaxonomySelect\Admin;

use tiFy\Core\Taboox\Admin;

class TaxonomySelect extends Admin
{
	private static $Instance;
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args(
			$this->args,
			array(
				'id'				=> 'tify_taboox_taxonomy_select-'. ++self::$Instance,	
				'selector' 			=> 'checkbox',
				'taxonomy' 			=> '',
				'show_option_none'	=> __( 'Aucun', 'tify' ),
				'col'				=> 4
			)
		);
	}
	
	/* = MISE EN FILE DE SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'tiFyTabooxPostTaxonomySelectAdmin', $this->Url .'/TaxonomySelect.css' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{
		extract( $this->args );
		
		$taxonomies = get_terms( 
			array( 
				'taxonomy' 	=> $taxonomy, 
				'orderby'	=> 'title', 
				'order'=>'ASC', 
				'get' => 'all' 
			) 
		);
		
		if( is_wp_error( $taxonomies ) )
			return;
		
		if( $selector === 'radio' ) :
			$walker = new \tiFy\Lib\Walkers\Taxonomy_RadioList;
		else :
			$walker = new \tiFy\Lib\Walkers\Taxonomy_CheckboxList;
		endif;

		$args = array( 
			"taxonomy" 	=> $taxonomy, 
			"disabled" 	=> false, 
			"list_only" => false 
		);
		$args['selected_cats'] = wp_get_object_terms( $post->ID, $taxonomy, array_merge( $args, array( 'fields' => 'ids' ) ) );

		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"tify_taboox_taxonomy_select tify_taboox_taxonomy_select-{$taxonomy}\">\n";
		$output .= "\t<ul class=\"list-{$col}-items-by-row\">\n";
		$output .= call_user_func_array( array( $walker, 'walk' ), array( $taxonomies, 0, $args ) );
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
		
		echo $output;
	}
}	