<?php
/*
Plugin Name: Debug Bar
Plugin URI: http://presstify.com/dev-tools/addons/debugbar
Description: Barre de développement
Version: 1.150130
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_devtools_debugbar{
	var $tiFy, $dir, $path, $uri;
	public $timestart;
	
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
		
		// Actions et Filtres Wordpress
		add_action( 'muplugins_loaded', array( $this, 'muplugins_loaded' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		if( isset( $_REQUEST['debugbar'] ) ) :
			add_action( 'wp_head', create_function( '', 'echo "<base target=\"_parent\" />";'), 1 );
		else :
			add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );
			add_filter( 'body_class', array( $this, 'body_class' ), 10, 2 );			
		endif;
	}
	
	/**
	 * 
	 */
	function muplugins_loaded(){
		$this->timestart=microtime(true);
	}
	
	
	/**
	 *
	 */
	function init(){
		wp_register_style( 'tify-debugbar', $this->uri.'/debugbar.css', array( 'dashicons', 'font-awesome' ), '150130' );
		wp_register_script( 'tify-debugbar', $this->uri.'/debugbar.js', array( 'jquery', 'jquery-ui-resizable' ), '150130', true );
	}
	
	/**
	 * 
	 */
	function wp_enqueue_scripts(){
		wp_enqueue_style( 'tify-debugbar' );
		wp_enqueue_script( 'tify-debugbar' );
	}
	
	/**
	 * 
	 */
	 function body_class( $classes, $class ){
	 	$classes[] = 'tify-debugbar';

		return $classes;
	 }
	
	/**
	 * 
	 */
	function wp_footer(){		
	?><div id="tify-debugbar">
		<ul>
			<li class="version">
			<?php global $wp_version; ?>
				<i class="dashicons dashicons-wordpress-alt"></i>
				<?php printf( __( 'v. %s', 'tify'), $wp_version );?>
			</li>
			<li class="execution_time">
			<?php $timeend = microtime(true); $time = $timeend-$this->timestart; $page_load_time = number_format($time, 3);?>
				<i class="dashicons dashicons-backup"></i>
				<?php printf( __( '%s sec.', 'tify' ), $page_load_time );?>
			</li>
			<li class="responsive">
			<?php global $wp; $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );?>
				<ul class="context">
					<li>
						<a href="#" data-width="767px" class="dashicons dashicons-smartphone" title="<?php _e( 'Smartphone', 'tify' );?>"></a>
					</li>
					<li>
						<a href="#" data-width="991px" class="dashicons dashicons-tablet" title="<?php _e( 'Tablette', 'tify' );?>"></a>
					</li>
					<li>
						<a href="#" data-width="1199px" class="dashicons dashicons-desktop" title="<?php _e( 'Plein écran', 'tify' );?>"></a>
					</li>
					<li class="active">
						<a href="#" data-width="100%" class="dashicons dashicons-editor-expand fullscreen" title="<?php _e( 'Plein écran', 'tify' );?>"></a>
					</li>
				</ul>				
				<span class="size"><strong class="width"></strong>x<strong class="height"></strong></span>
				<div id="tify-debugbar-resize">
					<div class="overlay"><i class="fa fa-spinner fa-pulse"></i></div>
					<iframe src="<?php echo add_query_arg( 'debugbar', 'true', $current_url );?>" style="display:block; border:0 none; overflow:auto;" width="100%" height="100%"></iframe>
				</div>
			</li>
		</ul>
	</div><?php 
	}
}
new tiFy_devtools_debugbar();