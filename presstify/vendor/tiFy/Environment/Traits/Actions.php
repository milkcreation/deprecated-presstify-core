<?php
namespace tiFy\Environment\Traits;

trait Actions
{
	/* = ARGUMENTS = */
	// Actions à déclencher
	/** @see https://codex.wordpress.org/Plugin_API/Action_Reference **/	
	protected $CallActions				= array();
	
	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array();
		
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array();
	
	// Nombre d'arguments autorisé lors de l'appel des actions
	protected $CallActionsArgsMap		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{			
		foreach( $this->CallActions as $method_name ) :
			$priority 		= ( isset( $this->CallActionsPriorityMap[$method_name] ) ) 	? (int) $this->CallActionsPriorityMap[$method_name] : 10;
			$accepted_args 	= ( isset( $this->CallActionsArgsMap[$method_name] ) ) 		? (int) $this->CallActionsArgsMap[$method_name] 	: 1;
			
			if( ! isset( $this->CallActionsFunctionsMap[$method_name] ) ) :
				$function = array( $this, (string) $method_name );
			elseif( isset( $this->CallActionsFunctionsMap[$method_name][$priority] ) ) :
				$function = array( $this, (string) $this->CallActionsFunctionsMap[$method_name][$priority] );
			else :
				$function = array( $this, (string) $this->CallActionsFunctionsMap[$method_name] );
			endif;
				
			\add_action( $method_name, array( $this, $method_name ), $priority, $accepted_args );			
		endforeach;
	}
	
	/* = APPEL DE METHODE = */
	public function __call( $method_name, $arguments )
	{			
		if( in_array( $method_name, $this->CallActions ) && method_exists( $this, $method_name ) )
			return call_user_func_array( array( $this, $method_name ), $arguments );
	}			
}
