<?php
class tiFy_Forum_ContributorMain{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
	}
	
	/* = CONTROLEURS = */
	/** == Vérifie si l'utilisateur courant à un compte accès pro. == */
	public function has_account( $user_id = 0 ){
		if( ! $user_id )
			$user_id = get_current_user_id();
		if( ! $user_id )
			return false;
		if( in_array( get_user_role( $user_id ), array_keys( $this->master->roles ) )  )
			return true;
		
		return false;
	}
}