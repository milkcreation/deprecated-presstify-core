<?php
/* = Déclaration = */
add_action( 'tify_taboox_register_form', 'tify_taboox_video_gallery_init' );
function tify_taboox_video_gallery_init(){
	tify_taboox_register_form( 'tiFy_Taboox_VideoGallery' );
}

/* = Formulaire de saisie = */
class tiFy_Taboox_VideoGallery extends tiFy_Taboox{
	public 	$data_name = 'tify_post_meta[multi][tify_taboox_video_gallery]',
			$data_key 	= '_tify_taboox_video_gallery';
	
	/* = CONSTRUCTEUR = */
	public function __construct( ){
		parent::__construct();
		// Actions et Filtres Wordpress
		add_action( 'wp_ajax_taboox_video_gallery_add_item', array( $this, 'wp_ajax_add_item' ) );
	} 
	
	public function current_screen( $screen ){
		wp_enqueue_media();
		wp_enqueue_style( 'tify_taboox_video_gallery', $this->uri .'/admin.css', array( ), '150325' );	
		wp_enqueue_script( 'tify_taboox_video_gallery', $this->uri .'/admin.js', array( 'jquery' ), '150325', true );
		wp_localize_script( 'tify_taboox_video_gallery', 'tify_taboox_video_gallery', array(
				'maxAttempt' => __( 'Nombre maximum de vidéos dans la galerie atteint', 'tify' ),
			)
		);		
	}
	
	/** == Récupération de la valeur == **/
	public function get_value( $post ){
		return tify_get_post_meta_multi( $post->ID, $this->data_key );
	}
	
	/** == Formulaire de saisie == **/
	public function form( $post ){
		$args = wp_parse_args( 
					$this->args, 
					array(
						'max'	=> -1
					)
				);
		extract( $args );
	?>	
		<div id="taboox_video_gallery-<?php echo $this->inst;?>" class="taboox_video_gallery">
			<ul id="taboox_video_gallery-list-<?php echo $this->inst;?>" class="taboox_video_gallery-list">
			<?php foreach( (array) $this->data_value as $meta_id => $meta_value ) $this->item_render( $meta_id, $meta_value, $this->data_name );?>
			</ul>
			<a 	href="#" 
				class="taboox_video_gallery-add button-secondary" 
				data-name="<?php echo $this->data_name;?>" 
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

	/** == Rendu d'un élément == **/
	private function item_render( $meta_id = null, $meta_value = array(), $name ){
		if( ! $meta_id )
			$meta_id = uniqid();
		$attr 	= wp_parse_args( $meta_value, array( 'src' => '', 'poster' => '' ) );			
	?>
	<li>			
		<div class="poster"> 
			<a href="#taboox_video_gallery-poster_add"
				class="taboox_video_gallery-poster_add"
				data-media_title="<?php _e( 'Sélectionner une jaquette', 'tify' );?>"
				data-media_button_text="<?php _e( 'Ajouter la jaquette', 'tify' );?>"
				<?php echo ( $bkg = ( $attr['poster'] && ( $image = wp_get_attachment_image_src( $attr['poster'], 'thumbnail' ) ) ) )? "style=\"background-image:url($image[0]);\"" : "";?>
			>
				<?php _e( 'Changer la jaquette', 'mkpbx' );?>
				<input type="hidden" name="<?php echo $name;?>[<?php echo $meta_id;?>][poster]" value="<?php echo $attr['poster'];?>" />
			</a>
		</div>
		<div class="src">
			<textarea 
				name="<?php echo $name;?>[<?php echo $meta_id;?>][src]" rows="5" cols="40"
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
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Ajout d'un élément via Ajax = **/
	public function wp_ajax_add_item(){
		$this->item_render( null, array(), $_POST['name'] );
		exit;
	}
}