<?php
/**
 * Déclaration de la taboox
 */

add_action( 'tify_taboox_register_form', 'tify_taboox_register_form_threesixty_view' );
function tify_taboox_register_form_threesixty_view(){
	tify_taboox_register_form( 'tiFy_Taboox_ThreeSixtyView' );
}

/**
 * Taboox d'ajout d'une vue 360 degrés d'un post
 */
class tiFy_Taboox_ThreeSixtyView extends tiFy\Core\Taboox\Form{
	public 	$name = 'threesixty_view',
	 		$max;
	
	public function __construct( ){
		parent::__construct();
		add_action( 'wp_ajax_threesixty_view_item', array( $this, 'wp_ajax' ) );	
	}

	public function current_screen( $screen ){
		$this->max = apply_filters( 'tify_threesixty_view_options', -1 );	
	}
	
	public function admin_enqueue_scripts(){
		wp_enqueue_media();
		wp_enqueue_style( 'spinkit-three-bounce' );
		wp_enqueue_style( 'tify_taboox_threesixty_view', self::tFyAppUrl() . '/admin.css', array(), 150907 );
		wp_enqueue_script( 'tify_taboox_threesixty_view', self::tFyAppUrl() . '/admin.js', array( 'jquery' ), 150907, true );
		wp_localize_script( 'tify_taboox_threesixty_view', 'tify', array(
				'max'	  => $this->max,
				'l10nMax' => __( 'Nombre maximum de vues atteint', 'tify' )
			)
		);
	}

	
	public function form( $_args = array() ){
		$this->_parse_args( $_args );
	?>
	<div id="threesixty_view-<?php echo $this->instance;?>" class="threesixty_view-taboox">
		<input type="hidden" name="tify_meta_post[single][threesixty_view]" value="" />
		<div class="threesixty_view-overlay"></div>		 
		<div class="threesixty_view-loading">
		   <div class="sk-spinner sk-spinner-three-bounce">
		      <div class="sk-bounce1"></div>
		      <div class="sk-bounce2"></div>
		      <div class="sk-bounce3"></div>
		   </div>			
		</div>
		<ul id="threesixty_view-list" class="threesixty_view-list thumb-list">		
		<?php 	
			if( ! empty( $this->value ) ) :
				$thumbs = mk_multisort( $this->value );
			
				foreach( $thumbs as $thumb ) :
					if( is_array( $thumb ) )
						echo threesixty_view_item( array( 'post_id' => $this->post->ID, 'attachment_id' => $thumb['attachment'], 'name' => 'tify_meta_post[single][threesixty_view]', 'order' => $thumb['order'] ) );
				endforeach;
			
			endif;
		?>	
		</ul>
		<a href="#" class="threesixty_view-add button-secondary" 
			data-post_id="<?php echo $this->post->ID; ?>"
			data-name="<?php echo $this->name; ?>"
			data-media_title="<?php _e( 'Galerie d\'images de la vue 360 degrés', 'tify' );?>"
			data-media_button_text="<?php _e( 'Ajouter les images à la vue 360 degrés', 'tify' );?>">
			<?php _e( 'Ajouter des images à la vue 360 degrés', 'tify' );?>
		</a>
	</div>
	<?php	
	}
	
	/**
	 * Chargement AJAX d'une vignette
	 */
	function wp_ajax(){
		echo json_encode( array(
				'html' 	=> threesixty_view_item( array( 'post_id' => $_POST['post_id'], 'attachment_id' => $_POST['attachment_id'], 'name' => "tify_meta_post[single][{$this->name}]", 'order' =>  $_POST['order'] ) ),
				'order'	=> $_POST['order']
			)
		);		 
		exit;
	}
}


/**
 * Rendu d'un élément de la vue 360 degrés
 */
function threesixty_view_item( $item, $index = 0 ){
	if( ! $index )
		$index = uniqid();
		
	$defaults = array( 
		'post_id' => get_the_ID(),
		'attachment_id' => 0,
		'name' => '',
		'order' => 1
	);	
	
	$item = wp_parse_args( $item, $defaults );
	
	extract( $item );
	
	// Bypass
	if( ! $image = wp_get_attachment_image_src( $attachment_id, 'thumbnail' ) )
		return;
	
	$output  = "";
	$output .= "\n<li>";
	$output .= "\n\t<img width=\"{$image[1]}\" height=\"{$image[2]}\" src=\"{$image[0]}\" class=\"attachment-thumbnail\" />";
	$output .= "\n\t<input type=\"hidden\" name=\"{$name}[$index][attachment]\" value=\"$attachment_id\" />";
	$output .= "\n\t<a href=\"#remove\" class=\"tify_button_remove\"></a>";
	
	$output .= "\n\t<input type=\"hidden\" class=\"order\" name=\"{$name}[$index][order]\" value=\"$order\" size=\"3\" readonly />";	
	$output .= "\n</li>";
	
	return $output;
}

