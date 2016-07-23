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
 */

class mktzr_outdated{
	public 	$options;
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Actions et filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) ); 
	}
		
	/**
	 * Définition des options
	 * @see https://github.com/burocratik/Outdated-Browser/tree/master
	 * 
	 * Lower Than (<):
	    "IE11","borderImage"
	    "IE10", "transform" (Default property)
	    "IE9", "boxShadow"
	   	"IE8", "borderSpacing"
	 */
	function set_options( $args ){
		$defaults = array(
			'bgColor'		=> '#f25648',
	        'color'			=> '#ffffff',
	        'lowerThan'		=> 'transform',
	        'languagePath' 	=> ''
		);
		$this->options = wp_parse_args( $args, $defaults );			
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		// Définition des options
		$this->set_options( apply_filters( 'mktzr_outdated_options', array() ) );

		// Déclaration des scripts
		wp_register_style( 'outdated-browser', '//cdn.rawgit.com/burocratik/outdated-browser/develop/outdatedbrowser/outdatedbrowser.min.css', array(), '1.1.2' );
		wp_register_script( 'outdated-browser', '//cdn.rawgit.com/burocratik/outdated-browser/develop/outdatedbrowser/outdatedbrowser.min.js', array(), '1.1.2'  );
	}
		
	/** == Mise en file des scripts == **/
	function wp_enqueue_scripts(){
		wp_enqueue_style( 'outdated-browser' );
		wp_enqueue_script( 'outdated-browser' );
	}
	
	/** == == **/
	function wp_head(){
	?><style type="text/css">#outdated{z-index:9999999;}</style><?php
	}
		
	/** == Scripts du pied de page == **/
	function wp_footer( ){
		$output  = "";	
		$output .= "\t<div id=\"outdated\">\n";
		$output .= "\t\t<h6>" .__( 'La version de votre navigateur est trop ancienne', 'tify' ). "</h6>\n";
		$output .= "\t\t<p>" .__( 'Pour afficher de manière satisfaisante le contenu de ce site', 'tify' ). "<a id=\"btnUpdateBrowser\" href=\"http://outdatedbrowser.com/fr\" target=\"_blank\">" .__( 'Télécharger Google Chrome', 'tify' ). "</a></p>\n";
		$output .= "\t\t<p class=\"last\"><a href=\"#\" id=\"btnCloseUpdateBrowser\" title=\"" .__( 'Fermer', 'tify' ). "\">&times;</a></p>\n";
		$output .= "\t</div>";
		$output  = apply_filters( 'mktzr_outdated_display', $output, $this->options );
		
		$output .= "\t<script type=\"text/javascript\">/* <![CDATA[ */\n";
		$output .= "\t\tjQuery( document ).ready(function($) {\n";
	    $output .= "\t\t\toutdatedBrowser({\n";
	    $output .= "\t\t\t\tbgColor: '". $this->options['bgColor'] ."',\n";
	    $output .= "\t\t\t\tcolor: '". $this->options['color'] ."',\n";
	    $output .= "\t\t\t\tlowerThan: '". $this->options['lowerThan'] ."',\n";
	    $output .= "\t\t\t\tlanguagePath: '". $this->options['languagePath'] ."'\n";
	    $output .= "\t\t\t});\n";
	    $output .= "\t\t});\n";
	    $output .= "\t/* ]]> */</script>\n";
	
		echo $output;	
	}	
}
new mktzr_outdated;