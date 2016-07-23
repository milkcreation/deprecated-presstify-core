<?php
namespace tiFy\Taboox\Front;

use tiFy\Taboox\Front;

class Fileshare extends Front
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
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
				
		// Actions et Filtres Wordpress	
		add_filter( 'mk_force_file_upload_allowed', array( $this, 'allow_file_upload' ), 10, 2 );
	}
		
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
		
		return \tify_get_post_meta( $post_id, $args['name'] );
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
		
		$output  = ""; 
		$output .= "<ul>";
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
			
			$output_item = "\n<li class=\"tify_taboox_fileshare tify_taboox_fileshare-{$args['name']}\">";
			$output_item .= "\n\t<a href=\"" . add_query_arg( array( 'file_upload_media' => $file_id, 'post_id' => $post_id, 'fileshare_name' => $args['name'] ), site_url() ) . "\" class=\"fileshare_link clearfix\"  title=\"" . __( 'Télécharger le fichier', 'tify' ) ."\">";
			
			// Icone
			if( $thumb_url )
				$output_item .= "\n\t\t<img src=\"{$thumb_url}\" class=\"mimetype_ico\" />"; 
			else
				$output_item .= "\n\t\t<i class=\"tify_taboox_fileshare_ico tify_taboox_fileshare_ico-{$ext}\"></i>"; 
			
			// Titre du fichier		
			$output_item .= "\n\t\t<span class=\"fileshare_title\">". get_the_title( $file_id ) ."</span>";
			
			// Nom du fichier
			$output_item .= "\n\t\t<span class=\"fileshare_basename\">" . wp_basename( $fileurl ) . "</span>";
			
			// Poids du fichiers				
			if(  $filesize )
				$output_item .= "\n\t\t<span class=\"fileshare_size\">". self::FormatBytes( $filesize ) . "\n\t\t</span>";
			
			$output_item .= "\n\t\t<span class=\"download-label\">".__( 'Télécharger', 'tify' )."</span>";
			
			$output_item .= "\n\t</a>";
			$output_item .= "\n</li>";
			
			$output .= apply_filters( 'tify_taboox_fileshare_item', $output_item, $file_id, $args );
			
		 endforeach;
		$output .= "</ul>"; 
		
		if( $echo)
			echo $output;
		else
			return $output; 
	}

	/* = CONTROLEUR = */
	/** == == **/
	final public function allow_file_upload( $return, $filename )
	{
		// Bypass
		if( ! isset( $_REQUEST['post_id'] ) )
			return $return;
		if( ! isset( $_REQUEST['fileshare_name'] ) )
			return $return;	
		if( ! $attachment_ids = \get_post_meta( $_REQUEST['post_id'], $_REQUEST['fileshare_name'] ) )
			return $return;
		
		$attachment_path = array();
		foreach( $attachment_ids as $attachment_id )
			$attachment_path[] = get_attached_file( $attachment_id );	
		
		return in_array( $filename, $attachment_path );
	}
	
	/** == Convertion du poids des fichiers == **/
	public static function FormatBytes( $a_bytes )
	{
	    if ($a_bytes < 1024)
	        return $a_bytes .' B';
	    elseif ($a_bytes < 1048576)
	        return round($a_bytes / 1024, 2) .' Ko';
	    elseif ($a_bytes < 1073741824)
	        return round($a_bytes / 1048576, 2) . ' Mo';
	    elseif ($a_bytes < 1099511627776)
	        return round($a_bytes / 1073741824, 2) . ' Go';
	    elseif ($a_bytes < 1125899906842624)
	        return round($a_bytes / 1099511627776, 2) .' To';
	    elseif ($a_bytes < 1152921504606846976)
	        return round($a_bytes / 1125899906842624, 2) .' Po';
	   	elseif ($a_bytes < 1180591620717411303424)
	        return round($a_bytes / 1152921504606846976, 2) .' Eo';
	    elseif ($a_bytes < 1208925819614629174706176)
	        return round($a_bytes / 1180591620717411303424, 2) .' Zo';
	    else
	        return round($a_bytes / 1208925819614629174706176, 2) .' Yo';
	}
}