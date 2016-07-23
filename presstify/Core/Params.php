<?php
namespace tiFy\Core;

use tiFy\Environment\App;

class Params extends App
{
	/* = ARGUMENTS = */
	// 
	public static $ConfigDir;
	//
	public static $ConfigExt;
	
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_theme'	
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'after_setup_theme' => 0
	);
	
	/* = PARAMETRAGE = */	
	final public function after_setup_theme()
	{
		$dir = self::$ConfigDir = ( defined( 'TIFY_CONFIG_DIR' ) ) ? TIFY_CONFIG_DIR : get_template_directory() .'/config';
		$ext = self::$ConfigExt = ( defined( 'TIFY_CONFIG_EXT' ) && in_array( TIFY_CONFIG_EXT, array( 'json', 'yml' ) ) ) ? TIFY_CONFIG_EXT : 'yml';
		
		$attrs = array(
			'config'		=> array(
				'eval'			=> true		
			),
			'components'	=> array(
				'eval'			=> true		
			),
			'core'			=> array(
				'eval'			=> true		
			),
			'plugins'		=> array(
				'eval'			=> true		
			),
			'schema'		=> array(
				'eval'			=> true		
			)
		);
		
		// Récupération du paramétrage natif
		$_dir = @ opendir( "{$this->AbsDir}/Config" );
		if( $_dir ) :
			while ( ( $file = readdir( $_dir ) ) !== false ) :
				if ( substr( $file, 0, 1 ) == '.' )
						continue;
				$basename = basename( $file, ".yml" );
				if( ! isset( $attrs[$basename] ) )
				 	continue;
				$attr = $attrs[$basename];
				if( ! isset( ${$basename} ) )
					${$basename} = array();				
				
				${$basename} = self::_parseFilename( "{$this->AbsDir}/Config/{$file}", ${$basename}, 'yml', $attr );
			endwhile;
			closedir( $_dir );
		endif;
		
		// Récupération du paramétrage personnalisé		
		$_dir = @ opendir( $dir );
		if( $_dir ) :
			while ( ( $file = readdir( $_dir ) ) !== false ) :
				if ( substr( $file, 0, 1 ) == '.' )
						continue;
				$basename = basename( $file, ".{$ext}" );
				if( ! isset( $attrs[$basename] ) )
				 	continue;
				$attr = $attrs[$basename];
				if( ! isset( ${$basename} ) )
					${$basename} = array();

				${$basename} += self::_parseFilename( "{$dir}/{$file}", ${$basename}, $ext, $attr );
			endwhile;
			closedir( $_dir );
		endif;
		
		$this->Params = compact( array_keys( $attrs ) );
		do_action( 'after_setup_tify' );		
	}
	
	/* = Traitement d'un chemin = */
	public static function _parseFilename( $filename, $current,  $ext = 'yml', $attr = array() )
	{
		if( ! is_dir( $filename ) ) :
			if ( substr( $filename, -4 ) == ".{$ext}" ) :					
				return self::_parseConfig( $filename, $current, $attr['eval'] );
			endif;
		elseif( $subdir = @ opendir( $filename ) ) :
			$res = array();
			while ( ( $subfile = readdir( $subdir ) ) !== false ) :
				if ( substr( $subfile, 0, 1 ) == '.' ) 
					continue;						
				$subbasename = basename( $subfile, ".{$ext}" );	

				$current[$subbasename] = isset( $current[$subbasename] ) ? $current[$subbasename] : array();
				$res[$subbasename] = self::_parseFilename( "$filename/{$subfile}", $current[$subbasename], $ext, $attr );
			endwhile;
			closedir( $subdir );
			return $res;
		endif;
	}
	
	/* = = */
	private static function _parseConfig( $filename, $defaults = array(), $eval = true )
	{
		if( $eval ) :
			return wp_parse_args( self::parseAndEval( $filename ), $defaults );
		else :
			return wp_parse_args( self::parseFile( $filename ), $defaults );
		endif;
	}
		
	/* = TRAITEMENT DU FICHIER DE CONFIGURATION = */
	public static function parseFile( $filename )
	{
		$input = file_get_contents( $filename );
		
		return spyc_load( $input );
	}
	
	/* = TRAITEMENT ET INTERPRETATION PHP DU FICHIER DE CONFIGURATION = */
	public static function parseAndEval( $filename )
	{
		$input = self::parseFile( $filename );
		
		return self::evalPHP( $input );
	}
	
	/* = INTERPRETATION PHP = */
	/** == Evaluation PHP == **/	
	public static function evalPHP( $input )
	{
		array_walk_recursive( $input, array( __CLASS__, '_pregReplacePHP' ) );

		return $input;
	}
	
	/** == Remplacement du code PHP par sa valeur == **/
	private static function _pregReplacePHP( &$input )
	{
		if( preg_match( '/<\?php(.+?)\?>/is', $input ) )
			$input = preg_replace_callback( '/<\?php(.+?)\?>/is', function( $matches ){ return self::_phpEvalOutput( $matches );}, $input );

		return $input;
	}
	
	/** == Récupération de la valeur du code PHP trouvé == **/
	private static function _phpEvalOutput( $matches )
	{
		ob_start();
		eval( $matches[1] );
		$output = ob_get_contents();
		ob_clean();
		ob_end_flush();
		
		return $output;
	}	
}