<?php
namespace tiFy\Components\CustomFields\Post\Subtitle;

class Subtitle
{
	private $PostType = null;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $post_type, $args = array() )
	{
		$this->PostType = $post_type;
		
		add_action( 'current_screen', array( $this, 'current_screen' ) );		
	}
	
	/* = ACTIONS = */
	/** == Chargement de l'écran courant == **/
	final public function current_screen( $current_screen )
	{
		if( $current_screen->id !== $this->PostType )
			return;
		
		tify_meta_post_register( $current_screen->id, '_subtitle', true );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
	}
	
	/** == Champ d'édition du sous titre == **/
	final public function edit_form_after_title( $post )
	{
	?>
		<input type="text" class="widefat"  name="tify_meta_post['_subtitle']" value="<?php echo get_post_meta( $post->ID, '_subtitle', true );?>" placeholder="<?php _e( 'Sous-titre', 'tify' );?>"
		 style="margin-top:10px; margin-bottom:20px; background-color: #fff; font-size: 1.4em; height: 1.7em; line-height: 100%; margin: 10 0 15px; outline: 0 none; padding: 3px 8px; width: 100%;" />
	<?php	
	}
	

}