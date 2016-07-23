<?php
/**
 * Usage :
 * 
 	add_filter( 'tify_custom_column_post_thumbnail', '{function_hook_name}' );
	function function_hook_name( $post_types ){
		return $post_types += array( '{post_type}' => {col_position} ); 
	}
 */
class tiFy_admin_manager_post_thumbnail{
	var $post_types;
	
	/**
	 * Initialisation
	 */
	function __construct(){
		// Action
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );	
	}
	
	/**
	 * Instanciation de la colonne
	 */
	function admin_init(){
		// Type de post actif
		$this->post_types = apply_filters( 'tify_custom_column_post_thumbnail', array( ) );
		
		// Activation de la colonne pour les types de post concernés
		foreach( $this->post_types as $post_type => $position ) :
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );
		endforeach;	
	}
	
	/**
	 * Entête et position de la colonne
	 */
	function columns( $columns ){
		if( $position = $this->post_types[ get_current_screen()->post_type ] ) :
			$newcolumns = array(); $n = 0;
			foreach( $columns as $key => $column ) :
				if( $n == $position ) 
					$newcolumns['post_thumbnail'] = __( 'Mini.', 'tify' );
				$newcolumns[$key] = $column;
				$n++;				
			endforeach;
			$columns = $newcolumns;
		else :
			$newcolumns['post_thumbnail'] = __( 'Mini.', 'tify' );
		endif;

		return $columns;
	}
	
	/**
	 * Affichage des données de la colonne
	 */
	function custom_column( $column, $post_id ){
		if( $column != 'post_thumbnail' )
			return $column;
		
		$attachment_id = ( $_attachment_id = get_post_thumbnail_id($post_id) )? $_attachment_id : 0;
		// Vérifie l'existance de l'image 
		if( ( $attachment = wp_get_attachment_image_src( $attachment_id ) ) 
			&& isset( $attachment[0] ) 
			&& ( $path = tify_get_relative_url( $attachment[0] ) ) 
			&& file_exists( ABSPATH. $path ) )
			$thumb =  wp_get_attachment_image( $attachment_id, array( 80, 60 ), true );
		else
			$thumb = "<div style=\"background-color:#E4E4E4; height:80px; font-size:0.9em; line-height:80px; color:#999;\">". __( 'Indisponible', 'tify' ) ."</div>";		
		
		echo $thumb;		
	}
	
	/**
	 * Style de la colonne
	 */
	function admin_print_styles( ){
		if( ( get_current_screen()->base != 'edit' ) && ! in_array( get_current_screen()->post_type, $this->post_types ) )
			return;
		?><style type="text/css">
		.wp-list-table th#post_thumbnail,
		.wp-list-table td.post_thumbnail  {
			width:80px;
			text-align:center;
		}
		.wp-list-table td.post_thumbnail img{
			max-width: 80px;
			max-height: 60px;    		
		}
		</style><?php
	}
}
new tiFy_admin_manager_post_thumbnail;