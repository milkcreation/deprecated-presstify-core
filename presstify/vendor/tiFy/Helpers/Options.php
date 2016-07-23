<?php
namespace
{
	/** == Déclaration d'une section de boîte à onglets dans l'interface de gestion des options de PresstiFy  == **/ 
	function tify_options_register_node( $node = array() )
	{
		global $tiFy;
	
		return $tiFy->Kernel->Options->registerNode( $node );
	}	
}
