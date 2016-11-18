<?php
namespace tiFy\Core\CustomType;

use tiFy\Environment\Core;

class CustomType extends Core
{
	/* = ARGUMENTS = */
	// Liste des arguments de déclaration des taxonomies personnalisées
	private static $Taxonomies 		= array();
	// Liste des arguments de déclaration des types de post personnalisés 
	private static $PostTypes 		= array();
	// Liste des relations type de post personnalisé <> taxonomie
	private $PostTypeTaxonomies		= array();	
	// Liste des termes initiaux
	private $InitialTerms			= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Traitement des types personnalisés passés en arguments
		// Taxonomie
		foreach( (array) self::getConfig( 'taxonomy' ) as $taxonomy => $args )
			self::RegisterTaxonomy( $taxonomy, $args );
		
		// Type de post		
		foreach( (array) self::getConfig( 'post_type' ) as $post_type => $args )
			self::RegisterPostType( $post_type, $args );		
			
		add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
		add_action( 'init', array( $this, 'register_post_type' ), 0 );
		add_action( 'init', array( $this, 'register_taxonomy_for_object_type' ), 0 );
		add_action( 'admin_init', array( $this, 'create_initial_terms' ) );
	}
	
	/* = ACTIONS = */
	/** == Déclaration des taxonomies personnalisées == **/
	final public function register_taxonomy()
	{		
		do_action( 'tify_custom_taxonomy_register' );

		foreach( (array) self::$Taxonomies as $taxonomy => $args ) :
			$args = $this->ParseTaxonomyAttrs( $taxonomy, $args );
			extract( $args );	 		

			if( ! empty( $args['object_type'] ) )
				$this->RegisterPostTypeTaxonomies( $args['object_type'], $taxonomy );
			
			if( ! empty( $args['initial_terms'] ) ) 
				$this->RegisterInitialTerms( $taxonomy, $args['initial_terms'] );
			
			$allowed_args = array(
				'label', 'labels', 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud' , 'show_in_quick_edit', 
				'meta_box_cb', 'show_admin_column', 'description', 'hierarchical', 'query_var', 'rewrite', 'sort',
				'show_in_rest', 'rest_base', 'rest_controller_class'
			);
			foreach( $allowed_args as $allowed_arg )
				if( isset( $args[$allowed_arg] ) )
					$taxonomy_args[$allowed_arg] = $args[$allowed_arg];
			
			\register_taxonomy(
				$taxonomy,
				array(),
				$taxonomy_args
			);
		endforeach;
		if( is_admin() ):
			//exit;
		endif;
	}
	
	/** == Déclaration des types de posts personnalisés == **/
	final public function register_post_type()
	{
		do_action( 'tify_custom_post_type_register' );

		foreach( (array) self::$PostTypes as $post_type => $args ) :
			$args = $this->ParsePostTypeAttrs( $post_type, $args );
			extract( $args );
			
			if( ! empty( $taxonomies ) )
				$this->RegisterPostTypeTaxonomies( $post_type, $taxonomies );	
			
			$allowed_args = array( 
				'label', 'labels', 'description', 'public', 'exclude_from_search', 'publicly_queryable', 'show_ui',
				'show_in_nav_menus', 'show_in_menu', 'show_in_admin_bar', 'menu_position', 'menu_icon', 'capability_type',
				'map_meta_cap', 'hierarchical', 'supports', 'register_meta_box_cb', /*'taxonomies',*/ 'has_archive',
				'permalink_epmask', 'rewrite', 'query_var', 'can_export', 'show_in_rest', 'rest_base', 'rest_controller_class'
			);
			foreach( $allowed_args as $allowed_arg )
				if( isset( $args[$allowed_arg] ) )
					$post_type_args[$allowed_arg] = $args[$allowed_arg];

			\register_post_type( 
				$post_type, 
				$post_type_args 
			);
		endforeach;
	}
	
	/** == Affiliation des types de post aux taxonomies == **/
	final public function register_taxonomy_for_object_type()
	{
		foreach( (array) $this->PostTypeTaxonomies as $post_type => $taxonomies ) :
			foreach( (array) $taxonomies as $taxonomy ) :
				\register_taxonomy_for_object_type( $taxonomy, $post_type );
			endforeach;
		endforeach;
	}
	
	/** == Création des catégories de produits initiales == **/
	final public function create_initial_terms()
	{
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		// Contrôle s'il s'agit d'une execution de page via ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		
		foreach( (array) $this->InitialTerms as $taxonomy => $terms ) :
			foreach( (array) $terms as $slug => $name ) :
				if( ! $term = get_term_by( 'slug', $slug, $taxonomy ) ) :
					wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
				/*elseif( $term->name !== $name ) :
					wp_update_term( $term->term_id, $taxonomy, array( 'name' => $name ) );*/
				endif;
			endforeach;
		endforeach;
	}
	
	/* = CONTROLEURS = */
	/** == Déclaration d'une taxonomie personnalisées == **/
	public static function RegisterTaxonomy( $taxonomy, $args )
	{
		if( ! isset( self::$Taxonomies[$taxonomy] ) )
			self::$Taxonomies[$taxonomy] = $args;
	}
	
	/** == Déclaration d'une taxonomie personnalisées == **/
	public static function RegisterPostType( $post_type, $args )
	{
		if( ! isset( self::$PostTypes[$post_type] ) )
			self::$PostTypes[$post_type] = $args;
	}
	
	/** == Arguments par défaut des taxonomies personnalisées == **/
	private function ParseTaxonomyAttrs( $taxonomy, $args = array() )
	{
		// Traitement des arguments généraux
		$label 		= _x( $taxonomy, 'taxonomy general name', 'tify' );
		$plural 	= _x( $taxonomy, 'taxonomy plural name', 'tify' );
		$singular 	= _x( $taxonomy, 'taxonomy singular name', 'tify' );
		$gender 	= false; 
		foreach( array( 'gender', 'label', 'plural', 'singular' ) as $attr ) :
			if ( isset( $args[$attr] ) ) :
				${$attr} = $args[$attr];
				unset( $args[$attr] );
			endif;
		endforeach;
		
		if( ! isset( $args['labels'] ) )
			$args['labels'] = array();	
		$labels = new \tiFy\Core\Labels\Factory( wp_parse_args( $args['labels'], array( 'singular' => $singular, 'plural' => $plural, 'gender' => $gender ) ) );
		$args['labels'] = $labels->Get();
		
		$defaults['public'] 				= true;
		$defaults['show_ui'] 				= true;
		$defaults['show_in_menu'] 			= true;
		$defaults['show_in_nav_menus'] 		= false;
		$defaults['show_tagcloud'] 			= false;
		$defaults['show_in_quick_edit'] 	= false;
		$defaults['meta_box_cb'] 			= null;
		$defaults['show_admin_column'] 		= true;
		$defaults['description'] 			= '';
		$defaults['hierarchical'] 			= false;
		//$defaults['update_count_callback'] = '';
		$defaults['query_var'] 				= true;
		$defaults['rewrite'] 				= array(
			'slug' 			=> $taxonomy, 
			'with_front'	=> false, 
			'hierarchical' 	=> false		
		);
		//$defaults['capabilities'] = '';
		$defaults['sort'] 	= true;
		
		return wp_parse_args( $args, $defaults );
	}
	
	/** == Arguments par défaut des types de post personnalisés == **/
	private function ParsePostTypeAttrs( $post_type, $args = array() )
	{
		// Traitement des arguments généraux
		/// Intitulés
		$label 		= _x( $post_type, 'post type general name', 'tify' );
		$plural 	= _x( $post_type, 'post type plural name', 'tify' );
		$singular 	= _x( $post_type, 'post type singular name', 'tify' );
		$gender 	= false; 
		foreach( array( 'gender', 'label', 'plural', 'singular' ) as $attr ) :
			if ( isset( $args[$attr] ) ) :
				${$attr} = $args[$attr];
				unset( $args[$attr] );
			endif;
		endforeach;
		
		if( ! isset( $args['labels'] ) )
			$args['labels'] = array();	
		$labels = new \tiFy\Core\Labels\Factory( wp_parse_args( $args['labels'], array( 'singular' => $singular, 'plural' => $plural, 'gender' => $gender ) ) );
		$args['labels'] = $labels->Get();
		
		// Définition des arguments du type de post
		/// Description
		$defaults['description'] = '';
		
		/// Autres arguments
		$defaults['public'] 				= true;
		$defaults['exclude_from_search']	= false;
		$defaults['publicly_queryable'] 	= true;
		$defaults['show_ui'] 				= true;
		$defaults['show_in_nav_menus']		= true;
		$defaults['show_in_menu'] 			= true;
		$defaults['show_in_admin_bar']		= true;
		$defaults['menu_position'] 			= null;
		$defaults['menu_icon'] 				= null;
		$defaults['capability_type'] 		= 'page';
		//$args['capabilities']			= array();
		$defaults['map_meta_cap']			= null;
		$defaults['hierarchical'] 			= false;
		$defaults['supports'] 				= array( 'title', 'editor', 'thumbnail' );
		$defaults['register_meta_box_cb']	= '';
		$defaults['taxonomies']				= array();
		$defaults['has_archive'] 			= true;
		$defaults['permalink_epmask']		= EP_PERMALINK;
		$defaults['rewrite'] 				= array( 
			'slug' 			=> $post_type, 
			'with_front'	=> false, 
			'feeds' 		=> true, 
			'pages' 		=> true,
			'ep_mask'		=> EP_PERMALINK
		);			
		$defaults['query_var'] 				= true;
		$defaults['can_export']				= true;
		$defaults['show_in_rest']			= true;
		$defaults['rest_base']				= $post_type;
		$defaults['rest_controller_class']	= 'WP_REST_Posts_Controller';		
						
		return wp_parse_args( $args, $defaults );
	}
	
	/** == Déclaration des taxonomies de type de post == **/
	private function RegisterPostTypeTaxonomies( $post_type, $taxonomies )
	{
		if( is_string( $post_type ) )
			$post_type = array_map( 'trim', explode( ',', $post_type ) );
		if( is_string( $taxonomies ) )
			$taxonomies = array_map( 'trim', explode( ',', $taxonomies ) );
		
		foreach( (array) $post_type as $pt ) :
			if( ! isset( $this->PostTypeTaxonomies[$pt] ) )
				$this->PostTypeTaxonomies[$pt] = array();
		
			foreach( (array) $taxonomies as $tax ) :
				if( ! in_array( $tax, $this->PostTypeTaxonomies[$pt] ) )
					array_push( $this->PostTypeTaxonomies[$pt], $tax );
			endforeach;
		endforeach;
	}
	
	private function RegisterInitialTerms( $taxonomy, $initial_terms )
	{
		if( ! isset( $this->InitialTerms[$taxonomy] ) )
			$this->InitialTerms[$taxonomy] = array();
		
		foreach( (array) $initial_terms as $slug => $name ) :
			$this->InitialTerms[$taxonomy][$slug] = $name;
		endforeach;
	}
}