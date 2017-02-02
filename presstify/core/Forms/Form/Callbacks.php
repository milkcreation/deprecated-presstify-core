<?php
namespace tiFy\Core\Forms\Form;

class Callbacks
{
	/* = ARGUMENTS = */
	// Paramètres
	/// Formulaire de référence
	private $Form					= null;
	
	/// Cartographie des accroche de fonctions de court-circuitage
	private $MapHooks 	= array(			
		'addon_set_form_options',
		'addon_set_field_options',
		
		'field_set',
		'field_value',
		'field_before_display',
		'field_type_output_display',
		'field_output_display',
		
		'form_set_options',
		'form_set_addons',
		'form_set_current',
		'form_before_display',			
		'form_before_output_display',
		'form_parse_action',
		'form_hidden_fields',
		'form_after_output_display',
		'form_output_display',
		'form_buttons_display',	
		
		'handle_proceed',
		'handle_get_request',			
		'handle_parse_request_field',
		'handle_parse_request',
		'handle_parse_submit',
		'handle_check_required',
		'handle_check_request',
		'handle_submit_request',
		'handle_before_redirect',
		'handle_redirect'					
	);
	
	// Fonctions de rappel déclarées
	public $Registered	= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\Forms\Form\Form $Form )
	{			
		// Définition du formulaire de référence
		$this->Form = $Form;
	}	
	
	/* = CONTROLEURS = */
	/** == == **/
	public function call( $hook, $args = array() )
	{
		if( ! isset( $this->Registered[$hook] ) )
			return;
		
		ksort( $this->Registered[$hook] );
		
		foreach( $this->Registered[$hook] as $priority => $functions ) :
			foreach( $functions as $attrs ) :
				call_user_func_array( $attrs['cb'], $args );
			endforeach;
		endforeach;
	}
		
	/** == Définition des fonctions de callback == **/
	private function _set( $hookname, $id, $callback, $priority, $type = 'core' )
	{	
		$this->Registered[$hookname][$priority][] = array( 'id' => $id, 'type' => $type, 'cb' => $callback );
	}
		
	/** == Définition des fonctions de rappel des addons == **/
	public function setAddons( $hookname, $addon_id, $callback, $priority = 10 )
	{
	 	$this->_set( $hookname, $addon_id, $callback, $priority, 'addons' );	 	
	}
	
	/** == Définition des fonctions de rappel des contrôleurs == **/
	public function setCore( $hookname, $controller_id, $callback, $priority = 10 )
	{
	 	$this->_set( $hookname, $controller_id, $callback, $priority, 'core' );	 	
	}
	 
	/** == Définition des fonctions de rappel des types de champ == **/
	public function setFieldType( $hookname, $field_type_id, $callback, $priority = 10 )
	{
	 	$this->_set( $hookname, $field_type_id, $callback, $priority, 'field_type' );
	}
	 	
	/** == Execution des fonctions de callback 
	public static function call( $hookname, $args = array() )
	{		
			// Bypass
		if( empty( self::$Functions[$hookname] ) )
			return;
		
		$callbacks = array(); 
		foreach( (array) self::$Functions[$hookname] as $type => $priorities ) :
			switch( $type ) :
				case 'addons' :	
					ksort( $priorities );
					foreach( (array) $priorities as $priority => $attrs ) :							
						foreach( (array) $attrs as $name => $functions ) :	
							if( ! Forms::getCurrent()->hasAddon( $name ) )
								continue;
							foreach( (array) $functions as $function )	:
								if( empty( $callbacks[$priority] ) )
									$callbacks[$priority] = array();
								array_push( $callbacks[$priority], array( $function, $args ) );								
							endforeach;
						endforeach;
					endforeach;
					break;
				case 'field_type' :	
					ksort( $priorities );
					foreach( (array) $priorities as $priority => $attrs ) :							
						foreach( (array) $attrs as $name => $functions ) : 
							if( ! $this->master->field_types->has_type( $name ) )
								continue;
							foreach( (array) $functions as $function ) :						
								if( empty( $callbacks[$priority] ) )
									$callbacks[$priority] = array();
								array_push( $callbacks[$priority], array( $function, $args ) );	
							endforeach;
						endforeach;
					endforeach;
					break;
				case 'core' :						
					ksort( $priorities );
					foreach( (array) $priorities as $priority => $attrs ) :							
						foreach( (array) $attrs as $name => $functions ) :							
							if( ! in_array( $name, array( 'buttons', 'datas', 'dirs', 'errors', 'steps' ) ) )
								continue;
							foreach( (array) $functions as $function ) :										
								if( empty( $callbacks[$priority] ) )
									$callbacks[$priority] = array();
								array_push( $callbacks[$priority], array( $function, $args ) );	
							endforeach;
						endforeach;
					endforeach;
					break;
			endswitch;
		endforeach;
		
		if( ! empty( $callbacks ) )
			ksort( $callbacks );
		foreach( $callbacks as $priority => $sets ) :
			foreach( $sets as $set ) :
				call_user_func_array( $set[0], $set[1] );
			endforeach;
		endforeach;
	}== **/	
}	