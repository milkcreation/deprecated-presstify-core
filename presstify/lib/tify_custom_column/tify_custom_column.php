<?php
class tiFy_CustomColumn{
	/* = ARGUMENTS = */
	public	$post_type,
			$column,
			$label,
			$data_type, // meta | custom
			$position,
			$sortable;
	
	/* = CONSTRUCTEUR = */
	function __construct( $args = array() ){
		$this->parse_args( $args );
		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );	
	}
	
	/* = CONFIGURATION = */
	function parse_args( $args ){
		$defaults = array(
			'post_type' 	=> '',
			'column'		=> '',
			'column_label'	=> '',
			'column_type'	=> false,
			'position'		=> 0,
			'sortable'		=> false
		);
		$args = wp_parse_args( $args, $defaults );
		
		if( is_string( $args['post_type'] ) )
			$args['post_type'] = array_map( 'trim', explode( ',', $args['post_type'] ) );
		
		foreach( $args as $k => $v )
			$this->$k = $v;				
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	function wp_admin_init(){
		foreach( (array) $this->post_type as $post_type ) :
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'columns' ) );			
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_column' ),10, 2 );
			if( $this->sortable )
				add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'sortable_columns' ) );
		endforeach;
	}
	
	/** == Déclaration de la colonne == **/
	function columns( $columns ){
		if( $this->position ) :
			$newcolumns = array(); $n = 0;
			foreach( $columns as $key => $column ) :
				if( $n == $this->position ) 
					$newcolumns[$this->column] = $this->label;
				$newcolumns[$key] = $column;
				$n++;				
			endforeach;
			$columns = $newcolumns;
		else :
			$columns[$this->column] = $this->label;
		endif;

		return $columns;
	}
		
	/** == Affichage des données de la colonne == **/
	function custom_column( $column, $post_id ){
		// Bypass
		if( $column != $this->column )
			return $column;
		
		if( ! $this->data_type && ( $post = get_post( $post_id ) ) && isset( $post->{$this->column} ) )
			echo $post->{$this->column};
		elseif( $this->data_type === 'meta' )
			echo get_post_meta( $post_id, $this->column, true );
		elseif( $this->data_type === 'custom' )
			echo $this->custom_column_callback( $post_id ); 
		else
			_e( 'Indisp.', 'tify' );		
	}
	
	/** == == **/
	function custom_column_callback( $post_id ){}
	
	/**
	 * Rend la colonne triable
	 */
	function sortable_columns( $columns ) {
	    $columns['menu_order'] = 'menu_order';
	 
	    return $columns;
	}	
}