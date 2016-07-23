<?php
namespace tiFy\Core\Taboox;

use tiFy\Environment\App;

abstract class Helpers extends App
{
	/* = ARGUMENTS = */	
	// Intitulés des prefixes des fonctions		
	protected $Prefix = 'tify_taboox';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers	= array();
}