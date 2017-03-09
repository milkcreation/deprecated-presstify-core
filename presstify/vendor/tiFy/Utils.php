<?php
namespace tiFy\Lib;

class Utils
{
	/** == FICHIERS == **/	
	/*** === Récupére le chemin relatif vers un fichier (ou un repertoire) depuis son chemin absolu === ***/
	final public static function get_rel_filename( $filename, $original_path = ABSPATH ){
		$filename = wp_normalize_path( $filename );
		$original_path = wp_normalize_path( $original_path );

		if( ( $path = preg_replace( '/'. preg_quote( $original_path, '/' ) .'/', '', $filename ) ) && file_exists( $original_path .'/'. $path ) )	
			return ltrim( $path, '/' );
	}

	/*** === Récupére le chemin relatif vers un fichier (ou un repertoire) depuis son url absolue === ***/
	final public static function get_rel_url( $url, $original_url = null ){
		if( ! $original_url )
			$original_url = site_url();
		
		if( ( $path = preg_replace( '/'. preg_quote( $original_url, '/' ) .'/', '', $url ) ) && file_exists( ABSPATH .'/'. $path ) )	
			return ltrim( $path, '/' );
	}	
	
	/*** === Adapte l'url fournie au contexte du site === 
	 * @todo Adapter le $needle pour qu'il puisse fonctionner avec la racine du site
	***/
	final public static function get_context_url( $url, $needle = 'wp-content' ){
		if( ! preg_match( '/^'. preg_quote( $url, '/' ). '/',  site_url('/') ) ) 			
			$url = site_url('/') . substr( $url, strpos( $url, $needle ) );
			
		return $url;
	}
	
	/*** === Adapte le chemin au contexte du site === 
	 * @todo Adapter le $needle pour qu'il puisse fonctionner avec la racine du site
	***/
	final public static function get_context_filename( $filename, $needle = 'wp-content' ){
		$abspath 	= wp_normalize_path( ABSPATH );
		$filename 	= wp_normalize_path( $filename );
		if( ! preg_match( '/^'. preg_quote( $filename, '/' ). '/',  $abspath ) )
			$filename =  $abspath . substr( $filename, strpos( $filename, $needle ) );
			
		return $filename;
	}
	
	/*** === Récupère l'url d'une image dimensionnée dans le contexte du site === ***/
	final public static function get_context_img_src( $filename, $width = null, $height = null, $crop = false, $bypass = false ){
		$path = "";
		if( wp_is_stream( $filename ) ) :
			$filename 	= self::get_context_url( $filename );
			$path 		= self::get_rel_url( $filename );
		else :
			$filename 	= self::get_context_filename( $filename );
			$path 		= self::get_rel_filename( $filename );
		endif;
		
		if( ! $path )
			return;

		if( ! $width || ! $height )
			return site_url('/') . $path;
		
		return ( $image_resized = self::image_make_intermediate_size( ABSPATH . $path, $width, $height, $crop, $bypass ) ) ? site_url('/') . trailingslashit( dirname( $path ) ) . $image_resized['file'] : site_url('/') . $path;
	}	
	
	/** == Génére une version redimensionnée d'une image == 
	 * @see  /wp-includes/media.php > image_make_intermediate_size( $file, $width, $height, $crop = false )
	 * @param (bool) $bypass Force la création de l'image à chaque passe
	 **/
	final public static function image_make_intermediate_size( $file, $width, $height, $crop = false, $bypass = false ) {
		if( $bypass )
			return image_make_intermediate_size( $file, $width, $height, $crop );
		
		if ( $width || $height ) :
			$editor = wp_get_image_editor( $file );
	
			if ( is_wp_error( $editor ) || is_wp_error( $editor->resize( $width, $height, $crop ) ) )
				return false;
			
			if( ( $filename = $editor->generate_filename() ) && file_exists( $filename ) ) :
				$size = $editor->get_size();
				return array(
					'path'      => $filename,
					'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
					'width'     => $size['width'],
					'height'    => $size['height'],
					//'mime-type' => $editor->mime_type
				);
			endif;
						
			$resized_file = $editor->save();
	
			if ( ! is_wp_error( $resized_file ) && $resized_file ) :
				unset( $resized_file['path'] );
				return $resized_file;
			endif;
		endif;
		
		return false;
	}	
	
	/** == Récupération d'un SVG depuis son chemin == **/
	final public static function get_svg( $filename, $echo = true ){
		$output = "";
		
		$dom = new \DOMDocument;
		@$dom->loadXML( file_get_contents( $filename ) );
		if( $svgs = $dom->getElementsByTagName('svg') )
			foreach( $svgs as $n => $svg )
				$output .= $svgs->item($n)->C14N();		
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
}