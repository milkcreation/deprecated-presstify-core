<?php
namespace tiFy\Components\HookArchive\Taboox\Post\TermSelector\Admin;

use tiFy\Core\Taboox\Admin;
use tiFy\Core\Taboox\PostType\TaxonomySelect\Admin\TaxonomySelect;


/* = Formulaire de saisie = */
class TermSelector extends Admin
{
	/* = ARGUMENTS = */
	private $Nested;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		$this->Nested = New TaxonomySelect;	
	}
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		$this->Nested->current_screen( $current_screen );
		
		$this->args['id'] 	= 'tifyhookarchive-term-select';
		$this->Nested->args = wp_parse_args( 
			$this->args,
			$this->Nested->args
		);
		tify_meta_post_register( $current_screen->id, '_tify_hookarchive_term_permalink', true );
	}
	
	/* = MISE EN FILE DE SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_script( 'tiFy_HookArchive_Post_TermSelector_Admin', self::tFyAppUrl() . '/TermSelector.js', array( 'jquery' ), '160420' );
		$this->Nested->admin_enqueue_scripts();
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{
		$taxonomy = get_taxonomy( $this->args['taxonomy'] );
		
		if( $permalink_term = (int) get_post_meta( $post->ID, '_tify_hookarchive_term_permalink', true ) ) :
		elseif( $terms = wp_get_post_terms( $post->ID, $this->args['taxonomy'] ) ) :
			$term = current( $terms );
			$permalink_term = $term->term_id;
		else :
			$permalink_term = 0;
		endif;
	?>
		<h3><?php printf( '%s %s', $taxonomy->labels->name, __( 'd\'affichage du contenu', 'tify' ) );?></h3>	
		<?php $this->Nested->form( $post ); ?>
		<h3><?php printf( '%s %s', $taxonomy->labels->singular_name, __( 'du permalien (catégorie principale)', 'tify' ) );?></h3>
		<?php if( $terms = get_terms( array( 'taxonomy' => $this->args['taxonomy'], 'get' => 'all' ) ) ) :?>
		<ul id="tifyhookarchive-permalink-select">
			<li class="permalink_term-0">
				<label>
					<input type="radio" name="tify_meta_post[_tify_hookarchive_term_permalink]" value="-1" <?php checked( ( $permalink_term === 0 ) OR ( $permalink_term === -1 ) ); ?>/>
					<?php _e( 'Aucune', 'tify' );?>
				</label>
			</li>
			<?php foreach( (array) $terms as $term ) :?>
				<li class="permalink_term permalink_term-<?php echo $term->term_id;?>">
					<label>
						<input type="radio" name="tify_meta_post[_tify_hookarchive_term_permalink]" value="<?php echo $term->term_id;?>" <?php checked( $permalink_term === $term->term_id ); ?>//>
						<?php echo $term->name;?>
					</label>
				</li>
			<?php endforeach;?>
		</ul>	
		<?php endif;?>
	<?php	
	}
}