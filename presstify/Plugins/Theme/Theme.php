<?php
/*
 Plugin Name : Theme
 Plugin URI: http://presstify.com/plugins/theme
 Description: Générateur de thème
 Version: 1.141127
 Author: Milkcreation
 Author URI: http://milkcreation.fr
 Text Domain: tify
*/

namespace tiFy\Plugins\Theme;

use tiFy\Environment\Plugin;

class Theme extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'admin_init',
		'tify_options_register_node'
	);
	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
		'admin_init' => 'wp_admin_css_color'	
	);
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// 
		if( $namespace = self::getConfig( 'namespace' ) ) :
			$dirname = self::getConfig( 'dirname' ) ? self::getConfig( 'dirname' ) : get_template_directory();

			$loader = new \Psr4ClassLoader;
			$loader->addNamespace( "\\". $namespace, $dirname );
			$loader->register();
			
			foreach( glob( $dirname. '/*.php' ) as $filename ) :			
				$ClassName = basename( $filename, '.php' );
				
				if( ! class_exists( "\\". $namespace. "\\". $ClassName  ) )
					continue;
				$ClassName = "\\". $namespace. "\\". $ClassName;
				
				$reflection = new \ReflectionAnnotatedClass( $ClassName );
				if( $reflection->hasAnnotation( 'Autoload' ) ) :
					new $ClassName;
				endif;
			endforeach;
		endif;
	}	
	
	/* = Initialisation de Wordpress = */
	final public function init()
	{
		require_once $this->Dirname .'/inc/Helpers.php';
	}
	
	/* = Modification des couleurs du thème de base de Wordpress (compatibilité icônes SVG = */
	final public function wp_admin_css_color()
	{
		global $_wp_admin_css_colors;
	
		$_wp_admin_css_colors['fresh']->icon_colors['base'] = '#D1D1D1';
	}
	
	/* = Déclaration des boîtes à onglets d'options = */
	final public function tify_options_register_node()
	{
		if( in_array( 'palette', self::getConfig( 'tify_options' ) ) ) :		
			tify_options_register_node(
				array(
					'id' 		=> 'tify_theme',
					'title' 	=> __( 'Apparence du thème', 'tify' )
				)
			);

			tify_options_register_node(
				array(
					'id' 		=> 'tify_theme-color_palette',
					'parent' 	=> 'tify_theme',
					'title' 	=> __( 'Palette de couleurs', 'tify' ),
					'cb'		=> "\\tiFy\\Core\\Taboox\\Option\\ColorPalette\\Admin\\ColorPalette",
					'args'		=> array( 'name' => 'tify_theme_color_palette', 'colors' => self::getColors() )
				)
			);
		endif;
	}
	
	/* = Récupération de la liste des couleurs = */
	static public function getColors()
	{		
		if( $palette = get_option( 'tify_theme_color_palette', false ) ) :
			$colors = $palette['colors'];
			$sort	= $palette['order'];
		
			@array_multisort( $colors, $sort, ASC );
		
			return $colors;
		else :		
			$colors = array();
			foreach( (array) self::getConfig( 'colors' ) as $attr )	:
				$colors[$attr['name']] = $attr['hex'];
			endforeach;
			
			return $colors;
		endif;
	}
}