<?php
/*
Plugin Name: Minify
Plugin URI: http://presstify.com/theme_manager/addons/minify
Description: Minification des scripts
Version: 1.150130
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_devtools_minify{
	var $tiFy, $dir, $path, $uri;
	
	/**
	 * 
	 */
	function __construct() {
		global $tiFy;
		
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;

		// Chargement de la librairie Minify
		require_once $this->dir .'/minify-master/min/lib/Minify.php';
		require_once $this->dir .'/minify-master/min/lib/Minify/Loader.php';
		Minify_Loader::register();		

		add_action( 'wp_head', array( $this, 'wp_head' ), 1 );	
	}
	
	/**
	 * 
	 */
	function wp_head(){
		global $wp_styles, $wp_scripts;

		new tiFy_Minify_Styles( array( 'wp_styles' => $wp_styles, 'uri' => $this->uri ) );
		new tiFy_Minify_Scripts( array( 'wp_scripts' => $wp_scripts, 'uri' => $this->uri ) );
	}

}
new tiFy_devtools_minify();

/**
 *
 */
class tiFy_Minify_Styles extends WP_Dependencies {
	// WP_Dependencies
	public $registered = array();
	public $queue = array();
	public $to_do = array();
	public $done = array();
	public $args = array();
	public $groups = array();
	public $group = 0;
	// tiFy_Minify_Styles
	public $wp_styles;
	public $uri;
	public $concat = array();
	
	/**
	 * Initialisation de la classe
	 */
	function __construct( $args = array() ){
		extract( $args );
	
		foreach( array( 'wp_styles', 'uri' ) as $arg )
			if( isset( ${$arg} ) )
				$this->{$arg} = ${$arg};
				
		add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ), 100 );
	}
	
	/**
	 *
	 */
	function parse_concat(){
		$defaults = array(
			'src' 		=> array(),
			'deps' 		=> array(),
			'handles' 	=> array()
		);
		$this->concat = wp_parse_args( $this->concat, $defaults );
	}
	
	/**
	 *
	 */
	function add_concat( $handle, $src, $deps = array() ){
		// Sources
		array_push( $this->concat['src'], '//' .trim( $src, '/' ) );
		// Accroches
		array_push( $this->concat['handles'], $handle );
		// Dépendances
		foreach( $deps as $dep )
			array_push( $this->concat['deps'], $dep );
	}
	
	/**
	 *
	 */
	function wp_print_styles(){
		if( is_admin() )
			return;
		
		$this->parse_concat();
		
		foreach( $this->wp_styles->registered as $reg ) :
			$this->add( $reg->handle, $reg->src, $reg->deps, $reg->ver, $reg->args );
			foreach( (array)  $reg->extra as $key => $value )
				$this->add_data( $reg->handle, $key, $value );
		
			if( ! in_array( $reg->handle, $this->wp_styles->queue ) )
				continue;
		
			$this->enqueue( $reg->handle );
		endforeach;
	
		$this->do_items( );
		if( ! empty( $this->concat['src'] ) ) :
			$this->concat['deps'] = array_diff( $this->concat['deps'], $this->concat['handles'] );
			$this->wp_styles->add( 'minify', $this->uri .'/minify-master/min/f='. join( ',', array_map( create_function( '$src', 'return trim( $src, "/" );' ), $this->concat['src'] ) ), $this->concat['deps'] );
			$this->wp_styles->enqueue( 'minify' );
		endif;
		
		// Génération de la sortie
		//var_dump( Minify::combine( $this->concat['src'] ) ); exit;
	}
	
	/**
	 *
	 */
	public function do_item( $handle ) {
		if ( !parent::do_item($handle) )
			return false;
		
		$src = $this->registered[$handle]->src;
		
		if( preg_match( '#'. preg_quote( site_url() ) .'#', $src ) )
			$src = preg_replace( '#'. preg_quote( site_url() ) .'#', '', $src );
		
		if( ! file_exists( ABSPATH.$src ) )
			return false;
		
		$this->wp_styles->dequeue( $handle );
		
		$this->add_concat( $handle, $src, $this->registered[$handle]->deps );
	
		return true;
	}
}

/**
 *
 */
class tiFy_Minify_Scripts extends WP_Dependencies {
	// WP_Dependencies
	public $registered = array();
	public $queue = array();
	public $to_do = array();
	public $done = array();
	public $args = array();
	public $groups = array();
	public $group = 0;
	// WP_Scripts
	public $in_footer = array();
	// tiFy_Minify_Scripts	
	public $wp_scripts;
	public $uri;
	public $concat = array();
	
