<?php
/*
Plugin Name: Multilingual
Plugin URI: http://presstify.com/multilingual
Description: Gestion de site multilingue basée sur Wordpress multisite
Version: 1.141127
Author: Milkcreation
Author URI: http://milkcreation.fr
Text Domain: tify_multilingual
*/

namespace tiFy\Plugins\Multilingual; 

use tiFy\Environment\Plugin;

class Multilingual extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'setup_theme',
		'wp_print_styles',
		'admin_print_styles',
		'admin_bar_menu',
		'tify_taboox_register_form'
	);
	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
		'wp_print_styles' 		=> 'print_styles',
		'admin_print_styles'	=> 'print_styles'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'admin_bar_menu' => 99
	);
	
	/** == CONFIGURATION == **/
	// 
	public static $Flags				= array();
	//
	public static $AvailableLanguages	= array();
	//
	public static $Sites				= array();
	//
	public static $Translations		= array();
	
	
	/* = CONTRUCTEUR = */
	public function __construct()
	{
		if( ! is_multisite() )
			return;
		
		parent::__construct();
			
		// Définition des variables d'environnement
		/// Définition des traduction de locales
		if( ! function_exists( 'wp_get_available_translations' ) )
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		
		self::$AvailableLanguages 		= get_available_languages();
		self::$Translations 			= wp_get_available_translations();
		self::$Translations['en_US'] 	= array(
			'language' 		=> 'en_US',
			'english_name' 	=> 'English (United States)',
			'native_name'  	=> 'English (United States)',
			'iso'			=> array( 1 => 'en' )
		);

		/// Récupération de la liste des sites
		self::$Sites = wp_get_sites();

		/// Récupération des drapeaux par pays de site
		self::$Flags = $this->getFlags();
		
		// Chargement des Helpers
		require_once( $this->Dirname .'/Helpers.php' );
	}
	
	/* = ACTIONS WORDPRESS = */
	/** == Au chargement du thème == **/
	final public function setup_theme()
	{
		if( ! get_option( 'tify_multilingual_adminlang', false ) )
			return;
		
		add_filter( 'locale', function( $locale = null ){
			if( is_admin() )
				$locale = get_option( 'tify_multilingual_adminlang', 0 );
			return $locale;
		});
	}
	
	/** == Styles de la barre d'administration == **/
	final public function print_styles()
	{
	?><style type="text/css">#wp-admin-bar-my-sites .tify_multilingual-flag{width:30px;height:18px;vertical-align:middle;margin-right:5px;}</style><?php
	}
	
	/** == Personnalisation de la barre d'administration ==  
 	 * @see http://fr.wikipedia.org/wiki/ISO_3166-1
 	 * @see http://wpcentral.io/internationalization/
 	 */
	final public function admin_bar_menu( $wp_admin_bar )
	{		
		foreach( self::$Sites as $site ) :
			$blog_id = $site['blog_id'];
			$locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			$wp_admin_bar->add_node(
				array( 
					'id' => 'blog-'. $blog_id,
					'title' => ( ( isset( self::$Flags[$blog_id] ) )? self::$Flags[$blog_id] : '' ) . get_blog_option( $blog_id, 'blogname' ),
					'parent' => 'my-sites-list',
					'href' => get_admin_url( $blog_id )
				)
			);
		endforeach;
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY  = */
	/** == Déclaration de taboox == **/
	final public function tify_taboox_register_form()
	{
		//tify_taboox_register_form( 'tiFy_Multilingual_MenuSwitcher_Taboox', $this );
		//tify_taboox_register_form( 'tiFy_Multilingual_AdminLang_Taboox', $this );
	}
	
	/** == CONTRÔLEUR == **/
	/** == Récupération des drapeaux == **/
	function getFlags()
	{
		$flags = array();
		foreach( self::$Sites as $site ) :
			$blog_id 	= $site['blog_id'];
			$locale 	= ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			$filename 	= $this->Dirname .'/flags/'.$locale.'.svg';
			
			if( ! file_exists( $filename ) )
				continue;

			if( $src = tify_svg_img_src( $filename ) )
				$flags[ $blog_id ] = "<img src=\"$src\" class=\"flag tify_multilingual-flag tify_multilingual-flag-{$locale}\" />";
		endforeach;
		
		return $flags;
	}
	
	/* = TEMPLATES = */
	/** == Selecteur de langage == **/
	final public static function tplSwitcher( $args = array( ) )
	{
		static $instance = 0;
		$instance++; 
		
		$defaults = array(
			'id'		=> 'tify_multilingual_switcher-'. $instance,
			'selected' 	=> get_current_blog_id(),
			'display'	=> 'dropdown',
			'separator'	=> '&nbsp;|&nbsp;',
			'label'		=> 'iso', // iso -ex : fr | language - ex: fr_FR | english_name - ex : French (France) | native_name - ex : Français
			'labels'	=> array( ), // Intitulés personnalisés, tableaux indexés par blog_id
			'flag'		=> false,
			'echo'		=> true
		);
		$args = wp_parse_args( $args, $defaults );

		// Création des liens
		$args['links'] = array();
		foreach( self::$Sites as $site ) :			
			$blog_id = $site['blog_id'];
			$locale = ( $_locale = get_blog_option( $blog_id, 'WPLANG' ) ) ? $_locale: 'en_US';
			if( ! empty( $args['labels'][$blog_id] ) )
				$label = $args['labels'][$blog_id];
			elseif( isset( self::$Translations[$locale] ) )
				$label = ( $args['label'] === 'iso' ) ?  self::$Translations[$locale]['iso'][1] : self::$Translations[$locale][ $args['label'] ];
			else
				$label = $locale;
			
			$args['links'][$blog_id]  = "";			
			$args['links'][$blog_id] .= "<a href=\"". get_site_url( $blog_id ) ."\"". ( $args['selected'] == $blog_id ? ' class="selected"' : '' ).">";
			if( $args['flag'] && isset( self::$Flags[$blog_id] ) )
				$args['links'][$blog_id] .=	self::$Flags[$blog_id];
			$args['links'][$blog_id] .= $label ."</a>";
		endforeach;

		$output = "";
		if( $args['display'] == 'dropdown' ) :
			$_args = $args;
			$_args['echo'] = false;
			
			$output .= tify_control_dropdown_menu( $_args );
		elseif( $args['display'] == 'inline' ) :
			$output .= "<div id=\"{$args['id']}\" class=\"tify_multilingual_switcher-inline\">". implode( $args['separator'], $args['links'] ) ."</div>";
		elseif( $args['display'] == 'list' ) :
			$output .= 	"<div id=\"{$args['id']}\" class=\"tify_multilingual_switcher-inline\">\n".
						"\t<ul>\n";
			foreach( $args['links'] as $link )
				$output .= "\t\t<li>". $link ."</li>\n";
			$output .= "\t</ul>\n";
			$output .= "</div>\n";
		endif;
		
		if( $args['echo'] )
			echo $output;
		
		return $output;
	}
}