<?php
/**
 * --------------------------------------------------------------------------------
 *	Tools
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Regenerate Thumbnails
 * @package    	Worpdress Extends Milkcreation Suite
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/regenerate-thumbnails
 * @author 		Jordy Manner
 * @version 	1.1
 */ 

/**
 * Menu d'administration
 */ 
function mk_post_thumbnails_management_menu() {
	add_management_page(
		__( 'Post Thumbnails Tools', 'mkact-addon-thb' ),
		__( 'Post Thumbnails', 'mkact-addon-thb' ),
		apply_filters( 'mk_post_thumbnails_capability', 'manage_options' ),
		'mk_post_thumbnails_tools',
		'mk_post_thumbnails_render_page'
	);
}
add_action( 'admin_menu', 'mk_post_thumbnails_management_menu' );

/**
 * Entête de la page d'administration 
 */
function mk_post_thumbnails_render_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2><?php _e( 'Post Thumbnails',  'milk-file-browser' ); ?></h2>
        <?php settings_errors(); ?>
  		<?php mk_post_thumbnails_tools_form_render();?>
	</div>	
<?php
}
 
/**
 * 
 */
function mk_post_thumbnails_tools_form_render(){
	regenerate_interface();
}
