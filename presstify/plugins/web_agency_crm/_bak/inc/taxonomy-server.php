<?php
/**
 * Initialisation de la taxonomie
 */
function mkcrm_server_init() {
	// Déclaration de la taxonomie
	register_taxonomy( 'mkcrm_server', array('mkcrm_hosting'), array(
			'labels' => array(
				'name'              => _x( 'Serveurs', 'taxonomy general name', 'mkcrm' ),
				'singular_name'     => _x( 'Serveur', 'taxonomy singular name', 'mkcrm' ),
				'menu_name'         => __( 'Serveurs', 'mkcrm' ),
				'all_items'         => __( 'Tous les serveurs', 'mkcrm' ),
				'edit_item'         => __( 'Editer le serveur', 'mkcrm' ),
				'view_item'			=> __( 'Voir le serveur', 'mkcrm'),
				'update_item'       => __( 'Mettre à jour le serveur', 'mkcrm' ),
				'add_new_item'      => __( 'Ajouter un serveur', 'mkcrm' ),
				'new_item_name'     => __( 'Nouveau serveur', 'mkcrm' ),
				'parent_item'       => __( 'Serveur parent', 'mkcrm' ),
				'parent_item_colon' => __( 'Serveur parent:', 'mkcrm' ),				
				'search_items'      => __( 'Recherche de serveur', 'mkcrm' ),
				'popular_items'		=> __( 'Serveurs populaires', 'mkcrm' ),
				'separate_items_with_commas' => __( 'Séparer par des virgules', 'mkcrm' ),
				'add_or_remove_items' => __( 'Ajout ou suppression de serveur', 'mkcrm' ),
				'choose_from_most_used' => __( 'Choisir parmi les serveurs les plus utilisés', 'mkcrm' )
			),
			'public'	=> true,
			'show_ui'	=> true,
  			'show_in_nav_menus' => false,
			'show_tagcloud'	=> false,
			'show_admin_column' => true,
			'hierarchical'	=> false,
			//'update_count_callback' => ''
   			//query_var => ''
			'rewrite' => array( 'slug' => __('serveur', 'mkcrm'), 'with_front' => false, 'hierarchical' => false ),
  			//'capabilities' => ''
			'sort'	=> true
		)
	);
	
	// Déclaration des champs de métadonnées
	add_action ( 'mkcrm_server_edit_form_fields', 'mkcrm_server_edit_form_fields' );
	// Enregistrement des metadonnées
	add_action ( 'edited_mkcrm_server', 'mkcrm_server_edited_taxonomy' );
}
add_action('init', 'mkcrm_server_init');

/**
 * Affichage des champs de métadonnées
 */
function mkcrm_server_edit_form_fields( $tag ){
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e( 'Dossier des données de site', 'mkcrm' );?></label></th>
		<td>
			<input type="text" name="datas_dir" value="<?php echo get_metadata( 'taxonomy', $tag->term_id, 'datas_dir', true );?>"/>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label><?php _e( 'Dossier des sauvegarde', 'mkcrm' );?></label></th>
		<td>
			<input type="text" name="backup_dir" value="<?php echo get_metadata( 'taxonomy', $tag->term_id, 'backup_dir', true );?>"/>
		</td>
	</tr>
<?php
}

/**
 * Enregistrement des valeurs de métadonnées
 */
function mkcrm_server_edited_taxonomy( $term_id ){
	foreach( array( 'datas_dir', 'backup_dir' ) as $index )
		if ( isset( $_POST[$index] ) )
			update_metadata( 'taxonomy', $term_id, $index, $_POST[$index] );
		else
			delete_metadata( 'taxonomy', $term_id, $index );
}