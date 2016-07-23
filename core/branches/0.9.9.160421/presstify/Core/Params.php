<?php
namespace tiFy\Core;

use tiFy\Environment\App;

class Params extends App
{
	/* = ARGUMENTS = */
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
		// Récupération du paramétrage Natif
		foreach( array( 'config', 'components', 'options', 'plugins', 'schema', 'taboox' ) as $key ) :
			${$key} = array();
			if( file_exists( $this->AbsDir ."/Config/{$key}.yml" ) ) :
				${$key} = self::parseAndEval( $this->AbsDir ."/Config/{$key}.yml" );
			endif;
		endforeach;
		
		// Récupération de la surchage de configuration du thème actif
		$config_dir = get_template_directory() .'/config';
		foreach( array( 'config', 'components', 'options', 'plugins', 'schema' ) as $key ) :
			// 			
			if( is_dir( $config_dir . "/{$key}" ) ) :				
				foreach( glob( $config_dir ."/{$key}/*.yml" ) as $filename ) :
					$basename = basename( $filename, '.yml' );
					if( ! in_array( $key, array( 'components', 'plugins' ) ) ) :
						${$key}[$basename] = isset( ${$key}[$basename] ) ?
							wp_parse_args( self::parseAndEval( $filename ), ${$key}[$basename] ) :
							self::parseAndEval( $filename );
					else :
						${$key}[$basename] = isset( ${$key}[$basename] ) ?
							wp_parse_args( self::parseFile( $filename ), ${$key}[$basename] ) :
							self::parseAndEval( $filename );
					endif;
				endforeach;
			endif;
		
			if( file_exists( $config_dir ."/{$key}.yml" ) ) :
				if( ! in_array( $key, array( 'components', 'plugins' ) ) ) :
					${$key} = wp_parse_args( self::parseAndEval( $config_dir ."/{$key}.yml" ), ${$key} );
				else :
					${$key} = wp_parse_args( self::parseFile( $config_dir ."/{$key}.yml" ), ${$key} );
				endif;
			endif;
		endforeach;
		
		// 
		foreach( array( 'taboox' ) as $key ) :
			$key_root = $config_dir ."/{$key}";
			$key_dir = @ opendir( $config_dir ."/{$key}" );
		
			if( $key_dir ) :
				while ( ( $file = readdir( $key_dir ) ) !== false ) :
					if ( substr( $file, 0, 1 ) == '.' )
						continue;
					
					$basename = basename( $file, '.yml' );

					if ( is_dir( $key_root .'/'. $file ) ) :
						
						$key_subdir = @ opendir( $key_root .'/'. $file );
						if ( $key_subdir ) :
							while ( ( $subfile = readdir( $key_subdir ) ) !== false ) :
								if ( substr( $subfile, 0, 1 ) == '.' ) 
									continue;
								
								$sub_basename = basename( $subfile, '.yml' );	
									
								if ( substr( $subfile, -4) == '.yml' ) :
									${$key}[$basename][$sub_basename] = isset( ${$key}[$basename][$sub_basename] ) ?
										wp_parse_args( self::parseAndEval( $key_root .'/'. $file .'/'. $subfile ), ${$key}[$basename][$sub_basename] ) :
										self::parseAndEval( $key_root .'/'. $file .'/'. $subfile );
								endif;
							endwhile;
							closedir( $key_subdir );
						endif;
					else :
						if ( substr( $file, -4) == '.yml' ) :
							${$key}[$basename] = isset( ${$key}[$basename] ) ?
								wp_parse_args( self::parseAndEval( $key_root .'/'. $file ), ${$key}[$basename] ) :
								self::parseAndEval( $key_root .'/'. $file );
						endif;
					endif;					
				endwhile;
				closedir( $key_dir );
			endif;
			//
			if( file_exists( $config_dir ."/{$key}.yml" ) ) :
				if( ! in_array( $key, array( 'components', 'plugins' ) ) ) :
					${$key} = wp_parse_args( self::parseAndEval( $config_dir ."/{$key}.yml" ), ${$key} );
				else :
					${$key} = wp_parse_args( self::parseFile( $config_dir ."/{$key}.yml" ), ${$key} );
				endif;
			endif;
		endforeach;
		
		$this->Params = compact( array( 'config', 'components', 'options', 'plugins', 'schema', 'taboox' ) );

		do_action( 'after_setup_tify' );		
	}
	
	static function parseAndEval( $filename )
	{
		$input = self::parseFile( $filename );
		
		return self::evalPHP( $input );
	}
	
	static function parseFile( $filename )
	{
		$input = file_get_contents( $filename );
		
		return spyc_load( $input );
	}
	
	static function evalPHP( $input )
	{
		array_walk_recursive( $input, array( __CLASS__, 'pregReplacePHP' ) );

		return $input;
	}
	
	private static function pregReplacePHP( &$input )
	{
		if( preg_match( '/<\?php(.+?)\?>/is', $input ) )
			$input = preg_replace_callback( '/<\?php(.+?)\?>/is', function( $matches ){ return self::phpEvalOutput( $matches );}, $input );

		return $input;
	}
	
	private static function phpEvalOutput( $matches )
	{
		ob_start();
		eval( $matches[1] );
		$output = ob_get_contents();
		ob_clean();
		ob_end_flush();
		
		return $output;
	}	
}