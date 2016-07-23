<?php
class tiFy_Forum_MultiMain{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** Initialisation globale == **/
	function wp_init(){
		register_post_type( 'tify_forum', array(
				'labels' => array(
					'name'			 	=> __( 'Forums', 'tify' ),
					'singular_name' 	=> __( 'Forum', 'tify' ),			
					'add_new'			=> __( 'Ajouter un forum', 'tify' ),
					'all_items' 		=> __( 'Tous les forums', 'tify' ),
					'add_new_item'		=> __( 'Ajouter un nouveau forum', 'tify' ),
					'edit_item'			=> __( 'Éditer le forum', 'tify' ),
					'new_item'			=> __( 'Nouveau forum', 'tify' ),
				 	'view_item'			=> __( 'Afficher le forum', 'tify' ),
					'search_items'		=> __( 'Rechercher un forum', 'tify' ),
					'not_found'			=> __( 'Aucun forum trouvé', 'tify' ),		
					'not_found_in_trash'=> __( 'Aucun forum dans la corbeille', 'tify' ),
					'parent_item_colon'	=> __( 'Forum parent', 'tify' ),
					'menu_name' 		=> __( 'Forums', 'add new on admin bar', 'tify' ),				
				),
				'description'			=> __( 'Forum PressTiFy basé sur les commentaires', 'tify' ),
				'public'				=> true,
				'exclude_from_search'	=> false,
				'publicly_queryable' 	=> true,
		    	'show_ui' 				=> true,
		    	'show_in_nav_menus' 	=> true,
		    	'show_in_menu' 			=> false,		
				'show_in_admin_bar' 	=> false,
				'menu_position'			=> null,
				'menu_icon'				=> false,
				'capability_type' 		=> 'page',
				'map_meta_cap' 			=> true,
				'hierarchical' 			=> true,
				'supports' 				=> array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		    	'has_archive' 			=> true,
		    	'permalink_epmask'		=> EP_PERMALINK,
		    	'rewrite' 				=> array( 'slug' => __( 'forum', 'tify' ), 'with_front' => false ),			
			   	'query_var' 			=> true,
		    	'can_export'			=> true,		
			)
		);	
	}	
}