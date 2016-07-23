<?php
namespace tiFy\Environment\Traits;

trait Helpers
{
	/* = ARGUMENTS = */	
	// Intitulés des prefixes des fonctions		
	protected $Prefix		= 'tify';
	
	// Identifiant des fonctions d'aide au développement		
	protected $ID 			= '';
	
	// Séparateur des parties du nom de la fonction
	protected $Separator	= '_';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers		= array();
	
	// Liste de la cartographie des nom de fonction des Helpers
	protected $HelpersMap	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		$Class = get_class( $this );
		$Class = addslashes( $Class );

		foreach( $this->Helpers as $Method ) :
			$funcNameParts = array();
			if( $this->Prefix )
				array_push( $funcNameParts, $this->Prefix );
			if( $this->ID )
				array_push( $funcNameParts, $this->ID );
			
			if( $Suffix = ( isset( $this->HelpersMap[$Method] ) ) ? $this->HelpersMap[$Method] : strtolower( $Method ) )
				array_push( $funcNameParts, $Suffix );	
			
			$funcName = implode( $this->Separator, $funcNameParts );	

			if( $funcName )
				eval( 
					'function '. $funcName . '()'.
					'{'.
						'return call_user_func_array( array( "'. $Class. '", "'. $Method .'" ), func_get_args() );'.
					'}' 
				);
		endforeach;	
	}
}