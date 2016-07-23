<?php
class tiFy_Membership_Capabilities{
	public	// Controleur
			$master;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Membership $master ){
		$this->master = $master;
		// Actions et Filtre Wordpress
		add_filter( 'map_meta_cap', array( $this, 'wp_map_meta_cap' ), 10, 4 );
	}
	
	/* = CONTROLEUR = */
	/** == Vérifie si l'utilisateur courant à un compte accès pro. == */
	function has_account( $user_id = 0 ){
		if( ! $user_id )
			$userid = get_current_user_id();
		if( ! $userid )
			return false;
		if( in_array( get_user_role( $userid ), array_keys( $this->master->roles ) )  )
			return true;
		
		return false;
	}
	
	/** == Récupération du status de l'utilisateur == **/
	function get_status( $user_id = 0 ){
		if( ! $user = get_userdata( $user_id ) )
			return 0;
		
		return (int) get_user_option( 'tify_membership_status', $user->ID );
	}
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == == **/
	function wp_map_meta_cap( $caps, $cap, $user_id, $args ){
		return $caps;
	}	
}

/**
 * 
 */
function tify_membership_has_account( $user_id = 0 ){
	global $tify_membership;
	return $tify_membership->capabilities->has_account( $user_id );
}

function tify_membership_status( $user_id = 0 ){
	global $tify_membership;
	return $tify_membership->capabilities->get_status( $user_id );
}
