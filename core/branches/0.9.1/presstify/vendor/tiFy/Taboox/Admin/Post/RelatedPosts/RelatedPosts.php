<?php
namespace tiFy\Taboox\Admin\Post\RelatedPosts;

use tiFy\Taboox\Admin;

class RelatedPosts extends Admin
{
	/* = CONSTRUCTEUR = */ 
	public function __construct()
	{
		parent::__construct();
				
		add_action( 'wp_ajax_mkpbx_related_posts_autocomplete', array( $this, 'wp_ajax_autocomplete' ) );
	}
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = 	wp_parse_args( 
			$this->args, 
			array(
				'name'			=> '_tify_taboox_related_posts',
				'post_type' 	=> 'any',
				'post_status' 	=> 'publish',
				'placeholder'	=> __( 'Rechercher un contenu en relation', 'tify' ),
				'max'			=> -1
			)
		);
		
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, $this->args['name'] );
	}

	/* = MISE EN FILE DE SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'taboox-related_posts-admin', $this->Url .'/admin.css' );
		wp_enqueue_script( 'taboox-related_posts-admin', $this->Url .'/admin.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete' ) );
		wp_localize_script( 'taboox-related_posts-admin', 'tify_taboox_related_posts', array(
				'maxAttempt' => __( 'Nombre maximum de contenu en relation atteint', 'tify' ),
			)
		);
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{	
		static $tify_taboox_related_posts_order = 0;	
	?>	
		<div id="tify_taboox_related_posts">
			<input 	type="text" 
					id="tify_taboox_related_posts-search" 
					class="tify_taboox_related_posts-search widefat" 
					data-post_type="<?php echo $this->args['post_type'];?>" 
					data-post_status="<?php echo $this->args['post_status'];?>"
					data-max="<?php echo $this->args['max'];?>"  
					data-name="<?php echo $this->args['name'];?>" 
					placeholder="<?php echo $this->args['placeholder'];?>" 
					autocomplete="off" />
			<ul id="tify_taboox_related_posts-list" class="tify_taboox_related_posts-list thumb-list">
			<?php 	
				if( $related_posts = \tify_get_post_meta( $post->ID, $this->args['name'] ) )
					foreach( $related_posts as $post_id ) 
						if ( ! $post_id ) 
							continue;
						else 
							echo $this->item_render( $post->ID, $this->data_name, ++$tify_taboox_related_posts_order );
			?>	
			</ul>	
		</div>			
	<?php
	}

	/* = RENDU D'UN ÉLÉMENT = */
	private function item_render( $post_id, $name, $order )
	{
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
	public function wp_ajax_autocomplete()
	{
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