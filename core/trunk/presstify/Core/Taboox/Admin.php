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
	
	/* = INITIALISATION GLOBALE = */
	public function init()
	{

	}			
		
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		
	}
	
	/* = CHARGEMENT DE LA PAGE COURANTE = */
	public function current_screen( $current_screen )
	{

	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		
	}
		
	/* = FORMULAIRE DE SAISIE = */
	/*public function form( $arg1 = null, $arg2 = null, $args3 = null )
	{
		
		
	}*/	
}