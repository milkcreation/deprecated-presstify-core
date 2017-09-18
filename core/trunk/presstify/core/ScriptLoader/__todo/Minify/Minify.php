<?php
/*
Plugin Name: Minify
Plugin URI: http://presstify.com/plugins/minify
Description: Minification des scripts
Version: 2.160130
Author: Milkcreation
Author URI: http://milkcreation.fr
*/
namespace tiFy\Plugins\Minify;

use tiFy\Environment\Plugin;

class Minify extends Plugin
{
	public function __construct()
	{
		parent::__construct();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ), 1 );	
	}
	
	
	final public function init()
	{
		/*$path = preg_replace( '#'. preg_quote( site_url(), '/' ) .'#', '', self::tFyAppUrl());
		add_rewrite_rule('^min/f=(.*)?', $path .'/min/f=$matches[1]','top' );*/
	}
	
	final public function wp_head()
	{
		global $wp_styles, $wp_scripts;

		new Styles( array( 'wp_styles' => $wp_styles, 'uri' => self::tFyAppUrl()) );
		new Scripts( array( 'wp_scripts' => $wp_scripts, 'uri' => self::tFyAppUrl()) );
	}
}
new Minify();