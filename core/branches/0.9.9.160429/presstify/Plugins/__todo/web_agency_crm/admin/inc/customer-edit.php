<?php
class tiFy_WebAgencyCRM_AdminEditCustomer extends tiFy_AdminView_EditForm{
	/* = ARGUMENTS = */
	public	// Configuration
			$roles,
			
			// Paramètres
			$is_profile_page,
			$current_user;
			
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master, tiFy_Query $query ){
		// Définition des classes de référence
		$this->master 	= $master;
				
		// Configuration
		$this->roles 	= $this->master->roles;
		
		// Instanciation de la classe parente
       	parent::__construct( $query );
	}
	
	/* = CHARGEMENT = */
	public function current_screen( $current_screen ){
		tify_form_set_current( $this->master->forms->subscribe_form_id );
	}
		
	/* = PARAMETRAGE = */
	/** == Récupération de l'élément à éditer == **/
	public function prepare_item(){
		$user_id 	= ( isset( $_REQUEST[$this->primary_key] ) ) ? (int) $_REQUEST[$this->primary_key] : 0;
		$this->item = get_user_to_edit( $user_id );
		$this->current_user = wp_get_current_user();
		if ( ! $this->is_profile_page )
			$this->is_profile_page =  ( $this->current_item() == $this->current_user->ID );
	}
	
	/* = AFFICHAGE = */	
	/** == Formulaire de saisie == **/
	public function form(){
		tify_taboox_display( $this->item );
	}	
}