/**
 * Récupération des images de la vue 360 degrés
 * @param int $post_id : ID du post
 */
function tify_get_threesixty_view( $post_id = null ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	if( $threesixty_views = get_post_meta( $post_id, '_threesixty_view', true ) ) :
		foreach( $threesixty_views as $k => $threesixty_view ) 
			if( ! $threesixty_view ) $threesixty_views = array_slice( $threesixty_views, 1 );
	endif;
			
	return $threesixty_views;
}

/**
 * Vérification d'image dans la galerie
 * @param int $post_id : ID du post
 */
function tify_has_threesixty_view( $post_id = null ){
	if( tify_get_threesixty_view( $post_id ) )
		return true;
}
/**
 * Équivalent de wp_parse_args() mais capable de fusionner des tableaux multidimensionnels
 */
function tify_wp_parse_args( &$a, $b ) {
	 $a = (array) $a;
	 $b = (array) $b;
	 $result = $b;
	 foreach ( $a as $k => &$v ) :
		 if ( is_array( $v ) && isset( $result[ $k ] ) ) :
			$result[ $k ] = tify_wp_parse_args( $v, $result[ $k ] );
		 else :
		 	$result[ $k ] = $v;
		 endif;
	 endforeach;
	 return $result;
}

/**
 * Affichage de la vue 360 degrés
 * @param int $post_id, ID du post
 * @param Array $args : Paramètres de la vue 360 degrés
 * @see http://360slider.com/default_control.html
 */
function tify_threesixty_view_display( $post_id = null, $args = array() ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	// Bypass
	if( ! tify_has_threesixty_view( $post_id ) )
		return;
	
	if( ! $images = tify_get_threesixty_view( $post_id ) )
		return;
	
	static $instances = 0;
	$instances++;
	
	// Traitement des arguments
	$defaults = array(
		// Arguments du lien
		'id' 			=> 'tify_threesixty_view-'. $post_id.'-'.$instances,
		'class'			=> '',
		'echo'			=> true,		
		// Arguments de la vue 360 degrés				
		'options'		=> array(
			'drag'					=> true, // Activer/Désactiver le déplacement de la vue 360 degrés ( souris + tactile )
			'autoplay'				=> false, // Vue 360 automatique
			'autoplaydirection'		=> 1, // Sens horaire | -1 pour sens antihoraire
			'navigation' 			=> false, // Play | Pause | Next | Prev
			'width'					=> 'auto', // Valeur ou Auto ( à définir en css )
			'height'				=> 'auto'  // Valeur ou Auto ( à définir en css )
		)		
	);
	$args = tify_wp_parse_args( $args, $defaults );

	extract( $args );
	
	$total_frames = count( $images );
	
	$output  = "";
	$output .= "\n<div id=\"{$id}\" data-tify_threesixty_view=\"container\"";
	$output .= " data-frames=\"$total_frames\" class=\"{$class} threesixty tify-threesixty_view clearfix\"";
	foreach( $options as $i => $j )
		$output .= " data-{$i}=\"{$j}\"";
	$output .= ">";
	$output .= "\n\t<ul data-tify_threesixty_view=\"list\">";
	foreach( $images as $img_id )
		if( ! empty( $img_id ) && ( $src = wp_get_attachment_image_src( $img_id['attachment'], 'full' ) ) )
			$output .= "\n\t\t<li data-tify_threesixty_view=\"{$src[0]}\"></li>";
	$output .= "\n\t</ul>";
	
	// Spinner
	$output .= "\n\t<div data-tify_threesixty_view=\"spinner\" class=\"tify-threesixty_view-spinner spinner\">";
	$output .= "\n\t\t<span>0%</span>";
	$output .= "\n\t</div>";
	
	$output .= "\n\t<ol data-tify_threesixty_view=\"display\" class=\"tify-threesixty_view-images threesixty_images\"></ol>";	

	$output .= "\n</div>";

	if( $echo ) 
		echo $output;
	else 
		return $output;
}
