<?php
/**
 * Metaboxe de saisie
 */ 
function mkpbx_custom_background_render( $post, $args = array()){
	// Bypass	
	if( ! $post = get_post( $post) )
		return;
	
	$args = wp_parse_args( $args, array(
			'name' => 'mkpbx_postbox'
		)
	);
	extract( $args );
	$custom_bkg = get_post_meta($post->ID, '_custom_background', true );
?>	
	<div class="custom_background-postbox">
		<div id="mkpbx_custom_background" style="background-color:#E4E4E4; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover; background-repeat:no-repeat !important; background-position:center !important;width:100%; height:500px; position:relative; background-image:url(<?php echo $custom_bkg? $custom_bkg : get_background_image();?>)">
			<a href="#add-custom-background" data-name="<?php echo $name;?>[single][custom_background]" id="add-custom-background" style="position:absolute; width:150px; top:50%; left:50%; margin-left:-75px; margin-top:-15px; z-index:99;" class="button-secondary">Changer l'arrière plan</a>
			<input type="hidden" name="<?php echo $name;?>[single][custom_background]" value="<?php echo $custom_bkg ? esc_url( $custom_bkg ): esc_url( get_background_image() );?>" />
			<input type="hidden" id="original_background" value="<?php echo esc_url( get_background_image() );?>" />
			<a href="#mkpbx_custom_background" data-name="<?php echo $name;?>[single][custom_background]" title="<?php _e('Rétablir l\'original', 'mktzr');?>" class="dashicons dashicons-no reset-cbkg" style="color:#AA0000; display:block; width:32px; height:32px; position:absolute; right:10px; top:10px; font-size:32px; display:<?php echo $custom_bkg?'inherit':'none';?>"></a>
		</div>
		<h4><i class="dashicons dashicons-info"></i> Conseil d'utilisation de la personnalisation d'image de fond</h4>
		<ul>
			<li>Personnaliser l'image de fond augmente le chargement de votre page de manière conséquente, utilisez donc cette fonctionnalité avec parcimonie.</li>
			<li>Utilisez des images d'un résolution de 1920x1080, pour répondre au spécifications de la plupart des résolutions d'écran.</li>
			<li>De préférence optimiser vos images de fond avec le service en ligne <a href="http://www.smushit.com/ysmush.it/" trage="_blank">Smush.it.</a></li>
		</ul>	
	</div>				
<?php	
}

/**
 * 
 */
function mkpbx_custom_background_replace( $default ){
	if( is_singular()&& post_custom( '_custom_background' ) )
		add_filter( "theme_mod_background_image", create_function( '', 'return post_custom( "_custom_background" );') );
}
add_action('wp_enqueue_scripts', 'mkpbx_custom_background_replace' );
