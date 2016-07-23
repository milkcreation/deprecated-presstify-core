<?php
/* = Déclaration de la taboox = */
add_action( 'tify_taboox_register_form', 'tify_taboox_register_form_links' );
function tify_taboox_register_form_links(){
	tify_taboox_register_form( 'tify_Taboox_Links' );
}

class tify_Taboox_Links extends tiFy_Taboox{
	public $defaults = array(
				'title'   => true,
				'caption' => false,
				'image'	  => false
			);
	
	public function current_screen( $screen ){
		tify_control_enqueue( 'dynamic_inputs' );
		tify_control_enqueue( 'text_remaining' );
		tify_control_enqueue( 'media_image' );
		wp_enqueue_style( 'tify_taboox_links', $this->uri .'/admin.css', array( ), '150626' );
		wp_enqueue_script( 'tify_taboox_links', $this->uri .'/admin.js', array( 'jquery' ), '150626', true );
	}
	
	/* = DÉFINITION DES OPTIONS = */
	private function setOptions( $args ){
		
		$this->options = wp_parse_args( $args, $defaults );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	function form( $_args = array() ){
		$this->setOptions( $this->args );
		$values = get_option( 'links' );
	?>
		<h3><?php _e( 'Partenaires', 'gbl3u' );?></h3>		
		<?php 
		tify_control_dynamic_inputs( 
			array( 
				'default' 			=> array( 'url' => '', 'title' => '', 'caption' => '', 'image' => '' ),
				'add_button_txt'	=> __( 'Ajouter un partenaire', 'gbl3u' ),
				'name' 				=> 'links',
				'class'				=> 'links-taboox',
				'values' 			=> $values, 
				'values_cb'			=> $values ? array( $this, 'values_cb' ) : false,
				'sample_html'		=> $this->sample_html()
			) 
		);
		?>
	<?php
	}
	
	/* = AFFICHAGE D'UN ITEM = */
	function sample_html(){
		$output = "<div class=\"link\">\n"; 
		
		// IMAGE
		if( $this->options['image'] )
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
		$output .= "\t\t\t<label>".__( 'Lien vers le site :','gbl3u' )."</label>\n";
		$output .= "\t\t\t<input type=\"text\" class=\"link-url\" name=\"%%name%%[%%index%%][url]\" value=\"%%value%%[url]\" placeholder=\"". __( 'Les liens externes doivent être prefixés par http:// ou https://', 'gbl3u' ) ."\" size=\"40\" autocomplete=\"off\">\n";
		$output .= "\t\t</div>\n";
		
		// INTITULÉ
		if( $this->options['title'] ) :
			$output .= "\t\t<div class=\"link-title\">\n";
			$output .= "\t\t\t<label>".__( 'Intitulé du lien :','gbl3u' )."</label>\n";
			$output .= "\t\t\t<input type=\"text\" class=\"link-title\" name=\"%%name%%[%%index%%][title]\" value=\"%%value%%[title]\" placeholder=\"". __( 'L\'intitulé apparait au survol du lien', 'gbl3u' ) ."\" size=\"40\" autocomplete=\"off\">\n";
			$output .= "\t\t</div>\n";
		endif;
		
		// LÉGENDE
		if( $this->options['caption'] ) :
			$output .= "\t\t<div class=\"link-caption\">\n";
			$output .= "\t\t\t<label>".__( 'Légende :','gbl3u' )."</label>\n";
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
	
	/* = AFFICHAGE D'UN ITEM APRÈS ENREGISTREMENT = */
	function values_cb( $index, $value ){		
	 	$output = "<div class=\"link\">\n"; 
		
		// IMAGE
		if( $this->options['image'] )
			$output .= tify_control_media_image( 
				array(
					'name'					=> "links[{$index}][image]",
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
		$output .= "\t\t\t<label>".__( 'Lien vers le site :','gbl3u' )."</label>\n";
		$output .= "\t\t\t<input type=\"text\" class=\"link-url\" name=\"links[{$index}][url]\" value=\"{$value['url']}\" placeholder=\"". __( 'Les liens externes doivent être prefixés par http:// ou https://', 'gbl3u' ) ."\" size=\"40\" autocomplete=\"off\">\n";
		$output .= "\t\t</div>\n";
		
		// INTITULÉ
		if( $this->options['title'] ) :
			$output .= "\t\t<div class=\"link-title\">\n";
			$output .= "\t\t\t<label>".__( 'Intitulé du lien :','gbl3u' )."</label>\n";
			$output .= "\t\t\t<input type=\"text\" class=\"link-title\" name=\"links[{$index}][title]\" value=\"{$value['title']}\" placeholder=\"". __( 'L\'intitulé apparait au survol du lien', 'gbl3u' ) ."\" size=\"40\" autocomplete=\"off\">\n";
			$output .= "\t\t</div>\n";
		endif;
		
		// LÉGENDE
		if( $this->options['caption'] ) :
			$output .= "\t\t<div class=\"link-caption\">\n";
			$output .= "\t\t\t<label>".__( 'Légende :','gbl3u' )."</label>\n";
			$output .= tify_control_text_remaining( 
				array( 
					'length' 		=> 150,
					'value' 		=> $value['caption'], 
					'name' 			=> "links[{$index}][caption]",
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

/* = VÉRIFICATION = */
function tify_has_links(){
	if( get_option( 'links' ) )
		return true;
	
	return false;
}
/* = AFFICHAGE DES LIENS = */
function tify_display_links(){
	if( ! $links = get_option( 'links' ) )
		return;
	
	$output = "<ul class=\"tify_links\">\n";
	
	foreach( $links as $link ) :				
		if( ! empty( $link['url'] ) )
			$url   = $link['url'];
		else
			$url = '#';
			
		if( ! empty( $link['title'] ) )
			$title = sprintf( __( 'Lien vers %s','tify' ), $link['title'] );
		else
			$title = sprintf( __( 'Lien vers %s','tify' ), $link['url'] );
		
		$output .= "\t<li>\n";
		$output .= "\t\t<a href=\"{$url}\" title=\"$title\">\n";
		
		if( ! empty( $link['image'] ) )
			$output .= wp_get_attachment_image( $link['image'], 'thumbnail' );
			
		if( ! empty( $link['caption'] ) )
			$output .= "\t\t\t<div class=\"tify_links_caption\">{$link['caption']}</div>";
			
		$output .= "\t\t</a>\n";
		$output .= "\t</li>\n";
	endforeach;
		
	$output .= "</ul>";
	
	return $output;
}