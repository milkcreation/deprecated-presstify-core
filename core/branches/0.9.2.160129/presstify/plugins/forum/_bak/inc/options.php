<?php
/**
 * -------------------------------------------------------------------------------
 *	Admin Page Options
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * 
 */ 
function mkforums_contribs_default_params( $subsection = ''){
	$params = array(
		'global_params' => array( 
			'require_name_email' =>0, 
			'contribs_registration' =>1, 
			'thread_contribs' => 0, 
			'thread_contribs_depth' => 1, 
			'page_contribs' => 1, 
			'contribs_per_page' => 20, 
			'default_contribs_page' => 'newest',
			'contribs_order' => 'desc'  
		), 
		'email_params' => array( 
			'contribs_notify' => 0, 
			'moderation_notify' => 0 
		),
		'moderation_params' => array( 
			'contribs_moderation'=> 0,
			'contribs_whitelist' => 0 
		)
	);
	if( $subsection && isset($params[$subsection] ) )
		return $params[$subsection];
	else
		return $params;
} 	

/**
 * 
 */ 
function mkforums_contribs_none_params( $subsection = ''){
	$params = array(
		'global_params' => array( 
			'require_name_email' =>0, 
			'contribs_registration' =>0, 
			'thread_contribs' => 0, 
			'thread_contribs_depth' => 0, 
			'page_contribs' => 0, 
			'contribs_per_page' => 20, 
			'default_contribs_page' => 'newest',
			'contribs_order' => 'desc'  
		), 
		'email_params' => array( 
			'contribs_notify' => 0, 
			'moderation_notify' => 0 
		),
		'moderation_params' => array( 
			'contribs_moderation'=> 0,
			'contribs_whitelist' => 0 
		)
	);
	if( $subsection && isset($params[$subsection] ) )
		return $params[$subsection];
	else
		return $params;
} 

/**
 * 
 */
function mkforums_contribs_get_params( $subsection = '' ){
	if( $subsection )
		return $params = mkforums_contribs_parse_params( get_option( 'mkforums_contribs_'.$subsection, mkforums_contribs_default_params($subsection) ), $subsection );
	//@todo else 

} 

/**
 * 
 */
function mkforums_contribs_parse_params( $params = array(), $subsection = '' ){
	$defaults = mkforums_contribs_none_params( $subsection );
	return wp_parse_args( $params, $defaults );
} 
 
  
/**
 * Création de la page relative à la gestion des options (entrées de menu + aide contextuelle)
 */
function mkforums_options_add_page() {
	add_options_page(
		__( 'Forums', 'milk-contact-form' ),
		__('Forums', 'milk-contact-form' ),
		'manage_options',
		'mkforums_options',
		'mkforums_options_render_page'
	);
}
add_action( 'admin_menu', 'mkforums_options_add_page' );

/**
 * Scripts de la page des options
 */
function mkforums_options_scripts( $hookname ){
	if( !preg_match( '/mkforums_options/', $_SERVER['REQUEST_URI'] ) )
		return;
	wp_enqueue_script( 'mkpack-options-tabs', MKFORUMS_URL.'/js/options-tabs.js', array( 'jquery' ), 121010, true );
}
add_action( 'admin_enqueue_scripts',  'mkforums_options_scripts' );

/**
 * Page de rendu de l'édition des options.
 */
function mkforums_options_render_page() {
?>
	<div id="mkforums_options_render_page" class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Forums', 'milk-contact-form' ); ?></h2>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php mkpack_do_settings_sections( 'mkforums_options' ); ?>			
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}

/**
 * Rendu des options générales de contributions
 */
function mkforums_general_options_init(){
	register_setting( 'mkforums_options', 'mkforums_page_for_forums' );		
	
	add_settings_section( 
		'page-for-forums', 
		__('General', 'milk-forums'),
		'__return_false',
		'mkforums_options'
	);
	
	add_settings_field( 'mkforums-page-for-forums', __( 'Page for forums archives', 'milk-forums' ), 'mkforums_page_for_forums_render', 'mkforums_options', 'page-for-forums' );
}
add_action( 'admin_init', 'mkforums_general_options_init' );

/**
 * 
 */
function mkforums_page_for_forums_render() {
	$page_for_forums = get_option( 'mkforums_page_for_forums', 0 );	
	wp_dropdown_pages( array(
		'selected' => $page_for_forums,
		'name' => 'mkforums_page_for_forums',
		'show_option_none' => __('No Page', 'milk-forums'), 
		'option_none_value' => '' 
		) 
	);
}

/**
 * 
 */
function mkforums_contrib_options_init(){
	add_settings_section( 
		'contrib-params', 
		__('Contribs', 'milk-forums'),
		'__return_false',
		'mkforums_options'
	);
	
	register_setting( 'mkforums_options', 'mkforums_contribs_global_params' );
	add_settings_field( 'mkforums-contribs-global-params', __( 'Global params', 'milk-forums' ), 'mkforums_contribs_global_params_render', 'mkforums_options', 'contrib-params' );

	register_setting( 'mkforums_options', 'mkforums_contribs_email_params' );
	add_settings_field( 'mkforums-contribs-email-params', __( 'Email params', 'milk-forums' ), 'mkforums_contribs_email_params_render', 'mkforums_options', 'contrib-params' );
	
	register_setting( 'mkforums_options', 'mkforums_contribs_moderation_params' );
	add_settings_field( 'mkforums-contribs-moderation-params', __( 'Moderation params', 'milk-forums' ), 'mkforums_contribs_moderation_params_render', 'mkforums_options', 'contrib-params' );
}
add_action( 'admin_init', 'mkforums_contrib_options_init' );

