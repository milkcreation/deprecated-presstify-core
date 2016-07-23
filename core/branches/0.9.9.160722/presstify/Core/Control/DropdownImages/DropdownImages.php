<?php
namespace tiFy\Core\Control\DropdownImages;

use tiFy\Core\Control\Factory;
use Emojione\Emojione;

class DropdownImages extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant de la classe		
	protected $ID 			= 'dropdown_images';
	
	// Instance Courante
	static $Instance = 0;
		
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{		
		wp_register_style( 'tify_control-dropdown_images', $this->Url .'/dropdown_images.css', array( ), '150122' );
		wp_register_script( 'tify_control-dropdown_images', $this->Url .'/dropdown_images.js', array( 'jquery' ), '150122', true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dropdown_images' );
		wp_enqueue_script( 'tify_control-dropdown_images' );	
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $instance = 0 )
	{
		if( ! $instance ) 
			$instance = self::$Instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_dropdown_images-'. $instance,
			'class'				=> 'tify_control_dropdown_images',
			'name'				=> 'tify_control_dropdown_images-'. $instance,			
			'echo'				=> 1,
			
			'choices'			=> array(),
			'selected' 			=> 0,
			'show_option_none'	=> self::getRelPath() .'/none.jpg',			// Chemin relatif vers
			'option_none_value' => -1,
			'cols'				=> 6, 								// Nombre de colonnes d'icônes à afficher par ligne							
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$pickerID = 'tify_control_dropdown_images-picker'. $instance;
		
		if( ! $choices ) :
			$client = Emojione::getClient();			
			$n = 0;
			foreach( (array) $client->getRuleset()->getShortcodeReplace() as $shotcode => $filename ) :				
				$src = 'https:'. $client->imagePathSVG . $filename .'.svg'. $client->cacheBustParam;
				$choices[esc_url($src)] = $src;
				if( ++$n > 10 ) break;
			endforeach;
		endif;
		
		// Ajout du choix aucun au début de la liste des choix
		if( $show_option_none ) :
			$choices = array_reverse($choices, true);
			$choices[$option_none_value] = $show_option_none;
			$choices = array_reverse( $choices, true);
		endif;

		if( $show_option_none && ! $selected  )
			$selected = $option_none_value;
		
		$seletedSrc = ( ! $selected ) ? current( $choices ) : ( isset( $choices[$selected] ) ? $choices[$selected] : $option_none_value );
		
		$output  = "";
		
		// Selecteur HTML
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_images\" data-picker=\"{$pickerID}\">\n";
		$output .= "\t<span class=\"selected\">\n";		
		$output .= "\t\t<b class=\"selection\">";
		$output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$selected}\" autocomplete=\"off\" checked=\"checked\">\n";
		$output .= "\t\t\t<img class=\"selection\" src=\"". self::base64ImgSrc( $seletedSrc ) ."\" style=\"width:100%;height:auto;\" />";
		$output .= "\t\t</b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "</div>\n";
		
		// Picker HTML
		$output  .= "<div id=\"{$pickerID}\" class=\"dropdown_images-picker\" data-selector=\"#{$id}\">\n";
		$output .= "\t<ul>\n";
		$col = 0;	
		foreach( $choices as $value => $url ) :
			/// Ouverture de ligne
			if( ! $col )
				$output .= "\t\t<li>\n\t\t\t<ul>\n";
			$output .= "\t\t\t\t<li";
			if( $selected == $value )
				$output .= " class=\"checked\"";
			$output .= ">\n";
			$output .= "\t\t\t\t\t<label>\n";
			$output .= "\t\t\t\t\t\t<b class=\"selection\">";
			$output .= "\t\t\t\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" autocomplete=\"off\" ". checked( ( $selected == $value ), true, false ) .">\n";
			$output .= "\t\t\t\t\t\t\t<img src=\"". self::base64ImgSrc( $url ) ."\" style=\"width:100%;height:auto;\" />";
			$output .= "\t\t\t\t\t\t</b>";
			$output .= "\t\t\t\t\t</label>\n";
			$output .= "\t\t\t\t</li>\n";
		
			/// Fermeture de ligne
			if( ++$col >= $cols ) :
				$output .= "\t\t\t</ul>\n\t\t</li>\n";
				$col = 0;
			endif;
		endforeach;		
		/// Fermeture de ligne si requise
		if( $col )
			$output .= "\t\t\t</ul>\n\t\t</li>\n";
		$output .= "\t</ul>\n";
		$output .= "</div>\n";
			
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = =*/
	public static function base64ImgSrc( $src )
	{
		$ext = pathinfo( $src, PATHINFO_EXTENSION );
		if( ! in_array( $ext, array( 'svg', 'png', 'jpg', 'jpeg' ) ) )
			return;
		
		switch( $ext ) :
			case 'svg' : 
				$data = 'image/svg+xml';
				break;
			default :
				$data = 'image/'. $ext;
				break;
		endswitch;
		
		if( preg_match( '/^http/', $src ) ) :
			/* @todo : utiliser curl de wordpress */
			$content = '';
		else :
			$content = file_get_contents( wp_normalize_path( ABSPATH . $src ) );
		endif;
		
		return "data:{$data};base64,". base64_encode( $content );
	}
	
}