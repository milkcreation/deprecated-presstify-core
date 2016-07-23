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
		'after_setup_theme' => 99
	);
	
	protected $Config		= array();
	protected $Component	= array();
	protected $Options		= array();
	protected $Plugins		= array();
	protected $Schema		= array();
	protected $Taboox		= array();
	
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
		$theme_dir = get_template_directory();
		foreach( array( 'config', 'components', 'options', 'plugins', 'schema', 'taboox' ) as $key ) :
			if( file_exists( $theme_dir ."/config/{$key}.yml" ) ) :
				if( ! in_array( $key, array( 'components', 'plugins' ) ) ) :
					${$key} = wp_parse_args( self::parseAndEval( $theme_dir ."/config/{$key}.yml" ), ${$key} );
				else :
					${$key} = wp_parse_args( self::parseFile( $theme_dir ."/config/{$key}.yml" ), ${$key} );
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