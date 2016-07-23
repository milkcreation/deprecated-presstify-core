<?php
/* = Déclaration = */
add_action( 'tify_taboox_register_form', 'tify_taboox_register_form_related_posts' );
function tify_taboox_register_form_related_posts(){
	tify_taboox_register_form( 'tiFy_Taboox_RelatedPosts' );
}

/* = Formulaire de saisie = */
class tiFy_Taboox_RelatedPosts extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public	$data_name 	= 'tify_post_meta[single][_tify_taboox_related_post]',
			$data_key 	= '_tify_taboox_related_post';
	
	/* = CONSTRUCTEUR = */ 
	public function __construct(){
		parent::__construct();
				
		add_action( 'wp_ajax_mkpbx_related_posts_autocomplete', array( $this, 'wp_ajax_autocomplete' ) );
	}
	
	/* = CHARGEMENT = */
	public function current_screen( $current_screen ){
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, '_tify_taboox_related_post' );
		
		// Mise en file des scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
	}

	/* = MISE EN FILE DE SCRIPTS = */
	public function wp_admin_enqueue_scripts(){
		wp_enqueue_style( 'taboox-related_posts', $this->uri .'/admin.css' );
		wp_enqueue_script( 'taboox-related_posts', $this->uri .'/admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete' ) );
		wp_localize_script( 'taboox-related_posts', 'tify_taboox_related_posts', array(
				'maxAttempt' => __( 'Nombre maximum de contenu en relation atteint', 'tify' ),
			)
		);
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post ){
		static $tify_taboox_related_posts_order;

		$args = wp_parse_args( 
					$this->args, 
					array(
						'post_type' 	=> 'any',
						'post_status' 	=> 'publish',
						'placeholder'	=> __( 'Rechercher un contenu en relation', 'tify' ),
						'max'			=> -1
					)
				);
		extract( $args );		
	?>	
		<div id="mkpbx_post_related">
			<input type="hidden" name="<?php echo $this->data_name;?>[]" value="" />
			<input 	type="text" 
					id="mkpbx_related_posts-search" 
					class="mkpbx_related_posts-search widefat" 
					data-post_type="<?php echo $post_type;?>" 
					data-post_status="<?php echo $post_status;?>"
					data-max="<?php echo $max;?>"  
					data-name="<?php echo $this->data_name;?>" 
					placeholder="<?php echo $placeholder;?>" 
					autocomplete="off" />
			<ul id="mkpbx_related_posts-list" class="mkpbx_related_posts-list thumb-list">
			<?php 	
				if( $related_posts = $this->data_value )
					foreach( $related_posts as $post_id ) 
						if ( ! $post_id ) 
							continue;
						else 
							echo $this->admin_item_render( $post_id, $this->data_name, ++$tify_taboox_related_posts_order );
			?>	
			</ul>	
		</div>			
	<?php
	}

	/** == Rendu d'un élément == **/
	private function admin_item_render( $post_id, $name, $order ){
		$output  = "";
		$output .= "\n<li>";
		$output .= ( $thumb = wp_get_attachment_image( get_post_thumbnail_id( $post_id ), 'thumbnail', false, array( 'style' => '' ) ) )? $thumb :'<div style="background-color:#CCC; width:150px; height:150px;"></div>';
		$output .= "\n\t<h4 class=\"post_title\">".get_the_title( $post_id )."</h4>";
		$output .= "\n\t<em class=\"post_type\">".get_post_type_object( get_post_type( $post_id ) )->label."</em>";
		$output .= "\n\t<em class=\"post_status\">". get_post_status_object( get_post_status( $post_id ) )->label ."</em>";
		$output .= "\n\t<input class=\"post_id\" type=\"hidden\" name=\"{$name}[]\" value=\"{$post_id}\" />";
		$output .= "\n\t<a href=\"#remove\" class=\"tify_button_remove remove\"></a>";
		$output .= "\n\t<input type=\"text\" class=\"order\" value=\"{$order}\" size=\"1\" readonly autocomplete=\"off\"/>";	
		$output .= "\n</li>";
		
		return $output;
	}

	/**
	 * Récupération Ajax de contenus pour l'autocompletion
	 */
	function wp_ajax_autocomplete(){
		$return = array();
		
		// Vérification du type de requête
		if ( isset( $_REQUEST['autocomplete_type'] ) )
			$type = $_REQUEST['autocomplete_type'];
		else
			$type = 'add';
		
		if ( ! empty( $_REQUEST['post_type'] ) )
			$post_type = explode( ',', $_REQUEST['post_type'] );
		else
			$post_type = 'any';
		
		if ( ! empty( $_REQUEST['post_status'] ) )
			$post_status = explode( ',', $_REQUEST['post_status'] );
		else
			$post_status = 'publish';
		
		$query_args = array(
			'post_type' => $post_type,
			'post_status' => $post_status,
			's' => $_REQUEST['term'],
			'posts_per_page' => -1
		);
		
		if( isset( $_REQUEST['post__not_in'] ) )	
			$query_args['post__not_in'] = $_REQUEST['post__not_in'];

		$query_post = new WP_Query;
		$posts = $query_post->query( $query_args );
		foreach ( $posts as $post ) {
			$post->label 		= sprintf( __( '%1$s' ), $post->post_title ); 
			$post->value 		= $post->post_title;
			$post->ico 			= ( $ico = wp_get_attachment_image( get_post_thumbnail_id( $post->ID ), array(50,50), false, array( 'style' => 'float:left; margin-right:5px;' ) ) )? $ico :'<div style="background-color:#CCC; width:50px; height:50px; float:left; margin-right:5px;"></div>';
			$post->thumb 		= ( $thumb = wp_get_attachment_image( get_post_thumbnail_id( $post->ID ), 'thumbnail', false, array( 'style' => '' ) ) )? $thumb :'<div style="background-color:#CCC; width:150px; height:150px;"></div>';
			$post->type 		= get_post_type_object( $post->post_type )->label;
			$post->id 			= $post->ID;
			$post->render 		= apply_filters( 
									'mktzr_ajax_autocomplete_render', 
									"<a style=\"display:block; min-height:50px;\">"
									. $post->ico ." ". $post->label ."<br>"
									."<em style=\"font-size:0.8em;\"><strong>{$post->type}</strong></em>"
									."<em class=\"post_status\" style=\"position:absolute; right:10px; top:50%; line-height:1; margin-top:-8px; font-size:12px; font-weight:600\">". get_post_status_object( get_post_status( $post->ID ) )->label ."</em>"
									."</a>",
									$post
								  );	
			$post->display 		= $this->admin_item_render( $post->ID, $_REQUEST['name'], $_REQUEST['order'] );	
			$return[] = $post;
		}
		wp_die( json_encode( $return ) );	
	}	
}

