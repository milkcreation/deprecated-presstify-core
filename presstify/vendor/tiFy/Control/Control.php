<?php
namespace tiFy\Control;

use tiFy\Environment\App;

abstract class Control extends App
{
	/* = ARGUMENTS = */	
	// Intitulés des prefixes des fonctions		
	protected $Prefix		= 'tify_control';
	
	// Identifiant des fonctions		
	protected $ID 			= '';
	
	// Liste des arguments pouvant être récupérés
	protected $GetAttrs	= array( 'ID' ); 
	
	// Liste des methodes à translater en Helpers
	protected $Helpers		= array( 'display' );
	
	// Liste de la cartographie des nom de fonction des Helpers
	protected $HelpersMap	= array( 'display' => '' );
}