<?php
Class tiFy_Forms_Handle{
	/* = ARGUMENTS = */
	public	// Configuration
			$handle_redirect,					 		// Url de redirection appliquée après le traitement
				
			// Paramètres
			$original_request, 							// Requête d'origine
			$parsed_request, 							// Requête translatée
			$is_handle 				= false,			// Indicateur de formulaire à traité
			
			$success				= array(),
			
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
	private function config(){}
	
	/* = PARAMETRAGE = */	
	/** == Initialisation == **/
	public function proceed()
	{		
		foreach( (array) $this->master->forms->get_list() as $form ) :
			if( $this->is_handle )
				break;
			$this->master->forms->set_current( $form );
			$this->master->callbacks->call( 'handle_proceed' );
			$this->handle();
		endforeach;			
	}
	
	/* = REQUÊTE = */
	/** == Récupération de la méthode de récupération de la soumission de formulaire
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * @return 
	 == **/
	 public function get_method( $form = null ){	 	
	 	// Bypass
	 	if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		switch( $_form['method'] ) :
			case 'post' :
				$method = $_POST;
			break;
			case 'get' :
				$method = $_GET;
			break;
			default :
			case 'request' :
				$method = $_REQUEST;
			break;
		endswitch;
	
		return $method;
	 }
	
	/** == Vérification de la soumission d'une requête pour un champs  
	 * @param array $field (requis) Tableau dimensionné d'un champ
	 * @return mixed|null Valeur de la requête
	 == **/
	 public function is_request( $field ){
	 	// Bypass
	 	if( ! $_method = $this->get_method( $field['form_id'] ) )
			return;
		
		return isset( $_method[ $field['form_prefix'] ][ $field['form_id'] ][ $field['slug'] ] );			
	 }
	
	/** == Récupération de la valeur de requête pour un champs  
	 * @param array $field (requis) Tableau dimensionné d'un champ
	 * @return mixed|null Valeur de la requête
	 == **/
	 public function get_request( $field ){
	 	// Bypass
	 	if( ! $_method = $this->get_method( $field['form_id'] ) )
			return;
		
		$request = false;
		
		if( isset( $_method[ $field['form_prefix'] ][ $field['form_id'] ][ $field['slug'] ] ) )
			$request = $_method[ $field['form_prefix'] ][ $field['form_id'] ][ $field['slug'] ];
		
		$this->master->callbacks->call( 'handle_get_request', array( &$request, $field, $_method, $this->master ) );
		
		return $request;		
	}		
		
	/**
 	* Traitement du formulaire
 	*/
	private function handle()
	{
		// Récupération du formulaire courant
		$_form = $this->master->forms->get_current();	

		// Bypass
		if( ! $this->original_request = $this->get_method() )
			return;	
		
		// Tests de sécurité
		if( ! isset( $this->original_request['_'.$_form['prefix'].'_nonce'] ) )
			return;
		$this->is_handle = true;
		/// Provenance de la soumission du formulaire	
		if( ! wp_verify_nonce( $this->original_request['_'.$_form['prefix'].'_nonce'], 'submit_'.$_form['prefix'].'-'.$_form['ID'] ) ) 
			wp_die( __( '<h2>Erreur lors de la vérification d\'origine de la soumission de formulaire</h2><p>Impossible de déterminer l\'origine de la soumission de votre formulaire.</p>', 'tify' ), array( 'form_id' => $_form['ID'] ) );
		/// Valeur de l'id du formulaire	
		if( empty( $this->original_request[ $_form['prefix'].'-form_id'] ) )
			wp_die( __( '<h2>Erreur lors de la vérification d\'origine de la soumission de formulaire</h2><p>Impossible de définir l\'ID votre formulaire.<p>', 'tify' ) );
				
		// Nettoyage du cache
		$this->master->datas->transient_sanitize_expired();
		
		// Session
		/// Vérification de session la validité de la session existante
		if( ( $session = $this->master->datas->session_get() ) && ( ! $this->master->datas->transient_has( $session ) ) )
			wp_die( __( '<h2>Erreur lors de la soumission du formulaire</h2><p>Votre session de soumission de formulaire est invalide ou arrivée à expiration</p>', 'tify' ) );
		/// Initialisation de la session
		if( ! $session )
			$session = $this->master->datas->session_init();
			
		// Mise à jour du cache
		$this->master->datas->transient_set();		
			
		// Traitement de la requête
		if( ! $this->parse_request() )
			return;
			
		// Vérification des champs de formulaire
		if( ! $this->check_request() )
			return;
			
		// Affichage du formulaire pour l'étape suivante
		if( $this->master->steps->next() )
			return;

		// Traitement de la requête
		$this->master->callbacks->call( 'handle_submit_request', array( &$this->parsed_request, $this->original_request, $this->master ) );
		
		// Affichage du formulaire et des erreurs suite au traitement de la requête	
		if( $this->master->errors->has() )
			return;		
			
		// Post traitement avant la redirection
		$this->master->callbacks->call( 'handle_before_redirect', array( &$this->parsed_request, $this->original_request, $this->master ) );
		
		//
		array_push( $this->success, $this->master->forms->get_current_id() );
		
		// Redirection après le traitement
		// IMPORTANT : doit contenir l'identifiant de session du formulaire
		$results_arg = $this->get_results_arg();		
		$location = ( ! empty( $this->original_request['_wp_http_referer'] ) ) 
			? add_query_arg( $results_arg, $this->original_request['_wp_http_referer'] ) 
			: add_query_arg( $results_arg, home_url('/') );	
		
		$this->master->callbacks->call( 'handle_redirect', array( &$location, $results_arg, $this->master ) );

		if( $location ) :
			$this->reset_request();
			wp_redirect( $location );
			exit;
		endif;
	}
	
	/**
	 * Traitement des élément de requête
	 */
	public function parse_request(){
		// Bypass
	 	if( ! $_form = $this->master->forms->get_current() )
			return;
		if( ! $request = $this->original_request )
			return;	
		
		$this->parsed_request['session'] 	= $this->master->datas->session_get();
		$this->parsed_request['form_id'] 	= $_form['ID'];
		$this->parsed_request['prefix'] 	= $_form['prefix'];	
		$this->parsed_request['submit'] 	= $request[ 'submit-'.$_form['prefix'].'-'.$_form['ID']];		
		$this->parsed_request['fields'] 	= array();
		
		// Traitement de la soumission de formulaire
		$submit = true;
		$submit = $this->parse_submit();		
		
		// Traitement des champs de formulaire
		foreach( $this->master->fields->get_fields_displayed() as $field ) :				
			// Bypass des champs qui ne doivent pas à être traiter par la requête		
			if( ! $this->master->field_types->type_supports( 'request', $field['type'] ) )
				continue;
			
			// Conservation de l'arborescence des champs non définit par le transport
			if( empty( $this->parsed_request['fields'][$field['slug']] ) )
				$this->parsed_request['fields'][$field['slug']] = $field;
			
			// Conservation de la valeur originale attribué au champ
			$this->parsed_request['fields'][$field['slug']]['original_value'] = $field['value'];
			
			// Attribution de la nouvelle valeur du champ passée par la requête de soumission
			$this->parsed_request['fields'][$field['slug']]['value'] = ( ! $value = $this->get_request( $field ) )? $this->parsed_request['fields'][$field['slug']]['value'] : ( ( is_string( $value ) ) ? stripslashes( $value ): $value );
			
			// Traitement des valeurs
			if(  $field['type'] === 'textarea' )
				$this->parsed_request['fields'][$field['slug']]['value'] = nl2br( $this->parsed_request['fields'][$field['slug']]['value'] );

			$this->master->callbacks->call( 'handle_parse_request_field', array( &$this->parsed_request['fields'][ $field['slug'] ], $this->master ) );					
		endforeach;
				
		// Conservation des valeurs
		$this->parsed_request['values'] = array();
		foreach( (array) $this->parsed_request['fields'] as $field_slug => $args )	
			$this->parsed_request['values'][$field_slug] = is_array( $args['value'] ) ? array_map( 'esc_attr', $args['value'] ) : esc_attr( $args['value'] );
				
		$this->master->callbacks->call( 'handle_parse_request', array( &$this->parsed_request, $this->original_request, $this->master ) );
	
		return $submit;
	}	
	
	/**
	 * Vérification du bouton de soumission du formulaire
	 */
	public function parse_submit(){
		$continue = true;
		
		if( ! $this->master->callbacks->call( 'handle_parse_submit', array( &$continue, $this->parsed_request['submit'], $this->master ) ) )
			return $continue;				
		
		return true;
	}	
	
	/**
	 * Vérification (Tests d'intégrité) des variables de saisie du formulaire.
	 * 
	 * @param array $request Tableau dimensionné de la requête à traité. Par défaut |$_POST|$_GET|$_REQUEST
	 */ 
	public function check_request( ){	
		$request = $this->parsed_request;
		// Bypass
		if( ! isset( $request['form_id'] ) )
			return;
		if( ! $_form = $this->master->forms->set_current( $request['form_id'] ) )
			return;
	
		foreach( $this->master->fields->get_fields_displayed() as $field ) :
			$errors = array();
			// Bypass : Le champ n'est pas présent dans la soumission de formulaire
			if( ! isset( $this->original_request[ $_form['prefix'] ][ $_form['ID'] ][ $field['slug'] ] ) && ! $field['required'] ) 
				continue;
			
			// Champs requis	
			if( $field['required'] && empty( $request['fields'][ $field['slug'] ]['value'] ) ) :
				if( is_bool( $field['required'] ) ) :
					$errors[ 'required:'. $field['slug'] ] = sprintf( __( 'Le champ "%s" ne peut être vide', 'tify' ) , $field['label'] );
				elseif( is_string( $field['required'] ) ) :
					$errors[ 'required:'. $field['slug'] ] = sprintf( $field['required'], $field['label'] );
				endif;
				$this->master->callbacks->call( 'handle_check_required', array( &$errors, $request['fields'][ $field['slug'] ], $this->master ) );
			// Tests d'integrité
			else :			
				if( $field['integrity_cb'] ) :
					$this->master->integrity->check( $field['integrity_cb'], $request['fields'][ $field['slug'] ]['value'] );
					if( isset( $this->master->integrity->errors ) ) :						
						foreach( $this->master->integrity->errors as $error ) :
							if( ! $error ) continue;
							$errors[] = sprintf( $error , $field['label'] );						
						endforeach;
						$this->master->integrity->errors = array();
					endif;
				endif;
				
				// Post traitement
				$this->master->callbacks->call( 'handle_check_request', array( &$errors, &$request['fields'][ $field['slug'] ], $this->master ) );
			endif;
			// Implémentation des erreurs
			if( ! empty( $errors ) )
				$this->master->errors->field_set( $errors, $field );			
		endforeach;
		
		if( $this->master->errors->has() )
			return;
		else
			return true;
	}
		
	/**
	 * Suppression des données de requête
	 */
	public function reset_request(){
		$this->original_request = null;
		$this->parsed_request = null;
	}
	
	/** == Arguments == **/
	public function get_results_arg(){
		return array( "tify_forms_results-". $this->master->forms->get_current_id() => $this->master->datas->session_get() );
	}
	
	/** == == **/
	public function check_success()
	{
		if( isset( $_REQUEST[ 'tify_forms_results-'. $this->master->forms->get_current_id() ] ) )
			return true;
		elseif( in_array( $this->master->forms->get_current_id(), $this->success ) )
			return true;
		
		return false;
	}
}