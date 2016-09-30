<?php
namespace tiFy\Core\Forms\FieldTypes;

class HtmlAttrs
{
	/* = CONTROLEURS = */
	public static function getValue( $attr, $value ) 
	{
		switch( $attr ) :
			case 'autocomplete' :
				return ( $value && ( $value !== 'off' ) ) ? 'on' : 'off';				
				break;
			case 'readonly' :
				return ( $value && ( $value !== 'off' ) ) ? 'readonly' : '';
				break;
			case 'disabled' :
				return ( $value && ( $value !== 'off' ) ) ? 'disabled' : '';
				break;
			case 'onpaste' :
				return ( $value && ( $value === 'off' ) ) ? 'return false;' : '';
				break;
		endswitch;
	}
}