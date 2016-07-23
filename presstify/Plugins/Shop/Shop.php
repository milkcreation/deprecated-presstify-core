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
		
		if( $product_range = self::getConfig( 'product_range' ) ) :
			self::$ProductRange = $this->parseProductRange( $product_range );
		endif;

		add_action( 'tify_custom_taxonomy_register', array( $this, 'tify_custom_taxonomy_register' ) );
		add_action( 'tify_custom_post_type_register', array( $this, 'tify_custom_post_type_register' ) );		
		
		add_action( 'admin_init', array( $this, 'create_initial_product_categories' ) );
	}
	
	/** == Traitement des gammes de produits == **/
	public function parseProductRange( $product_range = array() )
	{				
		foreach( $product_range as $post_type => &$args ) :
			// Definition des arguments généraux
			if( empty( $args['label'] ) )
				$args['label'] = _x( sprintf( 'Produits de la gamme %s', $post_type ), 'post type general name', 'tify' );
			if( empty( $args['plural'] ) )
				$args['plural'] = _x( sprintf( 'Produits de la gamme %s', $post_type ), 'post type plural name', 'tify' );
			if( empty( $args['singular'] ) )
				$args['singular'] = _x( sprintf( 'Produit de la gamme %s', $post_type ), 'post type singular name', 'tify' );

			if( empty( $args['menu_icon'] ) )
				$args['menu_icon'] 				= tify_svg_img_src( $this->Url .'/assets/barcode.svg' );
			
			// Définition des arguments de catégorie de produit
			if( isset( $args['product_category'] ) ) :
				$args['product_category']['taxonomy'] = 'tifyshopcat-'. $post_type;	
			if( empty( $args['label'] ) )
				$args['product_category']['label'] = _x( 'Catégories de produits', 'taxonomy general name', 'tify' );
			if( empty( $args['product_category']['plural'] ) )
				$args['product_category']['plural'] = _x( 'Catégories de produits', 'taxonomy plural name', 'tify' );
			if( empty( $args['product_category']['singular'] ) )
				$args['product_category']['singular'] = _x( 'Catégorie de produits', 'taxonomy singular name', 'tify' );
			endif;			
		endforeach;
	
		return $product_range;
	}
	
	/** == Déclaration des catégories type pour les gammes de produits multiple == **/
	final public function tify_custom_taxonomy_register()
	{		
		foreach( (array) self::$ProductRange as $object_type => $args ) :
			if( empty( $args['product_category'] ) )
				continue;
			
			extract( $args['product_category'] );				
				
			$taxonomy_args = compact(
				'object_type', 'label', 'singular', 'plural', 'gender', 'labels', 'public', 'show_ui', 'show_in_menu', 'show_in_nav_menus', 'show_tagcloud' , 'show_in_quick_edit', 
				'meta_box_cb', 'show_admin_column', 'description', 'hierarchical', 'query_var', 'rewrite', 'sort', 'initial_terms'
			);

			\tify_custom_taxonomy_register( $taxonomy, $taxonomy_args );
		endforeach;
	}
		
	/** == Déclaration des types de posts personnalisés des gammes de produits == **/
	final public function tify_custom_post_type_register()
	{
		foreach( (array) self::$ProductRange as $post_type => $args ) :
			extract( $args );

			$post_type_args = compact( 
				'label', 'singular', 'plural', 'gender', 'labels', 'description', 'public', 'exclude_from_search', 'publicly_queryable', 'show_ui',
				'show_in_nav_menus', 'show_in_menu', 'show_in_admin_bar', 'menu_position', 'menu_icon', 'capability_type',
				'map_meta_cap', 'hierarchical', 'supports', 'register_meta_box_cb', 'taxonomies', 'has_archive',
				'permalink_epmask', 'rewrite', 'query_var', 'can_export', 'show_in_rest', 'rest_base', 'rest_controller_class'
			);
			
			\tify_custom_post_type_register( $post_type, $post_type_args );
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