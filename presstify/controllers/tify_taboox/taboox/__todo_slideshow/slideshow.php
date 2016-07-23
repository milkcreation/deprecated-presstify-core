<?php
/*
Addon Name: Slideshow
Addon URI: http://presstify.com/theme_manager/addons/slideshow
Description: 
Version: 1.141213
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_editbox_slideshow{
	var $tiFy,
		$editbox,		
		$dir,
		$uri,
		$path,
		$options;
		
	/**
	 * Initialisation
	 */
	function __construct(){
		global $tiFy;
		
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;
		
		// Action et filtres Wordpress
		add_action( 'init', array( $this,'init' ) );
		add_action( 'admin_menu', array( $this,'admin_menu' ) );
		add_action( 'admin_init', array( $this,'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this,'admin_enqueue_scripts' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );
				
		add_action( 'wp_ajax_mk_home_slideshow_get_item_html', array( $this, 'ajax_get_item' ) );
	}
	
	/**
	 * Initialisation de Wordpress
	 */
	function init(){
		$this->options = apply_filters( 'tify_slideshow_options', array(
				'homeslideshow' => false,
			)
		);
	} 
	
	/**
	 * Menu de l'interface d'administration
	 */
	function admin_menu(){
		if( $this->options['homeslideshow'] )
			$option_page = add_theme_page(
				__( 'Diaporama', 'mktzr' ),
				__( 'Diaporama de l\'accueil', 'mktzr' ),
				'manage_options',
				'mk_home_slideshow_options',
				array( $this, 'admin_render' )
			);
	}
	
	/**
	 * Initialisation de l'interface d'administration
	 */
	function admin_init(){
		// Bypass
		if( ! $this->options['homeslideshow'] )
			return;
		
		// Déclaration des scripts
		wp_register_script( 'tinyMCE', includes_url( 'js/tinymce' ). '/tinymce.min.js', array(), ' 4.1.4', true );
		wp_register_script( 'jQuery-tinyMCE', '//cdnjs.cloudflare.com/ajax/libs/tinymce/4.1.4/jquery.tinymce.min.js', array( 'jquery', 'tinyMCE' ), true );
		wp_register_style( 'home-slideshow-options', $this->uri ."/css/home_slideshow-options.css", array(), '130608' );
		wp_register_script( 'home-slideshow-options', $this->uri ."/js/home_slideshow-options.js", array( 'jQuery-tinyMCE', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'tinyMCE' ), '130608', true );	
	
		// Section d'option	
		add_settings_section( 'posts', __('Diaporama', 'mktzr'), '__return_false', 'mk_home_slideshow_options' );		
		// Déclaration des options
		register_setting( 'mk_home_slideshow_options', 'mk_home_slideshow_items' );
	}
	
	/**
	 * Rendu de l'interface des options
	 */
	function admin_render(){
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Diaporama de la page d\'accueil', 'mktzr' ); ?></h2>
		<?php settings_errors(); ?>
		
		<form method="post" action="options.php">
			<?php settings_fields( 'mk_home_slideshow_options' );?>
			
			<?php mk_home_slideshow_options_render_panel();?>
			
			<?php do_settings_sections( 'mk_home_slideshow_custom_options' ); ?>
			<?php submit_button(); ?>			
		</form>			
	</div>
	<?php	
	}
	
	/**
	 * Mise en file des scripts
	 */
	function admin_enqueue_scripts( $hookname ){
		// Bypass
		if( ! $this->options['homeslideshow'] )
			return;		
		if( $hookname != 'appearance_page_mk_home_slideshow_options' )
			return;
		
		wp_enqueue_media();
		wp_enqueue_style( 'home-slideshow-options' );
		wp_enqueue_script( 'home-slideshow-options' );
		
		do_action('mk_home_slideshow_options_admin_enqueue_scripts', $hookname );
	}
	
	/**
	 * Barre d'administration
	 */
	function admin_bar_menu( $wp_admin_bar ){
		// Bypass
		if( is_admin() )
			return;
		if( ! $this->options['homeslideshow'] )
			return;
		
		// Ajout d'un lien de configuration du Diaporama
		$wp_admin_bar->add_node(
			array(
				'id' => 'mk_home_slideshow_options',
	    		'title' => __('Configurer le Diaporama', 'mktzr'),
	    		'href' => admin_url('/themes.php?page=mk_home_slideshow_options'),
	   			'parent' => 'site-name'
			)
		);
	}
	
	/**
	 * 
	 */
	function ajax_get_item(){
		echo mk_home_slideshow_item_html( array( 'post_id'=> $_POST['post_id'], 'order' =>$_POST['order'] ) );
		exit;
	}
}
new tiFy_editbox_slideshow;