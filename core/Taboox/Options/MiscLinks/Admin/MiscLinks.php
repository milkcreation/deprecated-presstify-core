<?php
namespace tiFy\Core\Taboox\Options\MiscLinks\Admin;

class MiscLinks extends \tiFy\Core\Taboox\Options\Admin
{
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		// Traitement des arguments
		$this->args = wp_parse_args( 
			$this->args, 
			array(
				'name'		=> 'tify_taboox_misclinks',
				'title'   	=> true,
				'caption' 	=> false,
				'image'	  	=> false
			)
		);
		
		\register_setting( $this->page, $this->args['name'] );		
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */	
	public function admin_enqueue_scripts()
	{
		\tify_control_enqueue( 'dynamic_inputs' );
		\tify_control_enqueue( 'text_remaining' );
		\tify_control_enqueue( 'media_image' );
		\wp_enqueue_style( 'tify_taboox_misclinks', self::tFyAppUrl() . '/admin.css', array( ), '150626' );
		\wp_enqueue_script( 'tify_taboox_misclinks', self::tFyAppUrl() . '/admin.js', array( 'jquery' ), '150626', true );
	}
		
	/* = FORMULAIRE DE SAISIE = */
	public function form()
	{
		$values = \get_option( $this->args['name'], array() );

		tify_control_dynamic_inputs( 
			array( 
				'default' 			=> array( 'url' => '', 'title' => '', 'caption' => '', 'image' => '' ),
				'add_button_txt'	=> __( 'Ajouter un partenaire', 'tify' ),
				'name' 				=> $this->args['name'],
				'class'				=> 'links-taboox',
				'values' 			=> $values, 
				'values_cb'			=> $values ? array( $this, 'item_render' ) : false,
				'sample_html'		=> $this->new_item()
			) 
		);
	}
	
	/* = AFFICHAGE D'UN NOUVEL ÉLÉMENT = */
	private function new_item()
	{
		$output = "<div class=\"tify_taboox_misclinks\">\n"; 
		
		// IMAGE
		if( $this->args['image'] )
			$output .= tify_control_media_image( 
				array(
					'name'					=> '%%name%%[%%index%%][image]',
					'value'					=> '%%value%%[image]',
					'width' 				=> 150,
					'height' 				=> 150,
					'echo'					=> 0
				)
			);
			
		$output .= "\t<div class=\"wrapper\">\n";
		
		// LIEN
		$output .= "\t\t<div class=\"link-url tify_input_link\">\n";
		$output .= "\t\t\t<label>".__( 'Lien vers le site :','tify' )."</label>\n";
		$output .= "\t\t\t<input type=\"text\" class=\"link-url\" name=\"%%name%%[%%index%%][url]\" value=\"%%value%%[url]\" placeholder=\"". __( 'Les liens externes doivent être prefixés par http:// ou https://', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n";
		$output .= "\t\t</div>\n";
		
		// INTITULÉ
		if( $this->args['title'] ) :
			$output .= "\t\t<div class=\"link-title\">\n";
			$output .= "\t\t\t<label>".__( 'Intitulé du lien :','tify' )."</label>\n";
			$output .= "\t\t\t<input type=\"text\" class=\"link-title\" name=\"%%name%%[%%index%%][title]\" value=\"%%value%%[title]\" placeholder=\"". __( 'L\'intitulé apparait au survol du lien', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n";
			$output .= "\t\t</div>\n";
		endif;
		
		// LÉGENDE
		if( $this->args['caption'] ) :
			$output .= "\t\t<div class=\"link-caption\">\n";
			$output .= "\t\t\t<label>".__( 'Légende :','tify' )."</label>\n";
			$output .= tify_control_text_remaining( 
				array( 
					'length' 		=> 150,
					'value' 		=> '%%value%%[caption]', 
					'name' 			=> '%%name%%[%%index%%][caption]',
					'echo'			=> 0
				) 
			);
			$output .= "\t\t</div>\n";
		endif;
		
		$output .= "\t</div>\n";
		
		$output .= "</div>";
		
		return $output;
	}
	
	/* = AFFICHAGE D'UN ÉLÉMENT = */
	public function item_render( $index, $value )
	{		
	 	$output = "<div class=\"tify_taboox_misclinks\">\n"; 
		
		// IMAGE
		if( $this->args['image'] )
			$output .= tify_control_media_image( 
				array(
					'name'					=> "{$this->args['name']}[{$index}][image]",
					'value'					=> $value['image'],
					'width' 				=> 150,
					'height' 				=> 150,
					'size'					=> 'thumbnail',
					'echo'					=> 0
				)
			);
		
		$output .= "\t<div class=\"wrapper\">\n";
		// LIEN
		$output .= "\t\t<div class=\"link-url tify_input_link\">\n";
		$output .= "\t\t\t<label>".__( 'Lien vers le site :','tify' )."</label>\n";
		$output .= "\t\t\t<input type=\"text\" class=\"link-url\" name=\"{$this->args['name']}[{$index}][url]\" value=\"{$value['url']}\" placeholder=\"". __( 'Les liens externes doivent être prefixés par http:// ou https://', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n";
		$output .= "\t\t</div>\n";
		
		// INTITULÉ
		if( $this->args['title'] ) :
			$output .= "\t\t<div class=\"link-title\">\n";
			$output .= "\t\t\t<label>".__( 'Intitulé du lien :','tify' )."</label>\n";
			$output .= "\t\t\t<input type=\"text\" class=\"link-title\" name=\"{$this->args['name']}[{$index}][title]\" value=\"{$value['title']}\" placeholder=\"". __( 'L\'intitulé apparait au survol du lien', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n";
			$output .= "\t\t</div>\n";
		endif;
		
		// LÉGENDE
		if( $this->args['caption'] ) :
			$output .= "\t\t<div class=\"link-caption\">\n";
			$output .= "\t\t\t<label>".__( 'Légende :','tify' )."</label>\n";
			$output .= tify_control_text_remaining( 
				array( 
					'length' 		=> 150,
					'value' 		=> $value['caption'], 
					'name' 			=> "{$this->args['name']}[{$index}][caption]",
					'echo'			=> 0
				) 
			);
			$output .= "\t\t</div>\n";
		endif;
		
		$output .= "\t</div>\n";
		
		$output .= "</div>";
		
		return $output;
	}
}