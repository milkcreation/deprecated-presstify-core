<?php
/*
 Plugin Name: Shop
 Plugin URI: http://presstify.com/plugins/shop
 Description: boutique
 Version: 1.160408
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 Text Domain: tify
*/

namespace tiFy\Plugins\Shop;

use tiFy\Environment\Plugin;

class Shop extends Plugin
{
	/* = ARGUMENTS = */
	// Gamme de produits
	private static 	$ProductRange = array();
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		if( $product_range = self::getConfig( 'product_range' ) )
			self::$ProductRange = $this->parseProductRange( $product_range );
			
		add_action( 'init', array( $this, 'register_taxonomy' ), 0 );
		add_action( 'init', array( $this, 'register_post_type' ) );		
		
		add_action( 'admin_init', array( $this, 'create_initial_product_categories' ) );
		add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
	}
	
	/** == Traitement des gammes de produits == **/
	public function parseProductRange( $product_range = array() )
	{				
		foreach( $product_range as $post_type => &$args ) :
			// Definition des arguments généraux
			$label_default = _x( sprintf( 'Produits de la gamme %s', $post_type ), 'post type general name', 'tify' );
			if( empty( $args['label'] ) )
				$args['label'] = $label_default;
			if( empty( $args['singular'] ) )
				$args['singular'] = $args['label'];	
			if( empty( $args['plural'] ) )
				$args['plural'] = $args['label'];
		
			// Définition des arguments du type de post produit			
			/// Intitulés secondaires
			$labels_defaults = array(
				'name'               => $args['plural'],
				'singular_name'      => _x( $args['singular'] , 'post type singular name', 'tify' ),
				'menu_name'          => _x( $args['plural'], 'admin menu', 'tify' ),
				'name_admin_bar'     => _x( $args['singular'], 'add new on admin bar', 'tify' ),
				'add_new'            => _x( 'Ajouter un produit', $post_type, 'tify' ),
				'add_new_item'       => __( 'Ajouter un nouveau produit', 'tify' ),
				'new_item'           => __( 'Nouveau produit', 'tify' ),
				'edit_item'          => __( 'Éditer le produit de la gamme', 'tify' ),
				'view_item'          => __( 'Voir le produit', 'tify' ),
				'all_items'          => __( 'Tous les produits', 'tify' ),
				'search_items'       => __( 'Recherche de produits', 'tify' ),
				//'parent_item_colon'  => __( sprintf( 'Produits parent de la gamme %d' ), 'tify' ),
				'not_found'          => __( 'Aucun produit trouvé.', 'tify' ),
				'not_found_in_trash' => __( 'Aucun produit dans la corbeille.', 'tify' ),
			);
			$args['labels'] = wp_parse_args( ( ! empty( $args['labels'] ) ? $args['labels'] : array() ), $labels_defaults );
			
			/// Description
			if( empty( $args['description'] ) )
				$args['description'] = __( $args['label'], 'tify' );
			
			/// Autres arguments
			$args['public'] 				= true;
			$args['exclude_from_search']	= false;
			$args['publicly_queryable'] 	= true;
			$args['show_ui'] 				= true;
			$args['show_in_nav_menus']		= true;
			$args['show_in_menu'] 			= true;
			$args['show_in_admin_bar']		= true;
			if( empty( $args['menu_position'] ) )
				$args['menu_position'] 			= null;
			if( empty( $args['menu_icon'] ) )
				$args['menu_icon'] 				= tify_svg_img_src( $this->Url .'/assets/barcode.svg' );
			$args['capability_type'] 		= 'page';
			//$args['capabilities']			= array();
			$args['map_meta_cap']			= null;
			$args['hierarchical'] 			= false;
			$args['supports'] 				= array( 'title', 'editor', 'thumbnail' );
			$args['register_meta_box_cb']	= '';
			$args['taxonomies']				= array();
			$args['has_archive'] 			= true;
			$args['permalink_epmask']		= EP_PERMALINK;
			$args['rewrite'] 				= array( 
				'slug' 			=> $post_type, 
				'with_front'	=> false, 
				'feeds' 		=> true, 
				'pages' 		=> true,
				'ep_mask'		=> EP_PERMALINK
			);			
			$args['query_var'] 				= true;
			$args['can_export']				= true;
			$args['show_in_rest']			= true;
			$args['rest_base']				= $post_type;
			$args['rest_controller_class']	= 'WP_REST_Posts_Controller';
			
			// Définition des arguments de catégorie de produit
			if( isset( $args['product_category'] ) ) :
				$args['product_category']['taxonomy'] 				= 'tifyshopcat-'. $post_type;
				$args['product_category']['labels']					= array(
					'name' 							=> _x( 'Catégories de produit', 'taxonomy general name', 'tify' ),
					'singular_name'					=> _x( 'Catégorie de produit', 'taxonomy singular name', 'tify' ),
					'menu_name'						=> __( 'Catégorie de produit', 'tify' ),
					'all_items'						=> __( 'Toutes les catégories de produit', 'tify' ),
					'edit_item'						=> __( 'Éditer la catégorie de produit', 'tify' ),
					'view_item'						=> __( 'Voir la catégorie de produit', 'tify' ),
					'update_item'					=> __( 'Mettre à jour la catégorie de produit', 'tify' ),
					'add_new_item'					=> __( 'Ajouter une nouvelle catégorie de produit', 'tify' ),
					'new_item_name'					=> __( 'Nouvelle catégorie de produit', 'tify' ),
					'search_items'					=> __( 'Recherche d\'une catégorie de produit', 'tify' ),						
					'popular_items'					=> __( 'Catégories de produit populaires', 'tify' ),						
					'separate_items_with_commas'	=> __( 'Séparer les catégories de produit par une virgule', 'tify' ),
					'add_or_remove_items'			=> __( 'Ajout ou suppression de catégories de produit', 'tify' ),
					'choose_from_most_used'			=> __( 'Choisir parmi les catégories de produits les plus utilisées', 'tify' ),
					'not_found'						=> __( 'Aucune catégorie de produit trouvée.', 'tify' )						
				);
				$args['product_category']['public'] 				= true;
				$args['product_category']['show_ui'] 				= true;
				$args['product_category']['show_in_menu'] 			= true;
				$args['product_category']['show_in_nav_menus'] 		= false;
				$args['product_category']['show_tagcloud'] 			= false;
				$args['product_category']['show_in_quick_edit'] 	= false;
				$args['product_category']['meta_box_cb'] 			= null;
				$args['product_category']['show_admin_column'] 		= true;
				$args['product_category']['description'] 			= '';
				$args['product_category']['hierarchical'] 			= false;
				//$args['product_category']['update_count_callback'] = '';
				$args['product_category']['query_var'] 				= true;
				$args['product_category']['rewrite'] 				= array(
					'slug' 			=> $args['product_category']['taxonomy'], 
					'with_front'	=> false, 
					'hierarchical' 	=> false		
				);
				//$args['product_category']['capabilities'] = '';
				$args['product_category']['sort'] 	= true;				
			endif;			
		endforeach;
	
		return $product_range;
	}
	
	/** == Déclaration des catégories type pour les gammes de produits multiple == **/
	final public function register_taxonomy()
	{		
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			if( empty( $args['product_category'] ) )
				continue;
			
			extract( $args['product_category'] );				
				
			$taxonomy_args = compact(
				'labels', 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud' , 'show_in_quick_edit', 
				'meta_box_cb', 'show_admin_column', 'description', 'hierarchical', 'query_var', 'rewrite', 'sort'
			);

			\register_taxonomy(
				$taxonomy,
				array( $post_type ),
				$taxonomy_args
			);
		endforeach;
	}
	
	/** == Création des catégories de produits initiales == **/
	final public function create_initial_product_categories()
	{
		// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		// Contrôle s'il s'agit d'une execution de page via ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			if( empty( $args['product_category']['initial_terms'] ) )
				continue;
			foreach( (array) $args['product_category']['initial_terms'] as $slug => $name ) :
				if( ! $term = get_term_by( 'slug', $slug, $args['product_category']['taxonomy'] ) ) :
					wp_insert_term( $name, $args['product_category']['taxonomy'], array( 'slug' => $slug ) );
				elseif( $term->name !== $name ) :
					wp_update_term( $term->term_id, $args['product_category']['taxonomy'], array( 'name' => $name ) );
				endif;
			endforeach;
		endforeach;
	}
	
	/** == Nettoyage des metaboxe == **/
	final public function remove_meta_box()
	{
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			if( empty( $args['product_category'] ) )
				continue;
			remove_meta_box( 'tagsdiv-tifyshopcat-bb_product', $post_type, true );
		endforeach;
	}
		
	/** == Déclaration des sections de boites de saisie des metadonnées de produits == **/
	final public function tify_taboox_register_node()
	{
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			if( empty( $args['product_category'] ) )
				continue;
			tify_taboox_register_node( 
				$post_type, 
				array(
					'title'	=> __( 'Catégorie de produit', 'tify' ),
    				'cb'	=> "\\tiFy\\Components\\HookArchive\\Taboox\\Post\\TermSelector\\Admin\\TermSelector",
   					'args'	=> array(	
   						'taxonomy' 			=> self::getProductCat($post_type), 
   						'selector'			=> 'checkbox', 
   						'show_option_none'	=> false   						
   					),
					'order'				=> 0
				)
			);
		endforeach;
	}
	
	/** == Déclaration des types de posts personnalisés des gammes de produits == **/
	final public function register_post_type()
	{
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			extract( $args );

			$post_type_args = compact( 
				'label', 'labels', 'description', 'public', 'exclude_from_search', 'publicly_queryable', 'show_ui',
				'show_in_nav_menus', 'show_in_menu', 'show_in_admin_bar', 'menu_position', 'menu_icon', 'capability_type',
				'map_meta_cap', 'hierarchical', 'supports', 'register_meta_box_cb', 'taxonomies', 'has_archive',
				'permalink_epmask', 'rewrite', 'query_var', 'can_export', 'show_in_rest', 'rest_base', 'rest_controller_class'
			);
			
			\register_post_type( $post_type, $post_type_args );
		endforeach;
	}
	
	/* = CONTROLEUR = */
	/** == Récupération de la catégorie de produit de la gamme == **/
	public static function getProductCat( $post_type )
	{
		if( ! empty( self::$ProductRange[$post_type]['product_category'] ) )
			return self::$ProductRange[$post_type]['product_category']['taxonomy'];
	}
}