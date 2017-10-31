<?php
namespace tiFy\Core\Taboox\PostType\Fileshare\Helpers;

class Fileshare extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'fileshare';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Has', 'Get', 'Display' );
	
	// Attributs par défaut
	public static $DefaultAttrs	= array(
		'name' 	=> '_tify_taboox_fileshare',
		'max'	=> -1
	);
				
	/* = VÉRIFICATION = */
	public static function Has( $post_id = null, $args = array() )
	{
		return self::Get( $post_id, $args );
	}
	
	/* = RÉCUPÉRATION = */
	public static function Get( $post_id = null, $args = array() )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		$args = wp_parse_args( $args, self::$DefaultAttrs );
		
		return \tify_meta_post_get( $post_id, $args['name'] );
	}
	
	/* = AFFICHAGE = */
	public static function Display( $post_id = null, $args = array(), $echo = true )
	{
		$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
		
		if( ! $files = self::Get( $post_id, $args ) )
			return;
		
		$args = wp_parse_args( $args, self::$DefaultAttrs );	
			
		$upload_dir 	= wp_upload_dir();
		$upload_path 	= $upload_dir['path'];
		$upload_url 	= $upload_dir['url']; 
		
		$ID = sanitize_key( $args['name'] );	
		
		$output  = "";
		$output .= "<div class=\"tiFyTabooxFileshare tiFyTabooxFileshare--{$ID}\">\n"; 
		$output .= "\t<ul class=\"tiFyTabooxFileshare-items\">\n";
		foreach( (array) $files as $file_id ) :
			$fileurl 	= wp_get_attachment_url( $file_id );
			$filename 	= $upload_path.'/'.wp_basename( $fileurl );
			$ext 		= preg_replace( '/^.+?\.([^.]+)$/', '$1', $fileurl );
			$filesize 	= 0;
			
			if( file_exists( $filename ) )
				$filesize = round( filesize( $filename ), 2 );
			
			$thumb_url = false;	
			if ( ( $attachment_id = intval( $file_id ) ) && $thumb_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail', false ) )
				$thumb_url = $thumb_url[0];
			
			$output .= "\t\t<li class=\"tiFyTabooxFileshare-item\">";
			$output .= "\t\t\t<a href=\"" . tify_upload_url( $file_id ) . "\" class=\"tiFyTabooxFileshare-itemUploadLink\"  title=\"" . __( 'Télécharger le fichier', 'tify' ) ."\">\n";
			
			// Icone
			if( $thumb_url ) :
				$output .= "\t\t\t\t<img src=\"{$thumb_url}\" class=\"tiFyTabooxFileshare-itemThumbnail\" />\n"; 
			else :
				$output .= "\t\t\t\t<i class=\"tiFyTabooxFileshare-itemIcon tiFyTabooxFileshare-itemIcon--{$ext}\"></i>\n"; 
			endif;
			
			// Titre du fichier		
			$output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemTitle\">". get_the_title( $file_id ) ."</span>\n";
			
			// Nom du fichier
			$output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemFilename\">" . wp_basename( $fileurl ) . "</span>\n";
			
			// Poids du fichiers				
			if(  $filesize )
				$output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemFilesize\">". self::FormatBytes( $filesize ) . "</span>\n";
			
			$output .= "\t\t\t\t<span class=\"tiFyTabooxFileshare-itemUploadLabel\">".__( 'Télécharger', 'tify' )."</span>\n";
			$output .= "\t\t\t</a>\n";
			$output .= "\t\t</li>\n";			
		 endforeach;
		$output .= "\t</ul>\n"; 
		$output .= "</div>\n";
		
		if( $echo)
			echo $output;
	
		return $output; 
	}

	/* = CONTROLEUR = */	
	/** == Convertion du poids des fichiers == **/
	public static function FormatBytes( $a_bytes )
	{
	    if ( $a_bytes < 1024 )
	        return $a_bytes .' B';
	    elseif ( $a_bytes < 1048576 )
	        return round( $a_bytes / 1024, 2 ) .' Ko';
	    elseif ( $a_bytes < 1073741824 )
	        return round( $a_bytes / 1048576, 2 ) . ' Mo';
	    elseif ( $a_bytes < 1099511627776 )
	        return round( $a_bytes / 1073741824, 2 ) . ' Go';
	    elseif ( $a_bytes < 1125899906842624 )
	        return round( $a_bytes / 1099511627776, 2 ) .' To';
	    elseif ( $a_bytes < 1152921504606846976 )
	        return round( $a_bytes / 1125899906842624, 2) .' Po';
	   	elseif ( $a_bytes < 1180591620717411303424 )
	        return round( $a_bytes / 1152921504606846976, 2 ) .' Eo';
	    elseif ( $a_bytes < 1208925819614629174706176 )
	        return round( $a_bytes / 1180591620717411303424, 2 ) .' Zo';
	    else
	        return round( $a_bytes / 1208925819614629174706176, 2 ) .' Yo';
	}
}