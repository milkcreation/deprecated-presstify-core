<?php
/*
Plugin Name: Outdated Browser
Plugin URI: http://presstify.com/policy/addons/outdated_browser
Description: Avertisseur de Navigateur déprécié
Version: 1.150505
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * @see http://outdatedbrowser.com/fr
 * @see https://github.com/burocratik/Outdated-Browser/tree/master
 * 
 * Lower Than (<):
    "IE11","borderImage"
    "IE10", "transform" (Default property)
    "IE9", "boxShadow"
   	"IE8", "borderSpacing"
 */
namespace tiFy\Plugins\OutdatedBrowser; 

use tiFy\Environment\Plugin;

class OutdatedBrowser extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts',
		'wp_footer'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'wp_footer' => 99
	);
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	final public function init()
	{
		// Déclaration des scripts
		wp_register_style( 'outdated-browser', '//cdn.rawgit.com/burocratik/outdated-browser/develop/outdatedbrowser/outdatedbrowser.min.css', array(), '1.1.2' );
		wp_register_script( 'outdated-browser', '//cdn.rawgit.com/burocratik/outdated-browser/develop/outdatedbrowser/outdatedbrowser.min.js', array( 'jquery' ), '1.1.2', true );
	}
		
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_style( 'outdated-browser' );
		wp_enqueue_script( 'outdated-browser' );
	}
			
	/** == Scripts du pied de page == **/
	final public function wp_footer( )
	{
		$output  = "";	
		$output .= "\t<div id=\"outdated\" style=\"z-index:9999999;\">\n";
		$output .= "\t\t<h6>" .__( 'La version de votre navigateur est trop ancienne', 'tify' ). "</h6>\n";
		$output .= "\t\t<p>" .__( 'Pour afficher de manière satisfaisante le contenu de ce site', 'tify' ). "<a id=\"btnUpdateBrowser\" href=\"http://outdatedbrowser.com/fr\" target=\"_blank\">" .__( 'Télécharger Google Chrome', 'tify' ). "</a></p>\n";
		$output .= "\t\t<p class=\"last\"><a href=\"#\" id=\"btnCloseUpdateBrowser\" title=\"" .__( 'Fermer', 'tify' ). "\">&times;</a></p>\n";
		$output .= "\t</div>";
		$output .= "\t<script type=\"text/javascript\">/* <![CDATA[ */\n";
		$output .= "\t\tjQuery( document ).ready( function($) {\n";
	    $output .= "\t\t\toutdatedBrowser({\n";
	    $output .= "\t\t\t\tbgColor: '". self::getConfig( 'bgColor' ) ."',\n";
	    $output .= "\t\t\t\tcolor: '". self::getConfig( 'color' ) ."',\n";
	    $output .= "\t\t\t\tlowerThan: '". self::getConfig( 'lowerThan' ) ."',\n";
	    $output .= "\t\t\t\tlanguagePath: '". self::getConfig( 'languagePath' ) ."'\n";
	    $output .= "\t\t\t});\n";
	    $output .= "\t\t});\n";
	    $output .= "\t/* ]]> */</script>\n";
	
		echo $output;	
	}	
}