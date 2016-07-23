<?php
/**
 * Usage :
 * 
 	add_filter( 'tify_custom_column_post_order', '{function_hook_name}' );
	function function_hook_name( $post_types ){
		return $post_types += array( '{post_type}' => {col_position} ); 
	}
 */

class tiFy_custom_column_order{
	var $post_types;
	
	/**
	 * Initialisation
	 */
	function __construct(){
		// Action
		add_action( 'admin_init', array( $this, 'admin_init' ) );	
	}
	
	/**
	 * Instanciation de la colonne
	 */
	function admin_init(){
		// Type de post actif
		$this->post_types = apply_filters( 'tify_custom_column_post_order', array( ) );
		
		// Activation de la colonne pour les types de post concernés
		foreach( $this->post_types as $post_type => $position ) :
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'columns' ) );
			add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'sortable_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );
		endforeach;	
		
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}
	
	/**
	 * Entête et position de la colonne
	 */
	function columns( $columns ){
		if( $position = $this->post_types[ get_current_screen()->post_type ] ) :
			$newcolumns = array(); $n = 0;
			foreach( $columns as $key => $column ) :
				if( $n == $position ) 
					$newcolumns['menu_order'] = __( 'Ordre d\'aff.', 'tify' );
				$newcolumns[$key] = $column;
				$n++;				
			endforeach;
			$columns = $newcolumns;
		else :
			$columns['menu_order'] = __( 'Ordre d\'aff.', 'tify' );
		endif;

		return $columns;
	}
	
	/**
	 * Rend la colonne triable
	 */
	function sortable_columns( $columns ) {
	    $columns['menu_order'] = 'menu_order';
	 
	    return $columns;
	}
	
	/**
	 * Affichage des données de la colonne
	 */
	function custom_column( $column, $post_id ){
		if( $column != 'menu_order' )
			return $column;
		
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
	 * Gestion du tri de la colonne
	 */
	function pre_get_posts( &$query ){
		// Bypass
		if( ! is_admin() ) 
			return;
		
		foreach( $this->post_types as $post_type => $position ) :
	 		if( $query->is_post_type_archive( $post_type ) ) :
				$query->set( 'orderby', $query->get('orderby', 'menu_order' ) );
				$query->set( 'order', $query->get('order', 'ASC' ) );
			endif;
		endforeach;
	}	
}
new tiFy_custom_column_order;