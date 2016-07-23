<?php
namespace tiFy\Components\CustomColumns\Column;

use tiFy\Components\CustomColumns\Factory;

class MenuOrder extends Factory
{
	/* = CONSTRUCTEUR = */
	public function __construct( $args = array() )
	{ 
		$defaults = array(
			'title'		=> 	__( 'Ordre d\'affich.', 'tify' ),
			'position'	=> 2
		);		
		$args = wp_parse_args( $args, $defaults );
		
		parent::__construct( $args );
		//add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}
			
	/* = Affichage des données de la colonne = */
	final public function Content( $column, $post_id ){	
		$level = 0;
		$post = get_post($post_id);
		if ( 0 == $level && (int) $post->post_parent > 0 ) :
			$find_main_page = (int) $post->post_parent;
			while ( $find_main_page > 0 ) :
				$parent = get_post( $find_main_page );

				if ( is_null( $parent ) )
					break;

				$level++;
				$find_main_page = (int) $parent->post_parent;
			endwhile;
		endif;
		$_level = "";
		for( $i=0; $i<$level; $i++ ) :
			$_level .= "<strong>&mdash;</strong> ";
		endfor;
		echo $_level.get_post( $post_id )->menu_order;		
	}
	
	/**
	 * Rend la colonne triable
	 * @todo
	 
	function sortable_columns( $columns ) {
		$columns['menu_order'] = 'menu_order';
	
		return $columns;
	}*/
	
	/**
	 * Gestion du tri de la colonne
	 * @todo
	
	function pre_get_posts( &$query ){
		// Bypass
		if( ! is_admin() ) 
			return;
		
		foreach( $this->PostTypes as $post_type ) :
	 		if( $query->is_post_type_archive( $post_type ) ) :
				$query->set( 'orderby', $query->get( 'orderby', 'menu_order' ) );
				$query->set( 'order', $query->get( 'order', 'ASC' ) );
			endif;
		endforeach;
	} */	
}