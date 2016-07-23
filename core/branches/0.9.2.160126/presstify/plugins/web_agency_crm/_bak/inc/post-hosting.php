<?php
/**
 * Initialisation du type de post
 */
function mkcrm_hosting_init() {
	register_post_type( 'mkcrm_hosting', array(
		'labels' => array(
			'name'			 	=> __( 'Hébergements', 'mkcrm' ),
			'singular_name' 	=> __( 'Hébergement', 'mkcrm' ),			
			'add_new'			=> __( 'Ajouter un Hébergement', 'mkcrm' ),
			'all_items' 		=> __( 'Tous les hébergements', 'mkcrm' ),
			'add_new_item'		=> __( 'Ajouter d\'un nouvel hébergement', 'mkcrm' ),
			'edit_item'			=> __( 'Éditer l\'hébergement', 'mkcrm' ),
			'new_item'			=> __( 'Nouvel hébergement', 'mkcrm' ),
		 	'view_item'			=> __( 'Afficher l\'hébergement', 'mkcrm' ),
			'search_items'		=> __( 'Rechercher un hébergement', 'mkcrm' ),
			'not_found'			=> __( 'Aucun hébergement trouvé', 'mkcrm' ),		
			'not_found_in_trash'=> __( 'Aucun hébergement dans la corbeille', 'mkcrm' ),
			'parent_item_colon'	=> __( 'Parent', 'mkcrm' ),
			'menu_name' 		=> __( 'Hébergements', 'add new on admin bar', 'mkcrm' ),				
		),
		'description'			=> __( 'Hébergements Milkcreation', 'mkcrm' ),
		'public'				=> true,
		'exclude_from_search'	=> false,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true,
		'show_in_nav_menus' 	=> true,
		'show_in_menu' 			=> true,		
		'show_in_admin_bar' 	=> true,
		'menu_position'			=> null,
		'capability_type' 		=> 'page',
		//'capabilities'		=> array(),
		'map_meta_cap' 			=> true,
		'hierarchical' 			=> false,
		'supports' 				=> array( 'title', 'thumbnail' ),
		'register_meta_box_cb'	=> '',
		'taxonomies'			=> array(),
		'has_archive' 			=> true,
		'permalink_epmask'		=> EP_PERMALINK,
		'rewrite' 				=> array( 'slug'=> __( 'hebergements', 'mkcrm' ), 'with_front'=> false ),			
	   	'query_var' 			=> true,
		'can_export'			=> true,		
		)
	);
}
add_action('init', 'mkcrm_hosting_init');

/**
 * Nettoyage des metaboxes
 */
function mkcrm_hosting_offer_meta_boxes(){
	remove_meta_box( 'tagsdiv-offer', 'mkcrm_hosting', 'side' );
}
add_action( 'admin_menu', 'mkcrm_hosting_offer_meta_boxes' );

/**
 * COLONNES PERSONNALISEES
 */ 
/**
 * Déclaration des colonnes personnalisées
 */
function mkcrm_hosting_custom_columns( $columns ){
	$columns['sub-domains'] = __( 'Sous-domaines', 'mkcrm' );	
	unset( $columns['date'] );
	
	return $columns;
}
add_filter( 'manage_edit-mkcrm_hosting_columns', 'mkcrm_hosting_custom_columns' );

/**
 * Affichage du contenu des colonnes personnalisées
 */
function mkcrm_hosting_custom_rows( $column, $post_id ){
	switch( $column ) :
		default :
			return $column;
			break;
		case 'sub-domains' :
			$output = "";
			foreach( (array) get_post_meta( $post_id, '_domain_name' ) as $dn ) :
				foreach( (array) $dn['sub'] as $sub ) :
					if( ! $dn['name'] ) continue;					
					$_sub = ( $sub != '@' )? $sub.".".$dn['name'].", " : $dn['name'];
					$output .= "<a href=\"http://$_sub\" target=\"_blank\">$_sub</a>, ";
				endforeach;
			endforeach;
			echo $output;
		break;
	endswitch;
}
add_action( 'manage_mkcrm_hosting_posts_custom_column', 'mkcrm_hosting_custom_rows', null, 2);
		
/**
 * Définition des colonnes personnalisés triables
 
function mkcrm_hosting_sortable_columns($columns) {
	$custom = array(
		'amount' => 'amount'
	);
	return wp_parse_args($custom, $columns);
}
add_filter( 'manage_edit-mkcrm_hosting_sortable_columns', 'mkcrm_hosting_sortable_columns' ); */

/**
 * Gestion du trie des colonnes personnalisées
 
function mkcrm_hosting_column_orderby( $vars ) {
	if( isset( $vars['orderby'] ) && 'amount' == $vars['orderby'] )
		$vars = array_merge( $vars, array(
			'meta_key' => '_amount',
			'orderby' => 'meta_value'
		) );
	
	return $vars;
}
add_filter( 'request', 'mkcrm_hosting_column_orderby' ); */

/**
 * 
 */
function mkcrm_hosting_list(){
	
}