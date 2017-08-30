<?php
namespace tiFy\Core\Taboox\Post\VideoGallery\Admin;

class VideoGallery extends \tiFy\Core\Taboox\Admin
{	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		
		
		// Actions et Filtres Wordpress
		add_action( 'wp_ajax_taboox_video_gallery_add_item', array( $this, 'wp_ajax' ) );
	} 
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args( 
			$this->args, 
			array(
				'name'	=> '_tify_taboox_video_gallery',
				'max'	=> -1
			)
		);
				
		// Déclaration des metadonnées à enregistrer
		tify_meta_post_register( $current_screen->id, $this->args['name'], false );							
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_media();
		wp_enqueue_style( 'tiFyTabooxVideoGallery', self::tFyAppUrl() .'/VideoGallery.css', array( ), '150325' );	
		wp_enqueue_script( 'tiFyTabooxVideoGallery',self::tFyAppUrl() .'/VideoGallery.js', array( 'jquery', 'jquery-ui-sortable' ), '150325', true );
		wp_localize_script( 'tiFyTabooxVideoGallery', 'tify_taboox_video_gallery', array(
				'maxAttempt' => __( 'Nombre maximum de vidéos dans la galerie atteint', 'tify' ),
			)
		);
	}
		
	/** == FORMULAIRE DE SAISIE == **/
	public function form( $post )
	{
		extract( $this->args, EXTR_SKIP );
		$metadatas = tify_meta_post_get( $post->ID, $name );
	?>	
		<div id="taboox_video_gallery" class="taboox_video_gallery">
			<ul id="taboox_video_gallery-list" class="taboox_video_gallery-list">
			<?php foreach( (array) $metadatas as $meta_id => $meta_value ) : $this->item_render( $meta_id, $meta_value, $name ); endforeach;?>
			</ul>
			<a 	href="#" 
				class="taboox_video_gallery-add button-secondary" 
				data-name="<?php echo $name;?>" 
				data-max="<?php echo $max;?>"
				data-media_title="<?php _e( 'Galerie de vidéos', 'tify' );?>"			
				data-media_button_text="<?php _e( 'Ajouter la vidéo', 'tify' );?>" 
			>
				<span class="dashicons dashicons-video-alt2" style="vertical-align:middle;"></span>&nbsp;&nbsp;<?php _e( 'Ajouter une vidéo', 'tify' );?>
			</a>
			<span class="spinner" style="display:inline-block;float:none;"></span> 			
		</div>				
	<?php
	}

	/** == RENDU D'UN ELEMENT DU FORMULAIRE == **/
	private function item_render( $meta_id = null, $meta_value = array(), $name )
	{		
		if( ! $meta_id ) :
			$meta_id = uniqid();
		endif;
		
		$attr = wp_parse_args( $meta_value, array( 'src' => '', 'poster' => '' ) );			
	?>
	<li>			
		<div class="poster"> 
			<a href="#taboox_video_gallery-poster_add"
				class="taboox_video_gallery-poster_add"
				data-media_title="<?php _e( 'Sélectionner une jaquette', 'tify' );?>"
				data-media_button_text="<?php _e( 'Ajouter la jaquette', 'tify' );?>"
				<?php echo ( $bkg = ( $attr['poster'] && ( $image = wp_get_attachment_image_src( $attr['poster'], 'thumbnail' ) ) ) )? "style=\"background-image:url($image[0]);\"" : "";?>
			>
				<?php _e( 'Changer la jaquette', 'tify' );?>
				<input type="hidden" name="tify_meta_post[<?php echo $name;?>][<?php echo $meta_id;?>][poster]" value="<?php echo $attr['poster'];?>" />
			</a>
		</div>
		<div class="src">
			<textarea 
				name="tify_meta_post[<?php echo $name;?>][<?php echo $meta_id;?>][src]" rows="5" cols="40"
				placeholder="<?php _e( 'Vidéo de la galerie ou iframe', 'tify' );?>" 
				class="taboox_video_gallery-src"><?php echo $attr['src'];?></textarea>
			<a href="#" 
				class="dashicons dashicons-admin-media taboox_video_gallery-media_add" 
				data-media_title="<?php _e( 'Sélectionner une vidéo', 'tify' );?>"
				data-media_button_text="<?php _e( 'Ajouter la vidéo', 'tify' );?>"
			></a>
		</div>
		<a href="#remove" class="tify_button_remove"></a>					
	</li>
	<?php
	}
	
	/* = ACTIONS AJAX = */
	public function wp_ajax()
	{
		$this->item_render( null, array(), $_POST['name'] );
		exit;
	}
}