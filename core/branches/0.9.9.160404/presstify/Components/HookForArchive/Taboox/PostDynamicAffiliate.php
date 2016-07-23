<?php
namespace tiFy\Components\HookForArchive\Taboox;

use tiFy\Core\Taboox\Admin;

class PostDynamicAffiliate extends Admin{
	/* = CHARGEMENT = */
	public function current_screen( $current_screen )
	{
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, '_'. $this->args['hook_post_type'] .'_for_'. $current_screen->id );
	}

	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{
		$hook_post_type = $this->args['hook_post_type'];
		$name 			= '_'. $hook_post_type .'_for_'. $post->post_type;
		$hook_id 		= $this->args['hook_id'];
		$value 			= ( $_value = tify_get_post_meta( $post->ID, $name ) ) ? $_value : array();

		if( $this->args['display'] === 'dynamic' ) :
		$output = wp_dropdown_pages(
				array(
						'name' 				=> "tify_post_meta[multi][{$name}][]",
						'child_of'			=> $hook_id,
						'post_type' 		=> $hook_post_type,
						'selected' 			=> current( $value ),
						'show_option_none' 	=> __( 'Aucune page d\'affichage', 'tify' ),
						'sort_column'  		=> 'menu_order',
						'echo'				=> 0
				)
				);
		elseif( $this->args['display'] === 'dynamic-multi' ) :
		$args = array(
				'sort_order' 	=> 'asc',
				'sort_column' 	=> 'menu_order',
				'hierarchical' 	=> 1,
				'authors' 		=> '',
				'child_of' 		=> $hook_id,
				'parent' 		=> -1,
				'offset' 		=> 0,
				'post_type' 	=> 'page',
				'post_status' 	=> 'publish'
		);
		if( ! $pages = get_pages( $args ) )
			return;

			$output  = "<ul>";
			foreach( $pages as $page )
				$output .= "<li><label><input type=\"checkbox\" name=\"tify_post_meta[multi][$name][". ( ( $meta_id = array_search( $page->ID, $value ) ) ? $meta_id : uniqid() )."]\" value=\"{$page->ID}\" ". ( checked( in_array( $page->ID, $value ), true, false ) ) ."> {$page->post_title}</label></li>";
				$output  .= "</ul>";
				endif;
				echo $output;
	}
}