	/**
	 * Initialisation de la classe
	 */
	function __construct( $args = array() ){
		extract( $args );
		
		foreach( array( 'wp_scripts', 'uri' ) as $arg )
			if( isset( ${$arg} ) )
				$this->{$arg} = ${$arg};
			
		add_action( 'wp_print_scripts', array( $this, 'wp_print_scripts' ), 100 );
	}
	
	/**
	 * 
	 */
	function parse_concat(){
		$defaults = array( 
			'head' => array(	
				'src' 		=> array(),
				'deps' 		=> array(),
				'handles' 	=> array()	
			),			
			'footer' => array(
				'src' 		=> array(),
				'deps' 		=> array(),
				'handles' 	=> array()
			)
		);
		$this->concat = wp_parse_args( $this->concat, $defaults );
	}
	
	/**
	 * 
	 */
	function add_concat( $handle, $src, $deps = array(), $group = 0 ){
		$g = ( 0 === $group )? 'head' :'footer';
		// Sources
		array_push( $this->concat[$g]['src'], '//'.trim( $src, '/' ) );
		// Accroches
		array_push( $this->concat[$g]['handles'], $handle );
		// Dépendances
		foreach( $deps as $dep )
			array_push( $this->concat[$g]['deps'], $dep );
	}
	
	/**
	 * 
	 */
	function wp_print_scripts(){
		if( is_admin() )
			return;
		
		$this->parse_concat();
		
		foreach( $this->wp_scripts->registered as $reg ) :
			$this->add( $reg->handle, $reg->src, $reg->deps, $reg->ver, $reg->args );
			foreach( (array)  $reg->extra as $key => $value )
				$this->add_data( $reg->handle, $key, $value );
				
			if( ! in_array( $reg->handle, $this->wp_scripts->queue ) )
				continue;
				
			$this->enqueue( $reg->handle );		
		endforeach;
		
		$this->do_head_items( );			
		if( ! empty( $this->concat['head']['src'] ) ) :	
			$this->concat['head']['deps'] = array_diff( $this->concat['head']['deps'], $this->concat['head']['handles'] );
			$this->wp_scripts->add( 'minify', $this->uri .'/minify-master/min/f='. join( ',', array_map( create_function('$src', 'return trim( $src, "/" );'), $this->concat['head']['src'] ) ), $this->concat['head']['deps'] );
			$this->wp_scripts->enqueue( 'minify' );
		endif;

		$this->do_footer_items( );
		if( ! empty( $this->concat['footer']['src'] ) ) :
			$this->concat['footer']['deps'] = array_diff( $this->concat['footer']['deps'], $this->concat['footer']['handles'] );
			$this->wp_scripts->add( 'minify-footer', $this->uri .'/minify-master/min/f='. join( ',', array_map( create_function('$src', 'return trim( $src, "/" );'), $this->concat['footer']['src'] ) ), $this->concat['footer']['deps'] );
			$this->wp_scripts->add_data( 'minify-footer', 'group', 1 );
			$this->wp_scripts->enqueue( 'minify-footer' );
		endif;
		// Génération de la sortie
		//var_dump( Minify::combine( $this->concat['footer']['src'] ) ); exit;
	}

	/**
	 *
	 */
	public function do_item( $handle, $group = false ) {
		if ( !parent::do_item($handle) )
			return false;

		if ( 0 === $group && $this->groups[$handle] > 0 ) {
			$this->in_footer[] = $handle;
			return false;
		}
	
		if ( false === $group && in_array($handle, $this->in_footer, true) )
			$this->in_footer = array_diff( $this->in_footer, (array) $handle );
		
		$src = $this->registered[$handle]->src;
		
		if( preg_match( '#'. preg_quote( site_url() ) .'#', $src ) )
			$src = preg_replace( '#'. preg_quote( site_url() ) .'#', '', $src );
		
		if( ! file_exists( ABSPATH.$src ) )
			return false;

		// @TODO à inclure dans la minification actuellement envoie en entête
		$this->wp_scripts->print_extra_script( $handle );
		$this->wp_scripts->dequeue( $handle );
		
		$this->add_concat( $handle, $src, $this->registered[$handle]->deps, $group );
		
		return true;
	}
	
	/**
	 * 
	 */
	public function set_group( $handle, $recursion, $group = false ) {	
		if ( $this->registered[$handle]->args === 1 )
			$grp = 1;
		else
			$grp = (int) $this->get_data( $handle, 'group' );
	
		if ( false !== $group && $grp > $group )
			$grp = $group;
	
		return parent::set_group( $handle, $recursion, $grp );
	}
	
	/**
	 * 
	 */
	public function do_head_items() {
		$this->do_items(false, 0);
		return $this->done;
	}
	
	/**
	 * 
	 */
	public function do_footer_items() {
		$this->do_items(false, 1);
		return $this->done;
	}
}