<?php
namespace tiFy\Core\Taboox\Option\ContentHook\Helpers;

use tiFy\Core\Taboox\Option\ContentHook\Admin\ContentHook as Admin;

class ContentHook extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'content_hook';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Is', 'Get' );
		
	/* = VÉRIFICATION (après init 25) = */
	public static function Is( $hook_id, $post = 0 )
	{
		if( ! $post = get_post( $post ) )
			return;
			
		return( isset( Admin::$Registered[$hook_id]['selected'] ) && ( Admin::$Registered[$hook_id]['selected'] === $post->ID ) );
	}
	
	/* = Récupération = */
	public static function Get( $hook_id, $default = 0 )
	{
	    if( ! empty( Admin::$Registered[$hook_id]['selected'] ) )
			return Admin::$Registered[$hook_id]['selected'];
		
		return $default;
	}
}