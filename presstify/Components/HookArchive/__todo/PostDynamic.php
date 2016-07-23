<?php
namespace tiFy\Components\HookForArchive;

class PostDynamic extends PostFactory
{
	/* = ARGUMENTS = *
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'save_post',
		'tify_taboox_register_node',
		'tify_taboox_register_form'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'init' 					=> 99999,
		'save_post'				=> 99
	);	
	// Nombre d'arguments autorisé lors de l'appel des actions
	protected $CallActionsArgsMap		= array(
		'save_post' 			=> 2
	);
	/** == FILTRES == **/
	// Liste des actions à déclencher
	protected $CallFilters				= array(
		'posts_clauses',
		'post_type_link',
		'quick_edit_dropdown_pages_args',
		'page_attributes_dropdown_pages_args',
		'tify_breadcrumb_is_single',
		'tify_breadcrumb_is_archive'
	);
	// Fonctions de rappel des filtres
	protected $CallFiltersFunctionsMap	= array(
		'quick_edit_dropdown_pages_args'		=> 'wp_dropdown_pages_args',
		'page_attributes_dropdown_pages_args'	=> 'wp_dropdown_pages_args'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallFiltersPriorityMap	= array(
		'posts_clauses' 		=> 99
	);
	// Nombre d'arguments autorisé lors de l'appel des actions
	protected $CallFiltersArgsMap		= array(
		'posts_clauses' 		=> 2,
		'post_type_link'		=> 4
	);
	
	/** == CONFIGURATION == **/
	public	$hooks = array();
	// Référence
	private $Master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( HookForArchive $HookForArchive )
	{
		parent::__construct();
		
		$this->Master = $HookForArchive;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation Globale == **/
	final public function init()
	{		
		// Définition des hooks
		foreach( $this->Master->getHooks() as $post_type => $args )
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
	}
	
	/** == Modification des condition de requête == **/	
	final public function posts_clauses( $pieces, $query )
	{	
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
		
		// Modification des conditions de requête
		if( $query->is_home ) :
			$query->is_home = false;
			$query->is_post_type_archive = true;
			$query->is_archive = true;
		endif;
		
		$hook_post_type = $this->get_hook_post_type( $archive_post_type );
		
		$join .= " INNER JOIN {$wpdb->postmeta} as tify_h4adyn_postmeta ON ( $wpdb->posts.ID = tify_h4adyn_postmeta.post_id )"; 
		$where .= " AND ( tify_h4adyn_postmeta.meta_key = '_". $hook_post_type ."_for_". $archive_post_type ."' AND tify_h4adyn_postmeta.meta_value = '{$hook_id}' )";

		$_pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );
		
		return compact ( $_pieces );
	}
	
	/** == Sauvegarde des enfants du post d'accroche == **/
	final public function save_post( $post_id, $post )
	{				
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
	final public function post_type_link( $post_link, $post, $leavename, $sample )
	{
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
	final public function wp_dropdown_pages_args( $args )
	{
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
	final public function tify_taboox_register_node()
	{
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
	final public function tify_taboox_register_form()
	{
		tify_taboox_register_form( 'tiFy_HookForArchive_Taboox' );
	}
	
	/** == Fil d'Ariane == **/
	/*** === Page === ***/	
	final public function tify_breadcrumb_is_single( $output )
	{
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
	final public function tify_breadcrumb_is_archive( $output )
	{
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
	private function add_rewrite_rules( $archive_post_type, $hook_post_type, $hook_id )
	{
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