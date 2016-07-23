<?php
/**
 * --------------------------------------------------------------------------------
 *	Tools
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation File Browser
 * @package    	Worpdress Extends Milkcreation Suite
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/file-browser
 * @author 		Jordy Manner
 * @version 	1.1
 */ 

/**
 * Menu d'administration
 */ 
function mk_file_browser_management_menu() {
	add_management_page(
		__( 'File Browser', 'milk-file-browser' ),
		__( 'File Browser', 'milk-file-browser' ),
		apply_filters( 'mk_file_browser_capability', 'manage_options' ),
		'mk_file_browser_tools',
		'mk_file_browser_render_page'
	);
}
add_action( 'admin_menu', 'mk_file_browser_management_menu' );

/**
 * Entête de la page d'administration 
 */
function mk_file_browser_render_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2><?php _e( 'File Browser',  'milk-file-browser' ); ?></h2>
        <?php settings_errors(); ?>
  		<?php mk_file_browser_elfinder_init();?>
	</div>	
<?php
}

/**
 * Appel de l'explorateur de fichiers
 */
function mk_file_browser_elfinder_init(){
?><div id="elfinder"></div>
<a href="<?php echo MKBROWSER_URL.'/elfinder/';?>"><?php _e( 'Browser without Wordpress interface', 'milk-file-browser' );?></a><?php
}

/**
 * Mise en queue des scripts du back-office dans l'entête
 */
function mk_file_browser_admin_header( $hookname ){
	if( $hookname != 'tools_page_mkact_tools' )
		return;
	if( ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] != 'browser' ) 
		return;
	
	$url =  WP_PLUGIN_URL.'/'.plugin_basename( dirname(dirname(__FILE__) ) );
	add_action( 'admin_footer', 'mk_file_browser_admin_footer', 99 );
	
	// Thème jQuery UI
	wp_enqueue_style('jquery-ui-smoothness', $url.'/css/smoothness/jquery-ui-1.8.22.custom.css', false, '1.8.22');
	
	// Thème Elfinder
	wp_enqueue_style( 'elfinder', $url.'/elfinder/css/elfinder.min.css' );
	wp_enqueue_style( 'elfinder-theme', $url.'/elfinder/css/theme.css' );
	
	// Plugins jQuery Ui nécessaire
	wp_enqueue_script( 'jquery-ui-selectable' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	
	// Plugin jQuery Elfinder
	wp_enqueue_script('elfinder', $url.'/elfinder/js/elfinder.min.js', array( 'jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), '', true );	
	
	// Translation de Elfinder
	$locale = preg_split( '/_/', get_locale() );
	wp_enqueue_script('elfinder-l10n', $url.'/elfinder/js/i18n/elfinder.'.$locale[0].'.js', array( 'jquery', 'jquery-ui-selectable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), '', true );		
}
add_action('admin_enqueue_scripts', 'mk_file_browser_admin_header' );

/**
 * Script d'appel de Elfinder dans le pied de page
 */
function mk_file_browser_admin_footer(){
?><script type="text/javascript">/* <![CDATA[ */
jQuery(document).ready( function($) {			
	var elf = $('#elfinder').elfinder({
		url : '<?php echo add_query_arg('elfinder_connector', true, admin_url() )?>',  // connector URL (REQUIRED)
		lang: 'fr',
	}).elfinder('instance');
});
/* ]]> */</script><?php
}

/**
 *  Appel du connecteur Php de Elfinder
 */
function mk_file_browser_elfinder_connector_for_wp( ) {
	if ( ! empty( $_REQUEST['elfinder_connector'] ) ) :
		include dirname( dirname(__FILE__) ).'/elfinder/php/wp.connector.php';	
		exit;
	endif;
}
add_action( 'admin_init', 'mk_file_browser_elfinder_connector_for_wp' );