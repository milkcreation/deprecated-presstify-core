<?php
class tiFy_Forms_Callbacks{
	/* = ARGUMENTS = */
	public	// Configuration
			$hooknames,
			
			// Paramètres
			$order,		
			$functions,
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */		
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){
		// Définition des hookname
		$this->hookname = array(			
			'addon_set_form_options',
			'addon_set_field_options',
			
			'field_set',
			'field_value',
			'field_before_display',
			'field_type_output_display',
			'field_output_display',
			
			'form_set_options',
			'form_set_current',
			'form_before_display',			
			'form_before_output_display',	// Modification du pré-affichage de formulaire
			'form_parse_action',
			'form_hidden_fields',
			'form_after_output_display',	// Modification du post-affichage de formulaire
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
	}
	
	/**
	 * Définition des fonctions de callback
	 */
	private function set( $hookname, $name, $function, $priority, $type = 'core' ){
		if( ! isset( $this->functions[$hookname][$type][$priority][$name] ) )
			$this->functions[$hookname][$type][$priority][$name] = array( $function );
		else 
			array_push( $this->functions[$hookname][$type][$priority][$name], $function );	
	}
	
	/**
	 * Définition des fonctions de callback au travers du coeur
	 */
	public function core_set( $hookname, $name, $function, $priority = 10 ){
	 	$this->set( $hookname, $name, $function, $priority, 'core' );	 	
	 }

	/**
	 * Définition des fonctions de callback au travers des addons
	 */
	public function addons_set( $hookname, $name, $function, $priority = 10 ){
	 	$this->set( $hookname, $name, $function, $priority, 'addons' );	 	
	 }
	 
	/**
	 * Définition des fonctions de callback au travers des types de champ
	 */
	public function field_type_set( $hookname, $name, $function, $priority = 10 ){
	 	$this->set( $hookname, $name, $function, $priority, 'field_type' );
	 }
	 
	/**
	 * Execution des fonctions de callback
	 */
	public function call( $hookname, $args = array() ){		
		// Bypass
		if( empty( $this->functions[$hookname] ) )
			return;
		
		$callbacks = array(); 
		foreach( (array) $this->functions[$hookname] as $type => $priorities ) :
			switch( $type ) :
				case 'addons' :						
					ksort( $priorities );
					foreach( (array) $priorities as $priority => $attrs ) :							
						foreach( (array) $attrs as $name => $functions ) :							
							if( ! $this->master->addons->is_form_active( $name ) )
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
		foreach( $callbacks as $priority => $sets )
			foreach( $sets as $set )
				call_user_func_array( $set[0], $set[1] );
	}	
}	