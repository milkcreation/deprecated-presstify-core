<?php
namespace
{
	/* = BREADCRUMB = */
	/** == Affichage du fil d'Ariane == **/
	function tify_breadcrumb( $args = array() ){
		return tiFy\Component\Breadcrumb\Breadcrumb::display( $args );
	}
	
	/* = PAGINATION * =/
	/** == Affichage de la pagination == **/
	function tify_pagination( $args = array() ){
		return tiFy\Component\Pagination\Pagination::display( $args );
	}
}