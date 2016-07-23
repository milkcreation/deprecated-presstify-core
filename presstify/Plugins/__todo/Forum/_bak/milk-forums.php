<?php
/**
 * -------------------------------------------------------------------------------
 *	Bootstrap
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 *
Plugin Name: Forums
Plugin URI: http://wpextend.milkcreation.fr/forums
Version: 1.1 
Description: Wordpress plugin of forums based on comments
Author: Milkcreation - Jordy Manner
Author URI: http://profil.milkcreation.fr/jordy-manner
Text Domain: milk-forums
*/

/**
 * Constantes du plugin.
 */ 
define( 'MKFORUMS_DIR', dirname(__FILE__) );
define( 'MKFORUMS_URL', WP_PLUGIN_URL.'/'. str_replace( '/'. basename(__FILE__) , '' , plugin_basename(__FILE__) ) );

/**
 * 
 */
Class Milk_Forums {
	var $dir 	= MKFORUMS_DIR,
		$url 	= MKFORUMS_URL;
		
	/**
	 * Constructeur.
	 */
	function __construct(){
		// Translation du plugin
		load_plugin_textdomain( 'milk-forums', null, plugin_basename( dirname(__FILE__) ).'/languages/' );
		
		// Définition des chemins. 		
		$this->inc_dir		= $this->dir.'/inc';	
		
		// Initialisation des contrôleurs
		require_once $this->inc_dir. '/contribs.php';
		require_once $this->inc_dir. '/custom-columns.php';
		require_once $this->inc_dir. '/forums.php';
		require_once $this->inc_dir. '/general-template.php';
		require_once $this->inc_dir. '/install.php';
		require_once $this->inc_dir. '/meta-boxes.php';
		require_once $this->inc_dir. '/nav-menu.php';
		require_once $this->inc_dir. '/pluggable.php';
		require_once $this->inc_dir. '/options.php';
		require_once $this->inc_dir. '/query.php';
		require_once $this->inc_dir. '/topics.php';
		
		// Création du menu de l'interface d'administration.
		add_action( 'admin_menu',  array( &$this, 'admin_menu' ) );
		
		// Styles
		add_action('admin_head-post.php', array( &$this, 'admin_edit_styles') );	
		add_action('admin_head-edit.php', array( &$this, 'admin_edit_styles') );
		add_action('admin_head-post-new.php', array( &$this, 'admin_edit_styles') );
		add_action('admin_head-edit-comments.php', array( &$this, 'admin_edit_styles') );
		add_action('admin_head-edit-tags.php', array( &$this, 'admin_edit_styles') );
	}

	/**
	 * Création des entrées de menu d'administration. 
	 */ 
	function admin_menu(){
		$title = __('Milkcreation Forums', 'milk-forums' );	
		$slug =	"edit.php?post_type=mkforums";
		add_submenu_page( $slug, sprintf( __('All topics | %s', 'milk-forums' ), $title ), __( 'All topics', 'milk-forums' ), 'edit_posts', 'edit.php?post_type=mktopics'  );
		add_submenu_page( $slug, sprintf( __('Add topic | %s', 'milk-forums' ), $title ), __( 'Add topic', 'milk-forums' ), 'edit_posts', 'post-new.php?post_type=mktopics' );	
		add_submenu_page( $slug, sprintf( __('All contributions | %s', 'milk-forums' ), $title ), __( 'All contribs', 'milk-forums' ), 'edit_posts', 'edit-comments.php?post_type=mktopics'  );	
		add_submenu_page( $slug, sprintf( __('Topic\'s category | %s', 'milk-forums' ), $title ), __( 'Add topic category', 'milk-forums' ), 'edit_posts', 'edit-tags.php?taxonomy=mktopics-cats' );		
		add_submenu_page( $slug, sprintf( __('Topic\'s tag | %s', 'milk-forums' ), $title ), __( 'Add topic tag', 'milk-forums' ), 'edit_posts', 'edit-tags.php?taxonomy=mktopics-tags' );
	}

/**
	 * Styles de la page d'édition des cartes et zones de carte de l'interface d'administration.
	 */
	function admin_edit_styles(){
		?><style type="text/css">
			#icon-edit.icon32-posts-mkforums,
			#icon-edit.icon32-posts-mktopics {
				background: url(<?php echo MKFORUMS_URL.'/images/cup-32x32.png'; ?>) no-repeat scroll;
			} 
		</style><?php	
	}
	
}
New Milk_Forums;