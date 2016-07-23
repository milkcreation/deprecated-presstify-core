<?php 
/**
 * Metaboxe de saisie
 */ 
function mkpbx_password_protect_render( $post, $args = array() ){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'mkpbx_postbox'
		)
	);
	extract($args );
	
	$password = get_post_meta( $post->ID, '_password_protect', true );
?>
	<style>
		::-ms-reveal {
			display:none !important;
		}
		.hideShowPassword-toggle {
			position:relative;		
			cursor: pointer;
			height:40px;
			overflow: hidden;
			text-indent: -9999em;
			width:40px;
		}
		.hideShowPassword-toggle:before{
			position:absolute;
			left:9px; right:0; top:8px; bottom:0;
			content: "\f070";
			text-indent: 0em;
			color:#999;
			font: 400 25px/1 'FontAwesome' !important;
		}
		.hideShowPassword-toggle-hide:before {
			content: "\f06e";
		}
	</style>
	<div id="password_protect-postbox">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label>
							<?php _e('Protection d\'accès', 'mktzr_postbox' );?>							
						</label>
					</th>
					<td>
						<input class="login-field login-field-password" name="<?php echo $name.'[single][password_protect]';?>" id="password_protect" type="password" placeholder="<?php _e('Mot de passe', 'mktzr');?>" value="<?php echo $password;?>" autocomplete="off" />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
}

/**
 * Vérification du cookie d'authentification au contenu
 */
function check_password_protect(){
	if( isset( $_COOKIE['mkpbx_password_protect-'.get_the_ID()] ) && wp_check_password( get_post_meta( get_the_ID(), '_password_protect', true ),  $_COOKIE['mkpbx_password_protect-'.get_the_ID()] ) )
		return true;
}

/**
 * Formulaire d'accès
 */
function mkpbx_password_protect_form(){
	global $password_protect_error;
	
	$output  = "";
	$output .= "\n<div class=\"entry-content\" style=\"padding-bottom:25px;\">";
	$output .= "\n<h3>".__( 'Il s\'agit d\'un espace réservé aux professionnels, veuillez vous identifier afin de pouvoir y accèder' )."</h3>";
	if( is_wp_error( $password_protect_error ) )
		$output .= "<div class=\"error\">".$password_protect_error->get_error_message()."</div>";
	$output .= "\n\t<form method=\"post\" action=\"\">";
	$output .= wp_referer_field( false );
	$output .= "\n\t\t<input type=\"password\" autocomplete=\"off\" name=\"mkpbx_password_protect-".get_the_ID()."\" />";
	$output .= "\n\t\t<input type=\"hidden\" name=\"post_id\" value=\"".get_the_ID()."\" />";
	$output .= "\n\t\t<input type=\"submit\" class=\"button-primary\" />";
	$output .= "\n\t</form>";
	$output .= "\n</div>";
	
	echo $output;	
}

/**
 * Vérification d'accès à la page
 */
function mkpbx_password_protect_validation(){
	global $password_protect_error, $post;
	
	//bypass
	if( is_admin() )
		return;
	if( empty( $_REQUEST['post_id'] ) ) 
		return;
	if( !isset( $_REQUEST['post_id'] ) && !isset( $_REQUEST['mkpbx_password_protect'] ) ) 
		return;
	if( !$post = get_post( $_REQUEST['post_id'] ) )
		return;

	if( empty( $_REQUEST['mkpbx_password_protect-'.$post->ID] ) )
		return $password_protect_error = new WP_Error( 'password_protect_error', __( 'Le mot de passe ne peut être vide', 'mkpbx' ) );
	if( $_REQUEST['mkpbx_password_protect-'.$post->ID ] !=  get_post_meta( $post->ID, '_password_protect', true ) ) :
		return $password_protect_error =  new WP_Error( 'password_protect_error', __( 'Le mot de passe saisie ne correspond pas', 'mkpbx' ) );
	else:
		setcookie( 'mkpbx_password_protect-'.$post->ID, wp_hash_password( $_REQUEST['mkpbx_password_protect-'.$post->ID] ) );
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) : 
			wp_safe_redirect( $_REQUEST['_wp_http_referer'] ); exit; 
		endif;
	endif;
}
add_action( 'wp_loaded', 'mkpbx_password_protect_validation', 1 );