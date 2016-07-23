<?php
/**
 * Déclaration de la taboox
 */
add_action( 'tify_taboox_register_form', 'tify_taboox_register_form_color_palette' );
function tify_taboox_register_form_color_palette(){
	tify_taboox_register_form( 'tify_Taboox_ColorPalette' );
}

class tify_Taboox_ColorPalette extends tiFy_Taboox{
	public 	$name = 'color_palette';

	public function current_screen( $screen ){
		wp_enqueue_style( 'taboox-color_palette', $this->uri ."/taboox-color_palette.css", array( 'tify_controls-colorpicker' ), '150325' );
		wp_enqueue_script( 'taboox-color_palette', $this->uri ."/taboox-color_palette.js", array( 'jquery', 'jquery-ui-sortable', 'tify_controls-colorpicker' ), '150325', true );
	}
		
	public function form(){
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
			<?php foreach( ( array ) $this->value  as $index => $color ) echo tify_color_palette_taboox_item( $index, $this->name, $color );?>
			</ul>
			<a class="tify_theme_color-add button-secondary" href="#">
				<span class="dashicons dashicons-art" style="vertical-align:middle;"></span>
				<?php _e( 'Ajouter une couleur', 'tify' );?>
			</a>
		</div>
	<?php	
	}
}


function tify_color_palette_taboox_item( $index, $name, $value = null ){
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

add_action( 'wp_ajax_tify_color_palette_taboox_add_item', 'tify_color_palette_taboox_ajax_add_item' );
function tify_color_palette_taboox_ajax_add_item(){
	echo tify_color_palette_taboox_item( $_POST['index'], $_POST['name'] );
	exit;
}