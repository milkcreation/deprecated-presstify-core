<?php
namespace tiFy\Control\DropdownImages;

use tiFy\Control\Control;
use Emojione\Emojione;

class DropdownImages extends Control
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'dropdown_images';
	
	// Nombre d'instance 
	static $Instance = 0;
	
	static $Uri;
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		self::$Uri = $this->Url;

		wp_enqueue_style( 'tify_controls-dropdown_images', $this->Url ."/dropdown_images.css", array( ), '150122' );
		wp_enqueue_script( 'tify_controls-dropdown_images', $this->Url ."/dropdown_images.js", array( 'jquery' ), '150122', true );	
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		self::$Instance++;
		$defaults = array(
			'id'				=> 'tify_control_dropdown_images-'. self::$Instance,
			'class'				=> 'tify_control_dropdown_images',
			'name'				=> 'tify_control_dropdown_images-'. self::$Instance,			
			'selected' 			=> 0,
			'echo'				=> 1,
			
			'choices'			=> array(),
			'show_option_none'	=> self::$Uri .'/none.jpg',			// Chemin relatif vers
			'option_none_value' => -1,
			'cols'				=> 6, 								// Nombre de colonnes d'icônes à afficher par ligne							
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$pickerID = 'tify_control_dropdown_images-picker'. self::$Instance;
		
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
			$choices = array_reverse($choices, true);
		endif;

		if( $show_option_none && ! $selected  )
			$selected = $option_none_value;
		
		$seletedSrc = ( ! $selected ) ? current( $choices ) : $choices[$selected];
		
		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_images\" data-picker=\"#{$pickerID}\">\n";
		$output .= "\t<span class=\"selected\">\n";		
		$output .= "\t\t<b class=\"selection\">";
		$output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$selected}\" autocomplete=\"off\" checked=\"checked\">\n";
		$output .= "\t\t\t<img class=\"selection\" src=\"". self::base64ImgSrc( $seletedSrc ) ."\" style=\"width:100%;height:auto;\" />";
		$output .= "\t\t</b>\n";
		$output .= "\t\t<i class=\"caret\"></i>\n";
		$output .= "\t</span>\n";
		$output .= "</div>\n";
		
		$pieces = array_keys( $defaults );
		array_push( $pieces, 'pickerID' );
		$_args = compact($pieces );

		$footer = function() use ( $_args ){
			extract( $_args );
			$output  = "<div id=\"{$pickerID}\" class=\"dropdown_images-picker\" data-selector=\"#{$id}\">";
			$output .= "\t<ul>\n";
			$col = 0;
		
			foreach( $choices as $value => $url ) :
				// Ouverture de ligne
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
			$output .= "</div>";
			echo $output;
		};		
		
		add_action( 'admin_footer', $footer );
		add_action( 'wp_footer', $footer );
	
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = = */
	public static function curl_file_get_contents( $url )
	{
		$options = array(
			CURLOPT_URL					=> $url,
	        CURLOPT_RETURNTRANSFER 		=> 1,     // return web page
	        CURLOPT_HEADER         		=> false,    // don't return headers
	        CURLOPT_FOLLOWLOCATION 		=> true,     // follow redirects
	        CURLOPT_ENCODING       		=> "",       // handle all encodings
	        CURLOPT_USERAGENT      		=> "spider", // who am i
	        CURLOPT_AUTOREFERER   		=> true,     // set referer on redirect
	        CURLOPT_CONNECTTIMEOUT		=> 120,      // timeout on connect
	        CURLOPT_TIMEOUT        		=> 120,      // timeout on response
	        CURLOPT_MAXREDIRS     		=> 10,       // stop after 10 redirects
			//CURLOPT_SSL_VERIFYPEER 	=> false
	    );
	
	    $ch      = curl_init();
	    curl_setopt_array( $ch, $options );
	    $content = curl_exec( $ch );
	    $err     = curl_errno( $ch );
	    $errmsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );
	    curl_close( $ch );
	
	    $header['errno']   = $err;
	    $header['errmsg']  = $errmsg;
	    $header['content'] = $content;
	    
	    return $header;
	}
	
	/* = =*/
	public static function base64ImgSrc( $src )
	{
		$data = self::curl_file_get_contents( $src );
		return 'data:'. $data['content_type'] .';base64,'. base64_encode( $data['content'] );
	}
	
}