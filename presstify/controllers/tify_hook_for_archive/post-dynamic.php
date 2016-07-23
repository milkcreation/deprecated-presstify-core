<?php
class tiFy_HookForArchive_PostDynamic extends tiFy_HookForArchive_Post{
	/* = ARGUMENTS = */
	public	// Configuration
			$hooks = array( ),
			// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_HookForArchive_Master $master ){
		// Déclaration de la classe de Référence
		$this->master = $master;
		 
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ),99999 );
		add_filter( 'posts_clauses', array( $this, 'wp_posts_clauses' ), 99, 2 );
		add_action( 'save_post', array( $this, 'wp_save_post' ), 99, 2 );
		add_filter( 'post_type_link', array( $this, 'wp_post_type_link' ), 10, 4 );
		add_filter( 'quick_edit_dropdown_pages_args', array( $this, 'wp_dropdown_pages_args' ) );
		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'wp_dropdown_pages_args' ) );
		
		// Actions et Filtres TiFy
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) ); 
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
		add_filter( 'tify_breadcrumb_is_single', array( $this, 'tify_breadcrumb_is_single' ) );
		add_filter( 'tify_breadcrumb_is_archive', array( $this, 'tify_breadcrumb_is_archive' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation Globale == **/
	function wp_init(){		
		// Définition des hooks
		foreach( $this->master->hooks as $post_type => $args )
			if( ( $args['display'] === 'dynamic' ) || ( $args['display'] === 'dynamic-multi' ) )
				$this->hooks[$post_type] = $args;
				
		foreach( $this->get_hooks() as $archive_post_type => $v ) :
			// Colonne Personnalisée	
			if( $v['custom_column'] )	
		 		new tiFy_HookForArchive_CustomColumn( $archive_post_type, $v );
							
			// Réécriture d'url
			$hook_post_type = $v['hook_post_type'];
			
			if( ! ( $hook_parent_id = $this->get_hook_id( $archive_post_type ) ) )
				continue;			
			if( ! get_post( $hook_parent_id ) )
				continue;
			if( ! $hooks = get_posts( array( 'post_parent' => $hook_parent_id, 'post_type' => 'page', 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC' ) ) )
				continue;

			foreach( $hooks as $hook ) :
				$this->add_rewrite_rules( $archive_post_type, $hook_post_type, $hook->ID );
			endforeach;
		endforeach;
		/*global $wp_rewrite;
		var_dump( $wp_rewrite );
		exit;*/
	}
	
	/** == Modification des condition de requête == **/	
	function wp_posts_clauses( $pieces, $query ){	
		//Bypass	
		if( is_admin() && ! defined( 'DOING_AJAX' ) )
			return $pieces;		
		if( ! $hook_id = $query->get( 'tify_hook_id' ) )
			return $pieces;
		if( ! $archive_post_type = $query->get( 'post_type' ) )
			return $pieces;
		if( is_array( $archive_post_type ) )
			return $pieces;	
		if( $archive_post_type === 'any' )
			return $pieces;			
		if( ! in_array( $archive_post_type, $this->get_archive_post_types() ) )
			return $pieces;
			
		global $wpdb;
		extract( $pieces );	
		
		$hook_post_type = $this->get_hook_post_type( $archive_post_type );
		
		$join .= " INNER JOIN {$wpdb->postmeta} as tify_h4adyn_postmeta ON ( $wpdb->posts.ID = tify_h4adyn_postmeta.post_id )"; 
		$where .= " AND ( tify_h4adyn_postmeta.meta_key = '_". $hook_post_type ."_for_". $archive_post_type ."' AND tify_h4adyn_postmeta.meta_value = '{$hook_id}' )";

		$_pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );
		
		return compact ( $_pieces );
	}
	
	/** == Sauvegarde des enfants du post d'accroche == **/
	function wp_save_post( $post_id, $post ){				
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	      return;
		// Contrôle s'il s'agit d'une routine Ajax.	
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
	      return;		
		//Bypass
		if( ! isset( $_POST['post_type'] ) )
			return;		
		// Contrôle des permissions
	  	if ( 'page' == $_POST['post_type'] )
	    	if ( ! current_user_can( 'edit_page', $post_id ) )
	        	return;
	  	else
	    	if ( ! current_user_can( 'edit_post', $post_id ) )
	        	return;
		
		// Vérification d'existance du post		
		if( ! $post = get_post( $post_id ) )
			return;
		// Vérification de l'existance du parent
		if( ! $hook_parent_id = $post->post_parent )
			return;
		
		$hook_id = $post->ID;
		$archive_post_type 	= false; $hook_post_type = false;		
		
		foreach( (array) $this->get_hooks() as $_archive_post_type => $args ) :
			if( $this->get_hook_id( $_archive_post_type ) != $hook_parent_id )
				continue;
			$hook_post_type = $this->get_hook_post_type( $_archive_post_type );
			$archive_post_type = $_archive_post_type; 
			break;
		endforeach;
		
		// Bypass	
		if( ! $archive_post_type )
			return;	
		if( ! get_post( $hook_parent_id ) )
			return;	

		$this->add_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id );
		
		flush_rewrite_rules( );
			
		return $post;
	}

	/** == == **/
	function wp_post_type_link( $post_link, $post, $leavename, $sample ){
		if( ! $post = get_post( $post ) )
			return $post_link;	
	
		$archive_post_type = $post->post_type;
		
		// Bypass
		if( ! isset( $this->hooks[$archive_post_type] ) )
			return $post_link;
		
		$hook_post_type = $this->get_hook_post_type( $archive_post_type );
		$name = $hook_post_type .'_for_'. $archive_post_type;
		
		if( ! $hook_parent_id = $this->get_hook_id( $archive_post_type ) )
			return $post_link;		
		if( ! $hook_parent = get_post( $hook_parent_id ) )
			return $post_link;		
		if( ! $hook_ids = get_post_meta( $post->ID, '_'. $name ) )
			return $post_link;
		
		$hook_id = (int) current( $hook_ids );
		if( ! $hook = get_post( $hook_id ) )
			return $post_link;
		
		$archive_slug = $this->get_archive_slug( $archive_post_type, $hook_post_type, $hook_id );
		
		$post_link =  site_url( $archive_slug .'/'. $post->post_name );
		
		return $post_link;
	}
	
	/** == == **/
	function wp_dropdown_pages_args( $args ){
		$exclude = array( );

		foreach( $this->get_hooks() as $archive_post_type => $v ) :
			if( ! $hook_post_type = $v['hook_post_type'] )
				continue;
			if( ! $hook_parent_id = $this->get_hook_id( $archive_post_type ) )
				continue;
			if( ! $childs = get_children( array(	
					'post_parent' => $hook_parent_id,
					'post_type'   => $hook_post_type, 
					'numberposts' => -1,
				),
				ARRAY_N
			) )
				continue;
			foreach( $childs as $id => $child )
				array_push( $exclude, $id );
		endforeach;
		
	    $args['exclude'] = $exclude;
	    
	    return $args;
	}
	
	/* = ACTIONS ET FILTRES TiFY = */
	/** == == **/
	function tify_taboox_register_node(){
		foreach( $this->get_hooks() as $archive_post_type => $v ) 
			// Déclaration des Taboox
			if( $v['taboox_auto'] )
				tify_taboox_register_node( 
					$archive_post_type, 
					array( 
						'id' 	=> 'tify_hook_for_archive', 
						'title' => __( 'Page d\'affichage', 'tify' ), 
						'cb' 	=> 'tiFy_HookForArchive_Taboox',
						'order'	=> is_numeric( $v['taboox_auto'] )? $v['taboox_auto'] : 99,
						'args'	=> $v
					)
				);
	}
	
	/** == Déclaration des taboox == **/
	function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_HookForArchive_Taboox' );
	}
	
	/** == Fil d'Ariane == **/
	/*** === Page === ***/	
	function tify_breadcrumb_is_single( $output ){
		if( ! in_array( get_post_type(), $this->get_archive_post_types() ) )
			return $output;	
		
		$archive_post_type = get_post_type();
		if( ! $hook_parent_id = $this->get_hook_id( $archive_post_type ) )
			return $output;
		
		if( ! $hook_id = $this->get_archive_post_hook_child_id() )
			return $output;		

		$hook_parent_link 	= '<li><a href="'. get_the_permalink( $hook_parent_id ) .'" title="'. sprintf( __( 'Retour vers %s', 'tify' ), get_the_title( $hook_parent_id ) ) .'">'. get_the_title( $hook_parent_id ) .'</a></li>';
		$hook_link 			= '<li><a href="'. get_the_permalink( $hook_id ) .'" title="'. sprintf( __( 'Retour vers %s', 'tify' ), get_the_title( $hook_id ) ) .'">'. get_the_title( $hook_id ) .'</a></li>'; 	
		
		/** @todo Gestion des ancetres **/
		return $hook_parent_link . $hook_link . /*$ancestors .*/ '<li class="active">'. esc_html( wp_strip_all_tags( get_the_title() ) ) .'</li>';
	}
	
	/*** === Page de flux === ***/	
	function tify_breadcrumb_is_archive( $output ){
		if( ! in_array( get_query_var( 'post_type' ), $this->get_archive_post_types() ) )
			return $output;
		
		$archive_post_type = get_query_var( 'post_type' );
		
		if( ! $hook_parent_id = $this->get_hook_id( $archive_post_type ) )
			return $output;
		
		$hook_parent_link = '<li><a href="'. get_the_permalink( $hook_parent_id ) .'" title="'. sprintf( __( 'Retour vers %s', 'tify' ), get_the_title( $hook_parent_id ) ) .'">'. get_the_title( $hook_parent_id ) .'</a></li>';
		
		return $hook_parent_link . $output;
	}
	
	/* = CONTROLEURS = */
	/** == == **/
	function add_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id ){
		global $wp_rewrite;
		
		$archive_slug = $this->get_archive_slug( $archive_post_type, $hook_post_type, $hook_id );
		add_rewrite_rule( $archive_slug ."/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}" . '&paged=$matches[1]', 'top' );
		add_rewrite_rule( $archive_slug ."/?$", "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}", 'top' );
		add_rewrite_rule( $archive_slug ."/([^/]+)(/[0-9]+)?/?$", "index.php?{$archive_post_type}" . '=$matches[1]&page=$matches[2]' . "&tify_hook_id={$hook_id}", 'top' );
		/*global $wp_rewrite;
		
		$archive_slug = $this->get_archive_slug( $archive_post_type, $hook_post_type, $hook_id );		
		
		$paged_regex		= $archive_slug ."/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$";
		$single_regex		= $archive_slug ."/?$";
		$archive_regex		= $archive_slug ."/([^/]+)(/[0-9]+)?/?$";
		
		$paged_query 		= "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}" . '&paged=$matches[1]';
		$single_query		= "index.php?post_type={$archive_post_type}&tify_hook_id={$hook_id}";
		$archive_query		= "index.php?{$archive_post_type}" . '=$matches[1]&page=$matches[2]' . "&tify_hook_id={$hook_id}";
		
		foreach( array( 'paged', 'single', 'archive' ) as $type ) :
			$regex = "{$type}_regex";
			$query = "{$type}_query";
			
			if( ! isset( $wp_rewrite->extra_rules_top[${$regex}] ) )
				continue;
			if( $wp_rewrite->extra_rules_top[${$regex}] !== ${$query} )
				continue;
			
			add_rewrite_rule( $regex, $query, 'top' );
		endforeach;*/
	}
}