/**
 * Vérifie l'existance de contenu en relation
 */
function mkpbx_has_related_posts( $post_id = null, $args = array() ){
	if( mkpbx_get_related_posts( $post_id, $args ) )
		return true;
}

/**
 * Récupération des contenus en relation
 */
function mkpbx_get_related_posts( $post_id = null, $args = array() ){
	$args = wp_parse_args( $args, array(
			'name' 		=> 'related_posts',
			'post_type' => 'any' 
		)
	);
	extract( $args );
	
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	if( $related_posts = get_post_meta( $post_id, '_'.$name, true ) ) :
		foreach( $related_posts as $k => $related_post ) 
			if( !$related_post ) $related_posts = array_slice( $related_posts, 1 );
	endif;
			
	return $related_posts;
}
 
/**
 * Affichage des contenus en relation
 */
function mkpbx_related_posts_display( $post_id = null, $args = array() ){
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$defaults = array( 
		'max'	=> -1,
		'echo' 	=> true 
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$output  = "";
	
	if( $related_posts = mkpbx_get_related_posts( $post_id, $args ) ) :
		$related_query = new WP_Query( array( 'post_type' => 'any', 'post__in' => $related_posts, 'posts_per_page' => $max, 'orderby' => 'post__in' ) );

		if( $related_query->have_posts() ) :
			$output .= "\n<div class=\"mkpbx_related_posts\">";	
			$output .= "\n\t<ul class=\"roller\">";
			while( $related_query->have_posts() ): $related_query->the_post();
				$item  = "\n\t\t<li>";
				$item .= "\n\t\t<a href=\"".get_permalink()."\">";
				$item .= get_the_post_thumbnail( get_the_ID(), 'thumbnail' );
				$item .= "\n\t\t\t<h3>".get_the_title( get_the_ID() )."</h3>";
				$item .= "\n\t\t</a>";
				$item .= "\n\t\t</li>";
				$output .= apply_filters( 'mkpbx_related_posts_display_item', $item, get_the_ID() );
			endwhile; ;
			$output .= "\n\t</ul>";
			$output .= "\n</div>";
		endif; wp_reset_postdata();
	endif;
	
	if( $echo )
		echo $output;
	else
		return $output;		
}