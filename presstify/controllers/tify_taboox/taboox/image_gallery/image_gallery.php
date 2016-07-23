<?php
/* = Déclaration = */
add_action( 'tify_taboox_register_form', 'tify_taboox_image_gallery_init' );
function tify_taboox_image_gallery_init(){
	tify_taboox_register_form( 'tiFy_Taboox_ImageGallery' );
}

/* = Formulaire de saisie = */
class tiFy_Taboox_ImageGallery extends tiFy_Taboox{
	public 	$data_name = 'tify_post_meta[single][tify_taboox_image_gallery]',
			$data_key 	= '_tify_taboox_image_gallery';
		
	public function current_screen( $screen ){
		wp_enqueue_media();
		wp_enqueue_script( 'tify_taboox_image_gallery', $this->uri .'/admin.js', array( 'jquery' ), '150325', true );
		wp_localize_script( 'tify_taboox_image_gallery', 'tify_taboox_image_gallery', array(
				'maxAttempt' => __( 'Nombre maximum d\'images dans la galerie atteint', 'tify' ),
			)
		);		
	}
	
	public function form( $post ){
		static $taboox_image_gallery_order;
		
		$args = wp_parse_args( 
					$this->args, 
					array(
						'max'	=> -1
					)
				);
		extract( $args );
	?>
	<div id="taboox_image_gallery-<?php echo $this->inst;?>" class="images_gallery-postbox">
		<input type="hidden" name="<?php echo $this->data_name;?>[]" value="0" />
		<ul id="taboox_image_gallery-list-<?php echo $this->inst;?>" class="taboox_image_gallery-list thumb-list">			
		<?php foreach( (array) $this->data_value as $attachment_id ) $this->item_render( $attachment_id, $this->data_name, $taboox_image_gallery_order++ ); ?>	
		</ul>
		<a href="#" class="taboox_image_gallery-add button-secondary" 
			data-name="<?php echo $this->data_name;?>" 
			data-max="<?php echo $max;?>"
			data-media_title="<?php _e( 'Galerie d\'images', 'tify' );?>"			
			data-media_button_text="<?php _e( 'Ajouter les images', 'tify' );?>"
		>
			<div class="dashicons dashicons-format-gallery" style="vertical-align:middle;"></div>&nbsp;
			<?php _e( 'Ajouter des images', 'tify' );?>
		</a>
	</div>					
	<?php
	}

	public function item_render( $attachment_id, $name, $order ){
		// Bypass
		if( ! $image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ) )
			return;		
		
		$output  = "";
		$output .= "\n<li>";
		$output .= "\n\t<img src=\"{$image[0]}\" class=\"attachment-thumbnail\" />";
		$output .= "\n\t<input type=\"hidden\" name=\"{$name}[]\" value=\"{$attachment_id}\" />";
		$output .= "\n\t<a href=\"#remove\" class=\"tify_button_remove\"></a>";	
		$output .= "\n\t<input type=\"text\" class=\"order\" value=\"{$order}\" size=\"1\" readonly />";	
		$output .= "\n</li>";
		
		echo $output;
	}
}

/**
 * Récupération des images de la galerie
 */
function mkpbx_get_images_gallery( $post_id = null, $args = array() ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	$args = wp_parse_args( $args, array(
			'name' => 'tify_taboox_image_gallery'
		)
	);
	extract( $args );
	
	if( $gallery_images = get_post_meta( $post_id, '_'.$name, true ) ) :
		foreach( $gallery_images as $k => $gallery_image ) 
			if( ! $gallery_image ) $gallery_images = array_slice( $gallery_images, 1 );
	endif;
			
	return $gallery_images;
}

/**
 * Vérification d'image dans la galerie
 */
function has_images_gallery( $post_id = null, $args = array() ){
	if( mkpbx_get_images_gallery( $post_id, $args ) )
		return true;
}

/**
 * Affichage de la galerie d'images
 */
function the_images_gallery( $post_id = null, $args = array() ){
	echo get_the_images_gallery( $post_id, $args );
}

/**
 * Récupération de la galerie d'images
 */
function get_the_images_gallery( $post_id = null, $args = array() ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	$args = wp_parse_args( $args, array(
			'name' => 'tify_taboox_image_gallery'
		)
	);
	extract( $args );
	// Bypass
	if( ! has_images_gallery( $post_id, $args ) )
		return;
	
	static $instances = 0;
	$instances++;
	
	if( ! $images = mkpbx_get_images_gallery( $post_id, $args ) )
		return;

	$output  = "";
	$output .= "\n<div id=\"mkpbx_images_gallery-$post_id-$instances\" class=\"mkpbx_images_gallery\">";
	$output .= "\n\t<div class=\"viewer\">";
	$output .= "\n\t\t<ul class=\"roller\">";
	foreach( $images as $img_id )
		if( ! empty( $img_id ) && ( $src = wp_get_attachment_image_src( $img_id, 'full' ) ) )
			$output .= "\n\t\t\t<li><div class=\"item-image\" style=\"background-image:url({$src[0]});\"></div></li>";
	$output .= "\n\t\t</ul>";
	$output .= "\n\t</div>";
	$output .= "\n\t<a href=\"#mkpbx_images_gallery-$post_id-$instances\" class=\"nav prev\"></a>";
	$output .= "\n\t<a href=\"#mkpbx_images_gallery-$post_id-$instances\" class=\"nav next\"></a>";
	$output .= "\n\t\t<ul class=\"tabs\">";
	$n = 1;
	reset( $images );
	foreach( $images as $img_id )
		if( ! empty( $img_id ) && ( $src = wp_get_attachment_image_src( $img_id, 'full' ) ) )
			$output .= "\n\t\t<li><a href=\"#mkpbx_images_gallery-$post_id-$instances\">".( $n++ )."</a></li>";	
	$output .= "\n\t\t</ul>"; 
	$output .= "\n</div>";	

	return apply_filters( 'mkpbx_images_gallery_display', $output, $images, $instances, $post_id );
}