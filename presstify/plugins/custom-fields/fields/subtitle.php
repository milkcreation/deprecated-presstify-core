<?php
/**
 *
 */
add_action( 'current_screen', 'mkpbx_subtitle_current_screen', 10, 1 );
function mkpbx_subtitle_current_screen( $current_screen )
{
	register_post_meta( $current_screen->id, '_subtitle' );
}
/**
 * 
 */
function mkpbx_subtitle_post_type( $post = null ){
	$post_type = apply_filters( 'mkpbx_subtitle_post_type', array( 'post', 'page' ) );

	// Bypass	
	if( ! $post = get_post( $post) )
		return;

	if( in_array( $post->post_type, $post_type ) )
		add_action( 'edit_form_after_title', 'mkpbx_subtitle_edit_form_after_title' );	
}
add_action( 'dbx_post_advanced', 'mkpbx_subtitle_post_type' );

/**
 * Edition du sous titre
 */
function mkpbx_subtitle_edit_form_after_title( $post = null, $args = array() ){
		
	// Bypass	
	if( ! $post = get_post( $post) )
		return;

	
	$args = wp_parse_args( $args, array(
			'name' => 'tify_post_meta[single][_subtitle]'
		)
	);
?>
	<input type="text" class="widefat"  name="<?php echo $args['name']?>" value="<?php echo get_post_meta( $post->ID, '_subtitle', true );?>" placeholder="<?php _e( 'Sous-titre (optionnel)', 'corfu' );?>" style="margin-bottom:20px;" />
<?php	
}

/**
 *  Récupération du sous-titre
 */
function get_the_subtitle( $post = null ){
	if( ! $post )
		global $post;
	// Bypass	
	if( ! $post = get_post( $post) )
		return;

	$subtitle = get_post_meta( $post->ID, '_subtitle', true ) ? get_post_meta( $post->ID, '_subtitle', true ) : '';
	$id = isset( $post->ID ) ? $post->ID : 0;

	if ( ! is_admin() ) {
		if ( ! empty( $post->post_password ) ) {
			$protected_title_format = apply_filters( 'protected_subtitle_format', __( 'Protected: %s' ) );
			$subtitle = sprintf( $protected_title_format, $subtitle );
		} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
			$private_title_format = apply_filters( 'private_subtitle_format', __( 'Private: %s' ) );
			$subtitle = sprintf( $private_title_format, $subtitle );
		}
	}

	return apply_filters( 'the_subtitle', $subtitle, $id );
}

/**
 *  Affichage du sous-titre
 */
function the_subtitle( $before = '', $after = '', $echo = true ){
	$subtitle = get_the_subtitle();

	if ( strlen($subtitle) == 0 )
		return;

	$subtitle = $before . $subtitle . $after;

	if ( $echo )
		echo $subtitle;
	else
		return $subtitle;
}