<?php
namespace tiFy\Core\Taboox;

use tiFy\Environment\App;

abstract class Admin extends App
{
	/* = ARGUMENTS = */
	// ID l'écran courant d'affichage du formulaire
	protected $ScreenID;
		
	// Liste des attributs définissables
	protected $SetAttrs					= array( 'ScreenID' );
	
	public		// PARAMETRES
				/// Environnement
				$screen,					// Objet écran
				$page,						
				$env,								
				$args			= array();	
	
	/* = DECLENCHEURS = */				
	/** == Initialisation globale == **/
	public function init()
	{

	}			
		
	/** == Initialisation de l'interface d'administration == **/
	public function admin_init()
	{
		
	}
	
	/** == Chargement de la page courante == **/
	public function current_screen( $current_screen )
	{

	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts()
	{
		
	}
    
	/* = CONTROLEURS = */
	/** == Formulaire de saisie  == **/
	/*public function form( $arg1 = null, $arg2 = null, $args3 = null ){}*/	
}