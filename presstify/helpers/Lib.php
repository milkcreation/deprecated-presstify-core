<?php
namespace
{	
	// --------------------------------------------------------------------------------------------------------------------------
	/* = MODAL = */
	/** == Création d'un contrôleur d'affichage d'une modale == **/
	function tify_modal_toggle( $args = array(), $echo = true )
	{
		return tiFy\Lib\Modal\Modal::toggle( $args, $echo );
	}
	
	/** == Création d'une modale == **/
	function tify_modal( $args = array(), $echo = true  )
	{
		return tiFy\Lib\Modal\Modal::display( $args, $echo );
	}
	
	/** == Création d'un contrôleur d'affichage d'une modale video == **/
	function tify_modal_video_toggle( $args = array(), $echo = true )
	{
		return tiFy\Lib\Modal\Video::toggle( $args, $echo );
	}
	
	/** == Création d'une modale vidéo == **/
	function tify_modal_video( $args = array(), $echo = true  )
	{
		return tiFy\Lib\Modal\Video::display( $args, $echo );
	}
}