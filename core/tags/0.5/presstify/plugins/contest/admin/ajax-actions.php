<?php
class tiFy_Contest_AjaxActions{
	/* = ARGUMENTS = */
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Contest_Master $master ){
		// Définition de la class de référence
		$this->master = $master;
	}
}