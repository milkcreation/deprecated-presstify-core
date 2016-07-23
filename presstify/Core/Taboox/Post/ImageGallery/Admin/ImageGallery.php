<?php
namespace tiFy\Core\Taboox\Post\ImageGallery\Admin;

use tiFy\Core\Taboox\Admin;

class ImageGallery extends Admin
{	
	/* = CHARGEMENT DE LA PAGE = */	
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args( 
			$this->args, 
			array(
				'name'	=> '_tify_taboox_image_gallery',
				'max'	=> -1
			)
		);
						
		// Déclaration des metadonnées à enregistrer
		tify_meta_post_register( $current_screen->id, $this->args['name'], true );			
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{		
		wp_enqueue_media();
		wp_enqueue_style( 'tify_taboox_image_gallery-admin', $this->Url .'/admin.css', array( ), '150325' );
		wp_enqueue_script( 'tify_taboox_image_gallery-admin', $this->Url .'/admin.js', array( 'jquery' ), '150325', true );
		wp_localize_script( 'tify_taboox_image_gallery', 'tify_taboox_image_gallery', array(
				'maxAttempt' => __( 'Nombre maximum d\'images dans la galerie atteint', 'tify' ),
			)
		);
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{
		static $taboox_image_gallery_order = 0;		
		extract( $this->args, EXTR_SKIP );
	?>
	<div class="taboox_image_gallery">
		<ul id="taboox_image_gallery-list" class="taboox_image_gallery-list thumb-list">			
		<?php foreach( (array) get_post_meta( $post->ID, $name, true ) as $attachment_id ) $this->item_render( $attachment_id, $name, $taboox_image_gallery_order++ ); ?>	
		</ul>
		<a href="#" class="taboox_image_gallery-add button-secondary" 
			data-name="<?php echo $name;?>" 
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
	
	/* = AFFICHAGE D'UN ÉLÉMENT = */
	public function item_render( $attachment_id, $name, $order ){
		// Bypass
		if( ! $image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ) )
			return;		
		
		$output  = "";
		$output .= "\n<li>";
		$output .= "\n\t<img src=\"{$image[0]}\" class=\"attachment-thumbnail\" />";
		$output .= "\n\t<input type=\"hidden\" name=\"tify_meta_post[{$name}][]\" value=\"{$attachment_id}\" />";
		$output .= "\n\t<a href=\"#remove\" class=\"tify_button_remove\"></a>";	
		$output .= "\n\t<input type=\"text\" class=\"order\" value=\"{$order}\" size=\"1\" readonly />";	
		$output .= "\n</li>";
		
		echo $output;
	}
}