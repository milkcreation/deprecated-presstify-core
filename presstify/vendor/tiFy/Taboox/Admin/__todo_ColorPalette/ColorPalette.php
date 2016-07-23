<?php
namespace tiFy\Taboox\Admin\Option\ColorPalette;

use tiFy\Taboox\Admin;

class ColorPalette extends Admin
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent:__construct();	
		add_action( 'wp_ajax_tify_color_palette_taboox_add_item', array( $this, 'wp_ajax' ) );
	}
		
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts( $current_screen )
	{
		wp_enqueue_style( 'taboox-color_palette', $this->Url ."/admin.css", array( 'tify_controls-colorpicker' ), '150325' );
		wp_enqueue_script( 'taboox-color_palette', $this->Url ."/admin.js", array( 'jquery', 'jquery-ui-sortable', 'tify_controls-colorpicker' ), '150325', true );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		// Attribution des valeurs par défaut
		if( empty( $this->value ) )
			$this->value = $this->args['default'];
		// Trie des valeurs
		$orderly = array( ); $order = array();
		if( isset( $this->value['order'] ) ) :
			$orderly = $this->value['order'];
			unset( $this->value['order'] );
		endif;
		foreach ( (array) $this->value as $key => $val ) 
			$order[$key] = array_search( $val, $orderly );
	 
		@array_multisort( $order, $this->value, ASC );
	?>
		<div id="tify_color_palette_taboox-<?php echo $this->instance;?>" class="tify_color_palette_taboox" data-name="<?php echo $this->name;?>">
			<ul>
			<?php foreach( (array) $this->value as $index => $color ) echo $this->_itemDisplay( $index, $this->name, $color );?>
			</ul>
			<a class="tify_theme_color-add button-secondary" href="#">
				<span class="dashicons dashicons-art" style="vertical-align:middle;"></span>
				<?php _e( 'Ajouter une couleur', 'tify' );?>
			</a>
		</div>
	<?php	
	}
	
	/* = AFFICHAGE D'UN ÉLÉMENT =*/
	private function _itemDisplay( $index, $name, $value = null )
	{
		if( ! isset( $value['hex'] ) ) $value['hex'] = "#FFFFFF";
		if( empty( $value['title'] ) ) $value['title'] = sprintf( __( 'Nouvelle couleur #%d', 'tify' ), $index+1 );
		$output  = "";
		$output .= "<li>";
		// Champs de saisie
		$output .= tify_control_colorpicker( 
						array( 
							'name' 		=> "{$name}[{$index}][hex]", 
							'value' 	=> $value['hex'],
							'attrs'		=> array( 'autocomplete' => 'off' ),
							'options'	=> array(
								'showInitial' 			=> false,
								'showInput' 			=> true,
								'showSelectionPalette' 	=> true,
								'showButtons' 			=> true,
								'allowEmpty' 			=> false
							),
							'echo' 		=> false
						) 
					);
		$output .= "<div class=\"title\"><input type=\"text\" name=\"{$name}[{$index}][title]\" value=\"{$value['title']}\" /></div>";
		$output .= "<input type=\"hidden\" name=\"{$name}[order][]\" value=\"{$index}\"/>";	
		// Contrôleurs
		$output .= "<a href=\"#\" class=\"dashicons dashicons-sort handle\"></a>";
		$output .= "<a href=\"#\" class=\"dashicons dashicons-no-alt delete\"></a>";
		$output .= "</li>";
		
		return $output;
	}
	
	/* = ACTION AJAX DE RECUPERATION D'UN ITEM = */
	public function wp_ajax()
	{
		$this->_itemDisplay( $_POST['index'], $_POST['name'] );
		exit;
	}
}