/* = TABOOX = */
/** == Affiliation dynamique == **/
class tiFy_HookForArchive_Taboox extends tiFy\Taboox\Admin{
	/* = CHARGEMENT = */	
	public function current_screen( $current_screen ){
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, '_'. $this->args['hook_post_type'] .'_for_'. $current_screen->id );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post ){
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

/* = CUSTOM COLUMN = */
class tiFy_HookForArchive_CustomColumn{
	/* = ARGUMENTS = */
	public 	$hook_post_type,
			$hook_args;
	
	/* = CONSTRUCTEUR = */
	function __construct( $hook_post_type, $hook_args ){
		// Déclaration des arguments
		$this->hook_post_type = $hook_post_type;
		$this->hook_args = $hook_args;
		
		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'admin == **/
	function wp_admin_init(){
		add_filter( "manage_edit-{$this->hook_post_type}_columns", array( $this, 'columns' ) );
		add_action( "manage_{$this->hook_post_type}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );
	}
	
	/**
	 * Entête et position de la colonne
	 */
	function columns( $columns ){
		$newcolumns = array(); $n = 0;
		foreach( $columns as $key => $column ) :
			if( $n == 3 ) 
				$newcolumns['tify_hook_for_archive'] = __( 'Page d\'affichage', 'tify' );
			$newcolumns[$key] = $column;
			$n++;				
		endforeach;
		$columns = $newcolumns;

		return $columns;
	}
	
	/**
	 * Affichage des données de la colonne
	 */
	function custom_column( $column, $post_id ){
		if( $column !== 'tify_hook_for_archive' )
			return $column;
		$post_type = $this->hook_post_type;
		$hook_post_type = $this->hook_args['hook_post_type'];
		
		if( $refs = get_post_meta( $post_id, '_'. $hook_post_type .'_for_'. $post_type ) )
			echo implode( ', ', array_map( 'get_the_title', $refs ) );	
	}
}