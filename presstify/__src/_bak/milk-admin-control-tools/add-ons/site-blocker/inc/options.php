<?php
/**
 * 
 */ 
function mkbkr_add_management_menu() {
	add_management_page(
		__('Site Blocker Options', 'milk-site-blocker' ),
		__('Site Blocker', 'milk-site-blocker' ),
		apply_filters( 'mkbkr_capability', 'manage_options' ),
		'milk_site_blocker_tools',
		'mkbkr_management_render_page'
	);
}
add_action( 'admin_menu', 'mkbkr_add_management_menu' );

/**
 * Entête de la page de rendu des options
 */
function mkbkr_management_render_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
        <h2><?php _e( 'Site Blocker',  'milk-site-blocker' ); ?></h2>
        <?php settings_errors(); ?>
  		<?php mkbkr_management_form_render();?>
	</div>	
<?php
}

/**
 * 
 */
function mkbkr_management_form_render() {
?>
	<form method="post" action="options.php">
		<?php settings_fields( 'mkbkr_options' );?> 
		<?php do_settings_sections( 'mkbkr_options' );?>
		<?php submit_button();?>
	</form>
<?php
}

/**
 * 
 */
function mkbr_defaults_options( $args = array() ){
	$defaults = array(
		'mkbkr_options_active' => 'off',
		'mkbkr_options_title' => __( 'This site is temporaly inactive', 'milk-site-blocker' ),
		'mkbkr_options_content' => __( 'Sorry, but you can try again to visit it later.', 'milk-site-blocker' ),
	
		'mkbkr_options_active' => 'default',
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	return apply_filters( 'mkbr_defaults_options', $args );
} 

/**
 * 
 */
function mkbr_get_default_option( $option_name = '' ){
	if( ! $option_name )
		return;
	
	$options = mkbr_defaults_options();
	
	if( isset( $options[$option_name] ) )
		return $options[$option_name];	
}

/**
 * 
 */
function mkbr_is_active(){
	return get_option( 'mkbkr_options_active', mkbr_defaults_options('mkbkr_options_active') ) == 'on';
}

/**
 * 
 */
function mkbkr_message_options_init() {
	register_setting(
		'mkbkr_options',
		'mkbkr_options_active'		
	);	
	register_setting(
		'mkbkr_options',
		'mkbkr_options_title'		
	);
	register_setting(
		'mkbkr_options',
		'mkbkr_options_content'				
	);

	// Déclaration des champs d'options
	add_settings_section( 
		'default', 
		'', // Titre de section si necessaire
		'__return_false', // Callback
		'mkbkr_options'
	);
	
	add_settings_field( 'active', __( 'Active', 'milk-site-blocker' ), 'mkbkr_options_active_render', 'mkbkr_options' );
	add_settings_field( 'title', __( 'Title', 'milk-site-blocker' ), 'mkbkr_options_title_render', 'mkbkr_options' );
	add_settings_field( 'content', __( 'Content', 'milk-site-blocker' ), 'mkbkr_options_content_render', 'mkbkr_options' );
}
add_action( 'admin_init', 'mkbkr_message_options_init' );

/**
 * 
 */
function mkbkr_options_active_render(){
	$value = get_option( 'mkbkr_options_active', mkbr_get_default_option( 'mkbkr_options_active' ) );	
?>
	<input type="checkbox" name="mkbkr_options_active" <?php checked( $value ==='on' );?>" />
<?php
}

/**
 * 
 */
function mkbkr_options_title_render(){
	$value = get_option( 'mkbkr_options_title', mkbr_get_default_option( 'mkbkr_options_title' ) );	
?>
	<input type="text" name="mkbkr_options_title" value="<?php echo $value;?>" size="80" style="font-size: 1.7em; line-height: 100%; outline: medium none; padding: 3px 8px; width: 100%;"/>
<?php
}

/**
 * 
 */
function mkbkr_options_content_render(){
	global $wp_version;
		
	$value = get_option( 'mkbkr_options_content', mkbr_get_default_option( 'mkbkr_options_content' ) );	
		
	$editor_opts = array(
		'media_buttons' => false,
		'teeny' => true, 
	);
	
	if( version_compare($wp_version, '3.3', '<') ) :
		the_editor($value, 'mkbkr_options_content', 'mkbkr_options_content', false, 2, false );
	else :
		wp_editor($value, 'mkbkr_options_content', $editor_opts );
	endif;	
} 
