<?php
/**
 * -------------------------------------------------------------------------------
 *	General template
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Site Blocker
 * @package    	Worpdress Extend Milkcreation Pack
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/site-blocker
 * @author 		Jordy Manner
 * @version 	1.1

Plugin Name: Site Blocker
Plugin URI: http://wpextend.milkcreation.fr/site-blocker
Version:1.1
Description: Wordpress plugin of site blocking
Author: Milkcreation - Jordy Manner
Author URI: http://profil.milkcreation.fr/jordy-manner 
Text Domain: milk-site-blocker
*/

define( 'MKBCKR_DIR', dirname(__FILE__) );
define( 'MKBCKR_URL', WP_PLUGIN_URL.'/'. str_replace( '/'. basename(__FILE__) , '' , plugin_basename(__FILE__) ) );

class Milk_Site_Blocker{
	 var $dir = MKBCKR_DIR, $url = MKBCKR_URL, $inc = 'inc', $inc_dir, $inc_url;

	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin	
		load_plugin_textdomain( 'milk-site-blocker', null, plugin_basename( MKBCKR_DIR.'/languages/' ) );
			
		// Création des chemins
		$this->inc_dir = $this->dir.'/'.$this->inc;
		$this->inc_url = $this->url.'/'.$this->inc;
		
		require_once $this->inc_dir.'/options.php';
	}
}
New Milk_Site_Blocker();

/**
 * 
 */
function mkbkr_init(){
	if( is_admin() )
		return;
	if( ! mkbr_is_active() )
		return;
	
	wp_enqueue_style( 'colorbox', plugins_url( 'css/colorbox.css', __FILE__ ), '', '1.3.19.3' );
	wp_enqueue_style( 'site-blocker-theme', plugins_url( 'themes/default/styles.css', __FILE__ ), '', '120806' );
	wp_enqueue_script( 'colorbox', plugins_url( 'js/jquery.colorbox-min.js', __FILE__ ), array( 'jquery'), '1.3.19.3', true );
}
add_action( 'init', 'mkbkr_init' );

/**
 * 
 */
function mkbkr_footer_script(){
	if( ! mkbr_is_active() )
		return;
?><script type="text/javascript">/* <![CDATA[ */
	var title = <?php echo json_encode( get_option('mkbkr_options_title', mkbr_get_default_option( 'mkbkr_options_title' ) ) );?>;
	var content = <?php echo json_encode( get_option('mkbkr_options_content', mkbr_get_default_option( 'mkbkr_options_content' ) ) );?>;

	jQuery(document).ready( function($){
		$.colorbox({
			html:"<h3>"+title+"</h3><p>"+content+"</p>",
			opacity: 1,
			open: true,
			escKey: false,
			overlayClose: false,
			onLoad: function() {
    			$('#cboxClose').remove();
    			$( 'body' ).css({ 'overflow':'hidden', 'height':'100%'});
				$( 'body' ).css({ margin:0, padding:0 });
			},
			width: '50%',
			height: '50%',
			scrolling: false,
			fixed: true	
		});
	});
/* ]]> */</script><?php
}
add_action( 'wp_footer', 'mkbkr_footer_script', 99 );
