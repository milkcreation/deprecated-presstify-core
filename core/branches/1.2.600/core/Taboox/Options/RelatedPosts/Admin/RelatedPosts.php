<?php
namespace tiFy\Core\Taboox\Options\RelatedPosts\Admin;

class RelatedPosts extends \tiFy\Core\Taboox\Options\Admin
{
	/* = ARGUMENTS = */
	// 
	static $Instance		= 0;
	
	// Elements
	protected $Items		= array();
	
	// Ordre des éléments
	protected $Order		= 0;
	
	// Actions
	protected $AjaxAction;
	
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		static::$Instance++;
		
		// Définition des arguments
		$this->AjaxAction = 'tiFyCoreTabooxOptionRelatedPostsAdminRelatedPostsItemRender'. static::$Instance;
		
		add_action( 'wp_ajax_'. $this->AjaxAction, array( $this, 'AjaxItemRender' ) );
		
		// Traitement des arguments
		$this->args = 	wp_parse_args( 
			$this->args, 
			array(
				'name'			=> 'tify_taboox_related_posts_'. static::$Instance,
				'query_args'	=> array( 'post_type' => 'any', 'post_status' => 'publish' ),
				'elements'		=> array( 'title', 'ico', 'type' ),
				'placeholder'	=> __( 'Rechercher un contenu en relation', 'tify' ),
				'max'			=> -1
			)
		);
		
		register_setting( $this->page, $this->args['name'] );		
	}
	
	/* = MISE EN FILE DE SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'tiFyCoreTabooxOptionRelatedPostsAdminRelatedPosts', self::tFyAppUrl( get_class() ) .'/RelatedPosts.css', array( 'tify_control-suggest', 'tify_control-holder_image' ) );
		wp_enqueue_script( 'tiFyCoreTabooxOptionRelatedPostsAdminRelatedPosts', self::tFyAppUrl( get_class() ) .'/RelatedPosts.js', array( 'jquery', 'jquery-ui-sortable', 'tify_control-suggest' ) );
		wp_localize_script( 'tiFyCoreTabooxOptionRelatedPostsAdminRelatedPosts', 'tiFyTabooxRelatedPostsAdmin', array(
				'maxAttempt' => __( 'Nombre maximum de contenu en relation atteint', 'tify' ),
			)
		);
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{	
		// Récupération des éléments
		$items = get_option( $this->args['name'], 0 );
		$this->Items = ! empty( $items ) ? array_map( 'intval', (array) $items  ) : array();
		
		$query_args 	= wp_parse_args( array( 'posts_per_page' => -1 ), $this->args['query_args'] );
	?>	
		<div id="tiFyTabooxRelatedPosts--<?php echo self::$Instance;?>" class="tiFyTabooxRelatedPosts tiFyTabooxRelatedPosts--<?php echo $this->args['name'];?>">
			<input type="hidden" class="tiFyTabooxRelatedPosts-action" value="<?php echo $this->AjaxAction;?>">
			<input type="hidden" class="tiFyTabooxRelatedPosts-item_name" value="<?php echo $this->args['name'];?>">
			<input type="hidden" class="tiFyTabooxRelatedPosts-item_max" value="<?php echo $this->args['max'];?>">
			<?php 
				tify_control_suggest(
					array(
						'class'			=> 'tiFyTabooxRelatedPosts-suggest',
						'placeholder'	=> $this->args['placeholder'],
						'options'		=> array(
							'minLength'	=> 2
						),
						'query_args'	=> $query_args,
						'elements'		=> $this->args['elements']
					)	
				);
			?>
			<?php $this->ItemsRender();?>
		</div>			
	<?php
	}
	
	/* = RENDU DE LA LISTE DES ELEMENTS = */
	public function ItemsRender()
	{
	?>
		<ul id="tiFyTabooxRelatedPosts-list--<?php echo self::$Instance;?>" class="tiFyTabooxRelatedPosts-list tiFyTaboox-TotemList tiFyTaboox-TotemList--sortable">
		<?php foreach( (array) $this->Items as $post_id ) : ?>
			<?php $this->ItemWrap( $post_id, $this->args['name'], ++$this->Order );?>			
		<?php endforeach;?>
		</ul>
	<?php	
	}
	
	/* = ENCAPSULATION D'UN ELEMENT = */
	public function ItemWrap( $post_id = 0, $name, $order )
	{
	?>	
		<li class="tiFyTaboox-TotemListItem tiFyTabooxRelatedPosts-listItem tiFyTabooxRelatedPosts-listItem--<?php echo $post_id;?>">	
			<div class="tiFyTaboox-TotemListItemWrapper">
				<?php $this->ItemRender( $post_id );?>
				
				<a href="#" class="tiFyTabooxRelatedPosts-listItemMetaToggle"></a>
				<ul class="tiFyTabooxRelatedPosts-listItemMeta">
					<li class="tiFyTabooxRelatedPosts-listItemPostType">
						<label><?php _e( 'Type :', 'tify');?></label>
						<?php echo get_post_type_object( get_post_type( $post_id ) )->label; ?>
					</li>
					<li class="tiFyTabooxRelatedPosts-listItemPostStatus">
						<label><?php _e( 'Statut :', 'tify');?></label>
						<?php echo get_post_status_object( get_post_status( $post_id ) )->label; ?>
					</li>
				</ul>
				
				<a href="#" class="tiFyTabooxRelatedPosts-listItemRemove tify_button_remove"></a>
				
				<input class="tiFyTabooxRelatedPosts-listItemPostID" type="hidden" name="<?php echo $name;?>[]" value="<?php echo $post_id;?>" />
			</div>	
		</li>	
	<?php
	}
	
	/* = RENDU D'UN ELEMENT = */
	public function ItemRender( $post_id = 0 )
	{
		if( ! $post_id )
			return;
		
		$query_post = new \WP_Query( 
			array( 
				'p' 		=> $post_id, 
				'post_type' => 'any' 
			) 
		);
		
		$output = "";
		if( $query_post->have_posts() ) :
			while( $query_post->have_posts() ) : $query_post->the_post();
				$output .= "";
				$output .= has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'post-thumbnail', array( 'class' => 'tiFyTaboox-TotemListItemWrapperThumbnail' ) ) : tify_control_holder_image( null, false );			
				$output .= "\t<h4 class=\"tiFyTaboox-TotemListItemWrapperTitle\">". get_the_title() ."</h4>\n";					
			endwhile; 
		endif;
		wp_reset_query();

		echo $output;
	}	
	
	/* = RECUPERATION D'UN ELEMENT VIA AJAX = */
	public function AjaxItemRender()
	{
		$post_id 	= (int) $_POST['post_id'];
		$name		= (string) $_POST['name'];
		$order		= (int)	$_POST['order'];

		$this->ItemWrap( $post_id, $name, ++$order );
		exit;
	}
}