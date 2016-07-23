<?php
namespace Theme;

use \tiFy\Environment\App;

class Theme extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
			'after_setup_theme',
			'login_headerurl',
			'login_headertitle'
	);

	/* = Initialisation du thème = */
	final public function after_setup_theme()
	{
		global $content_width;

		if ( ! isset( $content_width ) )
			$content_width = 1140;

			// Traduction du thème
			//load_theme_textdomain( 'bigben', get_template_directory() . '/languages' );

			// Style de l'éditeur
			$editor_styles = array(
				tify_style_get_src( 'bootstrap' ),
				get_template_directory_uri().'/css/editor-style-helpers.css',
				get_template_directory_uri().'/css/editor-style-custom.css',
				get_template_directory_uri().'/css/editor-style.css',			
				'//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700'
			);			
			array_walk( 
				$editor_styles,
				function( &$url ){
					return $url = str_replace( ',', '%2C', $url );
			});
			add_editor_style( $editor_styles );

			// Déclaration des menus
			//add_theme_support( 'menus' );
			//register_nav_menu( 'sitemap-navigation-menu', __( 'Menu de navigation du pied de page', 'bigben' ) );

			// Support des miniatures de post
			add_theme_support( 'post-thumbnails' );
			// Définition des tailles personnalisées des miniatures
			//add_image_size( 'archive', 480, 280, true );
			//add_image_size( 'single', 640, 9999, false );

			// Impose les dimension de l'image large
			add_filter( 'option_large_size_w', create_function( '', "return $content_width;" ) );
			add_filter( 'option_large_size_h', create_function( '', 'return 9999;' ) );

			remove_action( 'wp_head', 'wp_generator' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
			remove_action( 'wp_head', 'wp_dlmp_l10n_style' );
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );

			add_filter( 'use_default_gallery_style', '__return_false' );
	}

	/* = PAGE DE LOGIN = */
	/** == Url du logo de l'entête == **/
	final public function login_headerurl()
	{
		return home_url();
	}

	/** == Titre du logo de l'entête == **/
	final public function login_headertitle()
	{
		return get_bloginfo('name') . ' | ' . get_bloginfo('description');
	}
}