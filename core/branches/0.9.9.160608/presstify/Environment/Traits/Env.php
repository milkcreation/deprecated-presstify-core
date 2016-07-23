<?php
namespace tiFy\Environment\Traits;

trait Env
{
	public function __get( $name )
	{
		return  \tiFy\Environment\Kernel::get( $name );
	}
	
	public function __set( $name, $value )
	{
		return  \tiFy\Environment\Kernel::set( $name, $value );
	}
}