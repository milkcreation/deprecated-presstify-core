<?php
namespace tiFy\Plugins\Membership;

use tiFy\Environment\App;

class Capabilities extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'map_meta_cap'
	);
	// Nombre d'arguments autorisés lors de l'appel des actions
	protected $CallActionsArgsMap		= array(
		'map_meta_cap' => 4
	);
			
	/* = DECLENCHEMENT DES ACTIONS = */
	/** == == **/
	final public function map_meta_cap( $caps, $cap, $user_id, $args )
	{
		return $caps;
	}
	
	/* = CONTROLEUR = */
	/** == Vérifie si l'utilisateur courant à un compte accès pro. == */
	public static function has_account( $user_id = 0 )
	{
		if( ! $user_id )
			$userid = get_current_user_id();
		if( ! $userid )
			return false;
					
		if( in_array( get_user_role( $userid ), array_keys( \tiFy\Plugins\Membership\Membership::$Roles ) )  )
			return true;
		
		return false;
	}
	
	/** == Récupération du status de l'utilisateur == **/
	public static function get_status( $user_id = 0 )
	{
		if( ! $user = get_userdata( $user_id ) )
			return 0;
		
		return (int) get_user_option( 'tify_membership_status', $user->ID );
	}
}