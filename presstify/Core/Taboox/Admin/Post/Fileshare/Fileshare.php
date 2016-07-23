<?php
namespace tiFy\Core\Taboox\Admin\Post\Fileshare;

use tiFy\Core\Taboox\Admin;

/* = Formulaire de saisie = */
class Fileshare extends Admin
{
	/* = CHARGEMENT DE LA PAGE = */	
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = wp_parse_args( 
			$this->args, 
			array(
				'name' 		=> '_tify_taboox_fileshare',
				'filetype' 	=> '', // video || application/pdf || video/flv, video/mp4,
				'max' 		=> -1
			)
		);
		
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, $this->args['name'] );
		register_post_meta( $current_screen->id, '_taboox_fileshare_names' );			
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'tify_taboox_fileshare', $this->Url .'/admin.css', array( ), '151216' );
		wp_enqueue_media();
		wp_enqueue_script( 'tify_taboox_fileshare', $this->Url .'/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '151216', true );		
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{
		extract( $this->args, EXTR_SKIP );		
		$metadatas = \tify_get_post_meta( $post->ID, $name );			
	?>
		<div id="fileshare-postbox">
			<input type="hidden" name="tify_post_meta[single][_taboox_fileshare_names][]" value="<?php echo esc_attr( $name );?>" />
			<ul id="fileshare-<?php echo sanitize_title($name);?>-list" class="fileshare-list">
			<?php foreach( (array) $metadatas as $meta_id => $meta_value ) : ?>
				<li>
					<span class="icon"><?php echo wp_get_attachment_image( $meta_value, array( 46, 60 ), true );?></span>
					<span class="title"><?php echo get_the_title( $meta_value );?></span>
					<span class="mime"><?php echo get_post_mime_type( $meta_value );?></span>							
					<a href="#" class="remove tify_button_remove"></a>
					<input type="hidden" name="tify_post_meta[multi][<?php echo $name;?>][<?php echo $meta_id;?>]" value="<?php echo esc_attr( $meta_value );?>" />					
				</li>
			<?php endforeach;?>		
			</ul>
			<a href="#" class="add-fileshare button-secondary" 
				<?php if( $filetype ) echo "data-type=\"{$filetype}\"";?> 
				data-item_name="<?php echo $name;?>" 
				data-target="#fileshare-<?php echo sanitize_title($name);?>-list"
				data-max="<?php echo $max;?>"
				data-uploader_title="<?php _e( 'Sélectionner les fichiers à associer', 'tify' );?>"
			>
				<div class="dashicons dashicons-media-text" style="vertical-align:middle;"></div>&nbsp;
				<?php echo _n( __(  'Ajouter le fichier', 'tify' ), __(  'Ajouter des fichiers', 'tify' ), ( ( $max === 1 ) ? 1 : 2 ), 'tify'  );?>
			</a>
		</div>			
	<?php	
	}
}