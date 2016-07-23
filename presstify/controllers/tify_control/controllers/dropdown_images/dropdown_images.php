<?php
class tiFy_Control_DropdownImages extends tiFy_Control{
	public $id = 'dropdown_images';
		
	/* = Déclaration des scripts = */
	public function register_scripts(){
		wp_register_style( 'tify_controls-dropdown_images', $this->uri ."/dropdown_images.css", array( ), '150122' );
		wp_register_script( 'tify_controls-dropdown_images', $this->uri ."/dropdown_images.js", array( 'jquery' ), '150122', true );
	}
		
	/* = Affichage du controleur = */
	public function display( $args = array() ){
		static $instance;
		$instance++;
	
		$defaults = array(
			'id'				=> 'tify_control_dropdown_images-'. $instance,
			'class'				=> 'tify_control_dropdown_images',
			'name'				=> 'tify_control_dropdown_images-'. $instance,			
			'selected' 			=> 0,
			'echo'				=> 1,
			
			'choices'			=> array(),
			'show_option_none'	=> $this->master->path .'/assets/img/none.jpg',		// Chemin relatif vers 
			'cols'				=> 24, 										// Nombre de colonnes d'icônes à afficher par ligne
			'width'				=> 'auto',
			'height'			=> 32								
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		if( ! $choices ) :
			require_once $this->master->dir .'/assets/Emojione/Emojione.php';
			$emoji = file_get_contents( $this->master->dir .'/assets/Emojione/emoji.json' );
			$emoji = json_decode( $emoji );
			foreach( \Emojione\Emojione::$shortcode_replace as $shortname => $unicode ) :
				$name = preg_replace( '/\:/', '', $shortname );
				if( ! isset( $emoji->$name ) )
					continue;
				$filename = strtoupper($unicode);
				$src = \Emojione\Emojione::$imagePathPNG.$filename. '.png'. \Emojione\Emojione::$cacheBustParam;
				$choices[$name] = "<img src=\"{$src}\" width=\"$width\" height=\"$height\"/>";
			endforeach;
			
			array_multisort( (array) $emoji , $choices );
		endif;	
			
		$output  = "";
		$output .= "<div id=\"$id\" class=\"{$class}\" data-tify_control=\"dropdown_images\">\n";
		$output .= "\t<span class=\"selected\">\n";
		$sel_path = ( $selected ) ?  $path .'/'. $selected : ( $show_option_none ? $show_option_none : $path .'/'. current( $imgs )  );
		if( ! $src = tify_svg_img_src( ABSPATH . $sel_path ) ) $src = site_url( '/' ) . $sel_path;
		$output .= "\t\t<b><img src=\"$src\" width=\"$width\" height=\"$height\" /></b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "\t<ul>\n";
		$col = 0;
	
		if( $show_option_none ) :
		if( ! $col )
			$output .= "\t\t<li>\n\t\t\t<ul>\n";
		$output .= "\t\t\t\t<li";
		if( ! $selected )
			$output .= " class=\"checked\"";
		$output .= ">\n";
		$output .= "\t\t\t\t\t<label>\n";
		$output .= "\t\t\t\t\t\t<input type=\"radio\" name=\"$name\" value=\"0\" autocomplete=\"off\" ". checked( ! $selected, true, false ) .">\n";
		if( ! $src = tify_svg_img_src( ABSPATH . $show_option_none )  ) $src = site_url( '/' ) . $show_option_none;
		$output .= "\t\t\t\t\t\t<img src=\"$src\" width=\"$width\" height=\"$height\" /></b>\n";
		$output .= "\t\t\t\t\t</label>\n";
		$output .= "\t\t\t\t</li>\n";
		$col++;
		endif;
	
		foreach( $choices as $value => $img ) :
			// Ouverture de ligne
			if( ! $col )
				$output .= "\t\t<li>\n\t\t\t<ul>\n";
			$output .= "\t\t\t\t<li";
			if( $selected === $img )
				$output .= " class=\"checked\"";
			$output .= ">\n";
			$output .= "\t\t\t\t\t<label>\n";
			$output .= "\t\t\t\t\t\t<input type=\"radio\" name=\"$name\" value=\"$value\" autocomplete=\"off\" ". checked( ( $selected === $img ), true, false ) .">\n";
			$output .= $img;
			$output .= "\t\t\t\t\t</label>\n";
			$output .= "\t\t\t\t</li>\n";
		
			// Fermeture de ligne
			if( ++$col >= $cols ) :
				$output .= "\t\t\t</ul>\n\t\t</li>\n";
				$col = 0;
			endif;
		endforeach;
		// Fermeture de ligne si requise
		if( $col )
			$output .= "\t\t\t</ul>\n\t\t</li>\n";
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
}