<?php
namespace tiFy\Control;

class Bootstrap
{
	public function __construct()
	{
		foreach( glob( __DIR__.'/*', GLOB_ONLYDIR ) as $filename ) :
			$basename 	= basename( $filename );
			$ClassName	= "tiFy\\Control\\$basename\\$basename";
			
			if( class_exists( $ClassName ) ) :
				$Class = new $ClassName;	
				$this->{$Class->ID} = $Class;
			endif;
		endforeach;	
	}
}
