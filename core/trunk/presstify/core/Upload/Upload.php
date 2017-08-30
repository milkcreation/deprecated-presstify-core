<?php
namespace tiFy\Core\Upload;

use \Defuse\Crypto\Crypto;

class Upload extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// ACTIONS
	/// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'template_redirect',
		'tify_upload_register'
	);
	
	// FILTRES
	/// Liste des actions à déclencher
	protected $CallFilters				= array(
		'query_vars',
		'media_row_actions'
	);
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array(
		'media_row_actions' => 99
	);
	
	// Nombre d'arguments autorisés
	protected $CallFiltersArgsMap		= array(
		'media_row_actions' => 3
	);
	
	
	// Liste des fichiers autorisés
	private static $AllowedFiles		= array();
	
	/* = ACTIONS = */
	/** == Arguments de requête de récupération des fichiers medias partagés == **/
	final public function query_vars( $aVars ) 
	{
		$aVars[] .= 'file_upload_url';
		$aVars[] .= 'file_upload_media';
		
		return $aVars;
	}
		
	/** == == **/
	final public function media_row_actions( $actions, $post, $detached )
	{
		$actions['download'] = "<a href=\"". wp_nonce_url( self::Url( $post->ID ), 'tify_upload-f:'.$post->ID. '-u:'. get_current_user_id() ) ."\">". __( 'Télécharger', 'tify' ) ."</a>";
		
		return $actions;
	}
	
	/** == == **/
	final public function template_redirect()
	{
		if( ! self::Get() )
		    return;
		
	    $upload_url = false;
		if( $upload_url = self::Get( 'url' ) ) :
			$upload_url		= urldecode( $upload_url );
		elseif( $attachment_id = self::Get( 'media' ) ) :
			$upload_url 	= wp_get_attachment_url( $attachment_id );
		endif;

		// Bypass - L'url vers le fichier n'est pas valide
		if( ! $upload_url )
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>L\'url vers le fichier n\'est pas valide.</p>', 'tify' ), __( 'Impossible de trouver le fichier', 'tify' ), 404 );
		
		$relpath	= trim( preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $upload_url ), '/' );	
		$abspath 	= ABSPATH . $relpath;		

		// Bypass - Le fichier n'existe pas
		if( ! file_exists( $abspath ) )
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>Le fichier n\'existe pas.</p>', 'tify'), __( 'Impossible de trouver le fichier', 'tify' ), 404  );
		
		// Bypass - Le type du fichier est indeterminé ou non référencé
		$fileinfo = wp_check_filetype( $abspath, wp_get_mime_types() );
		if( empty( $fileinfo['ext'] ) || empty( $fileinfo['type'] ) )
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>Le type du fichier est indeterminé ou non référencé.</p>', 'tify' ), __( 'Type de fichier erroné', 'tify' ), 400  );	
		
		// Bypass - Le type de fichier est interdit
		if( ! in_array( $fileinfo['type'], get_allowed_mime_types() ) )
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>Le type de fichier est interdit.</p>', 'tify' ), __( 'Type de fichier interdit', 'tify' ), 405 );	

		// 
		do_action( 'tify_upload_register', $abspath );
		
		// Bypass - Le téléchargement de ce fichier n'est pas autorisé
		if( ! in_array( $abspath, self::$AllowedFiles ) ) :
			wp_die( __( '<h1>Téléchargement du fichier impossible</h1><p>Le téléchargement de ce fichier n\'est pas autorisé.</p>', 'tify' ), __( 'Téléchargement interdit', 'tify' ), 401  );	
		endif;
			
		// Définition de la taille du fichier
		$filesize 		=  @ filesize( $abspath );
		$rangefilesize	=  $filesize-1;

		if( ini_get( 'zlib.output_compression' ) )
			ini_set( 'zlib.output_compression', 'Off' );

		clearstatcache();
		nocache_headers();
		ob_start();
		ob_end_clean();

		header( "Pragma: no-cache" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0, public, max-age=0" );
		header( "Content-Description: File Transfer" );
		header( "Accept-Ranges: bytes" );

		if( $filesize )
			header( "Content-Length: ". (string) $filesize );
		if( $filesize && $rangefilesize )
			header( "Content-Range: bytes 0-". (string) $rangefilesize ."/". (string) $filesize );

		if( isset( $fileinfo['type'] ) ) :
			header( "Content-Type: ". (string) $fileinfo['type'] );
		else :
			header( "Content-Type: application/force-download" );
		endif;

		header( "Content-Disposition: attachment; filename=". str_replace(' ', '\\', basename( $abspath ) ) );
		//header("Content-Transfer-Encoding: {$fileinfo['type']}\n");

		@ set_time_limit( 0 );

		$fp = @ fopen( $abspath, 'rb' );
		if ( $fp !== false ) :
			while ( ! feof( $fp ) ) :
				echo fread( $fp, 8192 );
			endwhile;
		fclose( $fp );
		else :
			@ readfile( $abspath );
		endif;
		ob_flush();

		do_action( 'tify_upload_callback', $abspath );

		exit;
	}
	
	/** == == **/
	public function tify_upload_register()
	{
		if( ! $file = self::Get( 'media' ) )
			return;
		if( isset( $_REQUEST['token'] ) && ( $token = get_post_meta( $file, '_tify_upload_token', true ) ) && ( $_REQUEST['token'] === $token ) )
			self::Register( $file );
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration d'un fichier à télécharger == **/
	public static function Register( $file )
	{
		$_file = false;		
		if( is_numeric( $file ) ) :		
			$abspath 	= get_attached_file( (int) $file );
			if( file_exists( $abspath ) ) :
				$_file = $abspath;
			endif;
		else :	
			if( $relpath = preg_replace( '/'. preg_quote( ABSPATH, '/' ) .'/' , '', $file ) ) :
			else :
				$relpath	=  preg_replace( '/'. preg_quote( site_url(), '/' ) .'/', '', $file );
			endif;

			$abspath 	= ABSPATH . trim( $relpath, '/' );
			if( file_exists( $abspath ) ) :
				$_file = $abspath;
			endif;
		endif;
		
		if( $_file && ! in_array( $_file, self::$AllowedFiles ) )
			array_push( self::$AllowedFiles, $_file );
	}
	
	/** == Récupération du fichier à télécharger == **/
	public static function Get( $type = null )
	{
		$file = null;
		
		if( ! $type ) :
			if( $file = get_query_var( 'file_upload_url', false ) ) :
				$file = Crypto::decryptWithPassword( $file, NONCE_KEY );
				//$file = preg_replace( '/^'. preg_quote( site_url(), '/') .'|^'. preg_quote( ABSPATH, '/') .'/', '', $file );				
			elseif( $file = (int) get_query_var( 'file_upload_media', 0 ) ) :
			endif;
		elseif( $type === 'url' ) :
			if( $file = get_query_var( 'file_upload_url', false ) ) :
				$file = Crypto::decryptWithPassword( $file, NONCE_KEY );
				$file = preg_replace( '/^'. preg_quote( site_url(), '/') .'|^'. preg_quote( ABSPATH, '/') .'/', '', $file );
			endif;
		elseif( $type === 'media' ) :
			$file = (int) get_query_var( 'file_upload_media', 0 );
		endif;
				
		return $file;	
	}
	
	/** == Url de téléchargement d'un fichier == **/
	public static function Url( $file, $query_vars = array() )
	{
		$vars = array();		
		if( is_numeric( $file ) ) :
			$vars['file_upload_media'] = $file;

			if( $token = get_post_meta( $file, '_tify_upload_token', true ) ) :
				$vars['token'] = $token;
			else :
				$token = wp_hash( $file, 'nonce' );
				if( add_post_meta( $file, '_tify_upload_token', $token, true ) )
					$vars['token'] = $token;
			endif;			
		else :
			$file = urlencode_deep( Crypto::encryptWithPassword( $file, NONCE_KEY ) );
			$vars = array( 'file_upload_url' => $file );
		endif;
		
		$query_vars = wp_parse_args( $vars, $query_vars );
			
		return add_query_arg( $query_vars, site_url('/') );
	}
}