/**
 * Rendu des options générales de contributions
 */
function mkforums_contribs_global_params_render() {
	$params = mkforums_contribs_get_params( 'global_params' );
?>	
<?php //Renseignmenet du nom et de l'email ?>
	<input type="checkbox" name="mkforums_contribs_global_params[require_name_email]" id="require_name_email" value="1" <?php checked('1', $params['require_name_email'] ); ?> /><?php _e('Comment author must fill out name and e-mail');?>
	<br />
<?php //Utilisateur en mode connecté ? ?>	
	<input name="mkforums_contribs_global_params[contribs_registration]" type="checkbox" id="contribs_registration" value="1" <?php checked('1', $params['contribs_registration'] ); ?> /><?php _e('Users must be registered and logged in to comment') ?>
	<br />	
<?php //Fil de Discussion ?>
	<input name="mkforums_contribs_global_params[thread_contribs]" type="checkbox" id="thread_contribs" value="1" <?php checked('1', $params['thread_contribs'] ); ?> />
	<?php	
	$maxdeep = (int) apply_filters( 'mkforums_thread_contribs_depth_max', 5 );
	
	$thread_contribs_depth = '</label><select name="mkforums_contribs_global_params[thread_contribs_depth]" id="thread_contribs_depth">';
	for ( $i = 2; $i <= $maxdeep; $i++ ) {
		$thread_contribs_depth .= "<option value='" . esc_attr($i) . "'";
		if ( $params['thread_contribs_depth'] == $i ) $thread_contribs_depth .= " selected='selected'";
		$thread_contribs_depth .= ">$i</option>";
	}
	$thread_contribs_depth .= '</select>';
	printf( __('Enable threaded (nested) comments %s levels deep'), $thread_contribs_depth );
	?><br />
<?php //Pagination ?>	
	<input name="mkforums_contribs_global_params[page_contribs]" type="checkbox" id="page_contribs" value="1" <?php checked('1', $params['page_contribs'] ); ?> />
	<?php
	$default_contribs_page = '</label><label for="default_contribs_page"><select name="mkforums_contribs_global_params[default_contribs_page]" id="default_contribs_page"><option value="newest"';
	if ( 'newest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
	$default_contribs_page .= '>' . __('last') . '</option><option value="oldest"';
	if ( 'oldest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
	$default_contribs_page .= '>' . __('first') . '</option></select>';
	printf( __('Break comments into pages with %1$s top level comments per page and the %2$s page displayed by default'), '</label><label for="contribs_per_page"><input name="mkforums_contribs_global_params[contribs_per_page]" type="text" id="contribs_per_page" value="' . esc_attr( $params['contribs_per_page'] ) . '" class="small-text" />', $default_contribs_page );
	
	?></label>
	<br />
<?php //Order?>	
	<?php
	$contribs_order = '<select name="mkforums_contribs_global_params[contribs_order]" id="contribs_order"><option value="asc"';
	if ( 'asc' == $params['contribs_order'] ) $contribs_order.= ' selected="selected"';
	$contribs_order .= '>' . __('older') . '</option><option value="desc"';
	if ( 'desc' == $params['contribs_order'] ) $contribs_order .= ' selected="selected"';
	$contribs_order .= '>' . __('newer') . '</option></select>';
	printf( __('Comments should be displayed with the %s comments at the top of each page'), $contribs_order );
?>
<?php	
}

/**
 * Rendu des options d'emaling des contributions
 */
function mkforums_contribs_email_params_render() {		
	$params = mkforums_contribs_get_params( 'email_params' );
?>	
	<input name="mkforums_contribs_email_params[contribs_notify]" type="checkbox" id="contribs_notify" value="1" <?php checked( '1', $params['contribs_notify'] ); ?> /><?php _e('Anyone posts a comment') ?> 
	<br />
	<input name="mkforums_contribs_email_params[moderation_notify]" type="checkbox" id="moderation_notify" value="1" <?php checked('1', $params['moderation_notify'] ); ?> /><?php _e('A comment is held for moderation') ?>
	<br />
<?php	
}

/**
 * Rendu des options de modération des contributions
 */
function mkforums_contribs_moderation_params_render() {
	$params = mkforums_contribs_get_params( 'moderation_params' );
?>	
	<input name="mkforums_contribs_moderation_params[contribs_moderation]" type="checkbox" id="contribs_moderation" value="1" <?php checked('1', $params['contribs_moderation']); ?> /><?php _e('An administrator must always approve the comment') ?>
	<br />
	<input type="checkbox" name="mkforums_contribs_moderation_params[contribs_whitelist]" id="contribs_whitelist" value="1" <?php checked('1', $params['contribs_whitelist'] ); ?> /> <?php _e('Comment author must have a previously approved comment') ?>
	<br />
<?php	
}