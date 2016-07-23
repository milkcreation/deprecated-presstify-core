<?php
/**
 * Initialisation de la taxonomie
 */
function mkcrm_offer_init() {
	// Déclaration de la taxonomie
	register_taxonomy( 'mkcrm_offer', array('mkcrm_hosting'), array(
			'labels' => array(
				'name'              => _x( 'Offres', 'taxonomy general name', 'mkcrm' ),
				'singular_name'     => _x( 'Offre', 'taxonomy singular name', 'mkcrm' ),
				'menu_name'         => __( 'Offres', 'mkcrm' ),
				'all_items'         => __( 'Toutes les offres', 'mkcrm' ),
				'edit_item'         => __( 'Editer une offre', 'mkcrm' ),
				'view_item'			=> __( 'Voir l\'offre', 'mkcrm'),
				'update_item'       => __( 'Mettre à jour l\'offre', 'mkcrm' ),
				'add_new_item'      => __( 'Ajouter une offre', 'mkcrm' ),
				'new_item_name'     => __( 'Nouvelle offre', 'mkcrm' ),
				'parent_item'       => __( 'Offre parente', 'mkcrm' ),
				'parent_item_colon' => __( 'Offre parente:', 'mkcrm' ),				
				'search_items'      => __( 'Recherche d\'offre', 'mkcrm' ),
				'popular_items'		=> __( 'Offres populaires', 'mkcrm' ),
				'separate_items_with_commas' => __( 'Séparer par des virgules', 'mkcrm' ),
				'add_or_remove_items' => __( 'Ajout ou suppression d\'offre', 'mkcrm' ),
				'choose_from_most_used' => __( 'Choisir parmi les offres les plus utilisées', 'mkcrm' )
			),
			'public'	=> true,
			'show_ui'	=> true,
  			'show_in_nav_menus' => false,
			'show_tagcloud'	=> false,
			'show_admin_column' => true,
			'hierarchical'	=> false,
			//'update_count_callback' => ''
   			//query_var => ''
			'rewrite' => array( 'slug' => __('offre', 'mkcrm' ), 'with_front' => false, 'hierarchical' => false ),
  			//'capabilities' => ''
			'sort'	=> true
		)
	);
	
	// Déclaration des champs de métadonnées
	add_action ( 'mkcrm_offer_edit_form_fields', 'mkcrm_offer_edit_form_fields' );
	// Enregistrement des metadonnées
	add_action ( 'edited_mkcrm_offer', 'mkcrm_offer_edited_taxonomy' );
	
	add_filter( "manage_edit-mkcrm_offer_columns", 'mkcrm_offer_manage_edit_posts_custom_column' );
	add_filter( "manage_mkcrm_offer_custom_column", 'mkcrm_offer_manage_custom_column', null, 3 );
}
add_action('init', 'mkcrm_offer_init');

/**
 * Affichage des champs de métadonnées
 */
function mkcrm_offer_edit_form_fields( $tag ){
?>
	<tr>
		<th scope="row" valign="top"><label><?php _e( 'Tarif/mois', 'mkcrm' );?></label></th>
		<td>
			<input type="text" name="price" value="<?php echo get_metadata( 'taxonomy', $tag->term_id, 'price', true );?>" size="5" /> €
		</td>
	</tr>
 <?php
}

/**
 * Enregistrement des valeurs de métadonnées
 */
function mkcrm_offer_edited_taxonomy( $term_id ){
	if ( isset( $_POST['price'] ) )
		update_metadata( 'taxonomy', $term_id, 'price', number_format( $_POST['price'], 2 ) );
	else
		delete_metadata( 'taxonomy', $term_id, 'price' );
}

/**
 * Colonnes personnalisées de la page liste
 */
function mkcrm_offer_manage_edit_posts_custom_column( $columns ){
	$columns['price'] = __( 'Tarif/mois', 'mkcrm' );
	
	return $columns;
} 

/**
 * Valeur des lignes dans les colonnes personnalisées de la page liste
 */
function mkcrm_offer_manage_custom_column( $output, $column_name, $term_id ){
	switch( $column_name ) :
		case 'price' :
			echo ( $price = get_metadata( 'taxonomy', $term_id, 'price', true ) ) ? $price." €": "--" ;
		break;
	endswitch;
}