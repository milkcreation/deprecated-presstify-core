<?php

		// Définition des répertoires de dépôts
		/*$this->registred_dir = array(
			'temp'		=> array(
				'dirname'	=> WP_CONTENT_DIR .'/uploads/tify_forms/temp',
				'cleaning'	=> true
			),
			'upload'		=> array(
				'dirname'	=> WP_CONTENT_DIR .'/uploads/tify_forms/upload',
			),
			'export'		=> array(
				'dirname'	=> WP_CONTENT_DIR .'/uploads/tify_forms/export',
				'cleaning'	=> 3600
			)
		);
		$this->dirs->init();*/

namespace tiFy\Core\Forms;

class Files
{
	/* = ARGUMENTS = */
	public 	// Configuration			
			
			// Paramètres
			$locations;
	
	
	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init( ){
		foreach( (array) $this->master->registred_dir as $index => $dir ) :
			$_dirs = $this->parse_dir( $dir );			
			$_dirs['path'] = trailingslashit( trim( preg_replace( '/'. preg_quote( ABSPATH, '/' ) .'/', '/', $_dirs['dirname'] ) ) );			
			if( ! $_dirs['uri'] )
				$_dirs['uri'] = get_site_url( null, preg_replace( '/'. preg_quote( ABSPATH, '/' ) .'/', '', $_dirs['dirname'] ) );
			$_dirs['dirname'] = wp_normalize_path( $_dirs['dirname'] );	
			$this->locations[$index] = $_dirs;			
		endforeach;

		$this->create_dirs();		
	}
	
	public function dirname( $location ){
		if( isset( $this->locations[$location]['dirname'] ) )
			return $this->locations[$location]['dirname'];
	}
	
	public function uri( $location ){		
		if( isset( $this->locations[$location]['uri'] ) )
			return $this->locations[$location]['uri'];
	}
	
	public function path( $location ){		
		if( isset( $this->locations[$location]['path'] ) )
			return $this->locations[$location]['path'];
	}
		
	/** == Traitement des répertoires == **/
	private function parse_dir( $dir ){
		return wp_parse_args( $dir, 
			array(
				'dirname' 	=> '',
				'uri'		=> '',
				'path'		=> '',
				'mode'		=> 0777,
				'cleaning'	=> false
			)
		);
		
	}
	
	/** == Création des répertoires == **/
	private function create_dirs(){
		// TODO: Utiliser WP_Filesystem API
		foreach( $this->locations as $dir => $args ) :
			if( ! is_dir( $args['dirname'] ) && ! @ mkdir( $args['dirname'], $args['mode'], true ) )
				wp_die( __( '<h1>ERREUR SYSTEME</h1><p>Impossible de créer le dossier de stockage des fichiers téléchargés.</p>', 'tify' ) );		
			if( ! file_exists( $args['dirname'] ."/index.php" ) )
				@ copy( WP_CONTENT_DIR ."/index.php", $args['dirname'] ."/index.php" );
			/// Nettoyage des fichiers
			if( $args['cleaning'] ) :
				$lifetime = is_numeric( $args['cleaning'] ) ? $args['cleaning'] : 1*24*60*60;
			 	$files = glob( $args['dirname'] ."/*" );
				foreach( $files as $file )
					if( basename( $file ) === 'index.php' )
						continue;
					elseif( is_file( $file ) && ( ( time() - filemtime( $file ) ) >= $lifetime ) )
						@ unlink( $file );
			endif;		
		endforeach;		
	}
}