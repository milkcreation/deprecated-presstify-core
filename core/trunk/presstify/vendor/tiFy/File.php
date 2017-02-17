<?php
namespace tiFy\Lib;

use tiFy\tiFy;
use tiFy\Lib\Checker;

class File
{
	/** 
	 * Récupère le contenu d'un fichier
	 * @param $filename chemin relatif ou chemin absolue ou url du fichier  
	 */
	public static function getContents( $filename )
	{
		$contents = ''; 
		
		// Vérifie si le chemin du fichier est une url
		if( Checker::isUrl( $filename ) ) :
			if( preg_match( '/^'. preg_quote( site_url( '/' ), '/' ) .'/', $filename ) ) :
				$filename = preg_replace( '/^'. preg_quote( site_url( '/' ), '/' ) .'/', tiFy::$AbsPath, $filename );
				if( file_exists( $filename ) ) :
					$contents = file_get_contents( $filename );
				endif;
			else :
				$response = wp_remote_get( $filename );			
				$contents = wp_remote_retrieve_body( $response );
			endif;
		elseif( file_exists( tiFy::$AbsPath . ltrim( $filename ) ) ) :
			$contents = file_get_contents( tiFy::$AbsPath . ltrim( $filename ) );
		elseif( file_exists( $filename ) ) :
			$contents = file_get_contents( $filename );
		endif;
		
		return $contents;
	}
	
	/**
	 * Récupération de l'identifiant d'un médias depuis son URL
	 */
	public static function attachmentIDFromUrl( $url )
	{
		global $wpdb;
		
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) );
	}
	
	/**
	 * Import de fichier local ou distant dans le repertoire d'upload
	 */
	public static function importMedia( $filename, $name = null, $time = null, $max_size = 0 )
	{
		if( ! $name )
			$name = basename( $filename );
		
        $filename = dirname( $filename ) . '/' . rawurlencode( basename( $filename ) );        
		$response = wp_remote_get( $filename );
		
		// 
		if ( ! $response )
			return new \WP_Error( 'tify_file_import_media_error', __( 'Le serveur distant ne répond pas', 'tify' ) );
		
		//
		$code = wp_remote_retrieve_response_code( $response );
		$message = wp_remote_retrieve_response_message( $response );
		if( $code != '200' )
			return new \WP_Error( 'tify_file_import_media_error', sprintf( __( 'Le serveur distant a retourné l\'erreur suivante : %1$d %2$s', 'tify' ), esc_html( $message ), $code ) );

		$body = wp_remote_retrieve_body( $response );	
		$upload = wp_upload_bits( $name, 0, $body );
		
		$filesize = filesize( $upload['file'] );
		
		$content_length = wp_remote_retrieve_header( $response, 'content-length' );
		/*if ( $content_length  && ( $filesize != $content_length ) ) :
			@unlink( $upload['file'] );
			return new \WP_Error( 'tify_file_import_media_error', __('La taille du fichier distant est incorrect', 'tify' ) );
		endif;*/

		if ( 0 == $filesize ) :
			@unlink( $upload['file'] );
			return new \WP_Error( 'tify_file_import_media_error', __( 'Le fichier téléchargé est vide', 'tify' ) );
		endif;

		if ( ! empty( $max_size ) && $filesize > $max_size ) :
			@unlink( $upload['file'] );
			return new \WP_Error( 'tify_file_import_media_error', sprintf(__( 'Le fichier distant est trop lourd, la limite est fixée à %s', 'tify' ), size_format( $max_size ) ) );
		endif;
		
		return $upload;	
	}
	
	/** 
	 * Import de fichier local ou distant en tant que fichier attaché
	 */
	public static function importAttachment( $filename, $postdata = array(), $name = null, $time = null, $max_size = 0 )
	{
		$media = self::importMedia( $filename, $name, $time, $max_size );
		
		if( is_wp_error( $media ) )
			return $media;
		
		$file = $media['file'];
		
		// Traitement des arguments du fichier attaché
		$attachment = array_merge(
			array(
				'post_mime_type' 	=> $media['type'],
				'guid' 				=> $media['url'],
				'post_parent' 		=> 0,
				'post_title'		=> sanitize_title( basename( $media['file'] ) ),
				'post_content' 		=> '',
				'post_excerpt' 		=> ''
			),
			$postdata	
		);
		$attachment_id = wp_insert_attachment( $attachment, $file );
		
		if ( ! is_wp_error( $attachment_id ) ) :
			\wp_update_attachment_metadata( $attachment_id, \wp_generate_attachment_metadata( $attachment_id, $file ) );
		endif;
		
		return $attachment_id;
	}
	
	/** == == **/
	public static function getAttachmentDatas( $attachment_id )
	{
		if( ! $post = get_post( $attachment_id ) )
			return;
		
		if( $post->post_type !== 'attachment' )
			return;
			
		return array(
			'ID'			=> $attachment_id,
			'title'			=> get_the_title( $attachment_id ),
			'content'		=> $post->post_content,
			'excerpt'		=> $post->post_excerpt,	
			'url'			=> wp_get_attachment_url( $attachment_id ),
			'upload'		=> tify_upload_url( $attachment_id ),
			'icon'			=> wp_get_attachment_image( $attachment_id, array( 80, 60 ), true )
		);
	}
}