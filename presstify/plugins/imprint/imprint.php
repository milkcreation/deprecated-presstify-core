<?php
/*
Plugin Name: Imprint Page
Plugin URI: http://presstify.com/policy/addons/imprint
Description: Page des mentions légales
Version: 1.150925
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/* = HELPER = */
/** == Affichage du lien vers les Mentions Légales == 
 * @todo DEPRECATED => suffixe en tify
 */
function mktzr_imprint_display(){
	$page_for_imprint = get_option( 'page_for_imprint');
	$output  = "";
	$output .=  "<a href=\"". ( $page_for_imprint ? get_permalink( $page_for_imprint ) : '#' ) ."\""
				. " title=\"". sprintf( __( 'En savoir plus sur %s', 'tify' ), ( $page_for_imprint ? get_the_title( $page_for_imprint ) : __( 'Les mentions légales', 'tify' ) ) ) ."\">"
				. ( $page_for_imprint ? get_the_title( $page_for_imprint ) : __( 'Mentions légales', 'tify' ) )
				. "</a>";
	
	echo $output;
}

/* = CLASSE = */
class tiFy_Imprint{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */
	public function __construct(){
		// Actions et Filtres Wordpress		 
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	function wp_admin_init(){
		register_setting( 'reading', 'page_for_imprint' );
		add_settings_section( 
			'mktzr_imprint_reading_section', 
			__( 'Mentions légales', 'bigben' ), 
			null,
			'reading' 
		);
		add_settings_field( 'page_for_imprint', __( 'Page d\'affichage des mentions légales', 'bigben' ), array( $this, 'setting_field_render' ), 'reading', 'mktzr_imprint_reading_section' );
	}
	
	/* = VUES = */
	/** == Rendu des options == **/
	public function setting_field_render(){
		wp_dropdown_pages( 
			array( 
				'name' 				=> 'page_for_imprint', 
				'post_type' 		=> 'page', 
				'selected' 			=> get_option( 'page_for_imprint', false ), 
				'show_option_none' 	=> __( 'Aucune page choisie', 'bigben' ), 
				'sort_column' 		=> 'menu_order' 
			) 
		);
	}
}
new tiFy_Imprint;