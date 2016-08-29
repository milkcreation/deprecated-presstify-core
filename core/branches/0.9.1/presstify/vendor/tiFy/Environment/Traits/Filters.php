<?php
namespace tiFy\Environment\Traits;

trait Filters
{
	/* = ARGUMENTS = */
	// Liste des Filtres à déclencher
	protected $CallFilters				= array();
	
	// Fonctions de rappel des filtres
	protected $CallFiltersFunctionsMap	= array();
		
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array();
	
	// Nombre d'arguments autorisé lors de l'appel des filtres
	protected $CallFiltersArgsMap		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{			
		foreach( $this->CallFilters as $method_name ) :
			$priority 		= ( isset( $this->CallFiltersPriorityMap[$method_name] ) ) 	? (int) $this->CallFiltersPriorityMap[$method_name] : 10;
			$accepted_args 	= ( isset( $this->CallFiltersArgsMap[$method_name] ) ) 		? (int) $this->CallFiltersArgsMap[$method_name] 	: 1;
			
			if( ! isset( $this->CallFiltersFunctionsMap[$method_name] ) ) :
				$function = array( $this, (string) $method_name );
			elseif( isset( $this->CallFiltersFunctionsMap[$method_name][$priority] ) ) :
				$function = array( $this, (string) $this->CallFiltersFunctionsMap[$method_name][$priority] );
			else :
				$function = array( $this, (string) $this->CallFiltersFunctionsMap[$method_name] );
			endif;
				
			\add_filter( $method_name, array( $this, $method_name ), $priority, $accepted_args );			
		endforeach;
	}
	
	/* = APPEL DE METHODE = */
	public function __call( $method_name, $arguments )
	{			
		if( in_array( $method_name, $this->CallFilters ) && method_exists( $this, $method_name ) )
			return call_user_func_array( array( $this, $method_name ), $arguments );
	}			
}