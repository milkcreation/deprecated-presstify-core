<?php 
namespace tiFy\Lib;

use tiFy\tiFy;

class Country
{
	public static function flag( $country )
	{
		$flagsLib = tiFy::$AbsDir .'/vendor/tiFy/Assets/country-flags-master';
		if( file_exists( $flagsLib .'/svg/'. $country . '.svg' ) )
			return file_get_contents( $flagsLib .'/svg/'. $country . '.svg' );
	}
}