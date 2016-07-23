<?php
abstract class tiFy_Config{
	static function parse_file( $filename ){
		$input = file_get_contents( $filename );

		return self::parse( $input );
	}	
	
	static function parse( $input ){
		$output = preg_replace_callback( '/<\?php(.+?)\?>/is', function( $matches ){ return self::php_eval( $matches );}, $input );

		return spyc_load( $output );
	}
	
	static function php_eval( $matches ){
		ob_start(); 
		eval( $matches[1] ); 
		$output = ob_get_contents(); 
		ob_clean(); 
		ob_end_flush();
		return $output; 
	}
} 