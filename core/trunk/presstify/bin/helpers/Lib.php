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
    // --------------------------------------------------------------------------------------------------------------------------
	/* = VIDÉO = */
    /** == Création d'un contrôleur d'affichage d'une modale video == **/
	function tify_video_modal_toggle( $args = array(), $echo = true )
	{
	    return tiFy\Lib\Video\Modal::toggle( $args, $echo );
	}
	
	/** == Création d'une modale vidéo == **/
	function tify_video_modal( $args = array(), $echo = true )
	{
	    return tiFy\Lib\Video\Modal::display( $args, $echo );
	}
	
	/** == Création d'un contrôleur d'affichage d'une vidéo en ligne == **/
	function tify_video_inline_toggle( $target = null, $args = array(), $echo = true )
	{
	    return tiFy\Lib\Video\Inline::toggle( $target, $args, $echo);
	}
	
	/** == Affichage d'une vidéo en ligne == **/
	function tify_video_inline( $attr, $echo = true )
	{
	    return tiFy\Lib\Video\Inline::display( $attr, $echo );
	}
}