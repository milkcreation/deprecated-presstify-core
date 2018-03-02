<?php
namespace tiFy\Core\Templates\Admin;

class Helpers
{
	/* = STATUTS = */
	/** == Récupération des attributs == **/
	public static function getStatus( $status, $singular = true, $statuses = array() )
	{
		if( empty( $statuses[$status] ) ) :
			return $status;
		elseif( is_string( $statuses[$status] ) ) :
			return $statuses[$status];
		elseif( $singular && ! empty( $statuses[$status]['singular'] ) ) :
			return $statuses[$status]['singular'];
		elseif( ! $singular && ! empty( $statuses[$status]['plural'] ) ) :
			return $statuses[$status]['plural'];
		else :
			return $status;
		endif;		
	}
}