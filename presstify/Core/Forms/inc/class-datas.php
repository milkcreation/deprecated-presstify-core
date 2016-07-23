<?php
class tiFy_Forms_Datas{
	/* = ARGUMENTS = */
	public 	// Configuration			
			$transient_prefix 		= 'tify_forms_',	// Prefixe du cache 
			$transient_expiration		= HOUR_IN_SECONDS,	// Délai d'expiration du cache MINUTE_IN_SECONDS | HOUR_IN_SECONDS | DAY_IN_SECONDS | WEEK_IN_SECONDS | YEAR_IN_SECONDS
			
			// Paramètres			
			$extra,							// 
			$session	= array(),			// Identifiant de session de formulaire
			$transient	= array(),			// Données embarquées en base de données
			$transport	= array(),			// Données embarquées par la requête
						
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */		
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
		
		// Callbacks
		$this->master->callbacks->core_set( 'form_hidden_fields', 'datas', array( $this, 'cb_form_hidden_fields' ) );
		$this->master->callbacks->core_set( 'handle_parse_request', 'datas', array( $this, 'cb_handle_parse_request' ) );
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){		
		$this->transient_expiration;
	}
	
	/* = CONTRÔLEURS = */	
	/* = SESSION DE SOUMISSION DE FORMULAIRE = */	
	/** == Initialisation de la session == **/
	public function session_init(){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;
		
		$this->session[ $_form['ID'] ] = $this->master->functions->hash( uniqid().$_form['prefix'].$_form['ID'] );
		$this->transient_init( $this->session[ $_form['ID'] ] );
		
		return $this->session[ $_form['ID'] ];
	}
	
	/** == Récupération l'identifiant de session == 
	 * @return (string) identifiant unique de session
	 */
	public function session_get(){
		// Bypass	
		if( ! $_form = $this->master->forms->get_current( ) )
			return;

		if( ! empty( $this->session[ $_form['ID'] ] ) ) :
			return $this->session[ $_form['ID'] ];
		elseif( ! empty( $_REQUEST[ 'tify_forms_results-'. $_form['ID'] ] ) ) :
			$this->transient_delete( $_REQUEST[ 'tify_forms_results-'.$_form['ID'] ] );
			return $this->session[ $_form['ID'] ] = $_REQUEST[ 'tify_forms_results-'.$_form['ID'] ];
		elseif( ! empty( $this->master->handle->original_request[ 'session-'.$_form['prefix'].'-'.$_form['ID']] ) ) :
			return $this->session[ $_form['ID'] ] = $this->master->handle->original_request[ 'session-'.$_form['prefix'].'-'.$_form['ID']];
		else :
			$this->session[ $_form['ID'] ] = null;
		endif;
		
		return $this->session[ $_form['ID'] ];
	}
	
	/* = DONNEES EMBARQUEES PAR LA BASE DE DONNEES = */
	/** == Initialisation du cache == **/
	public function transient_init( $session ){
		return set_transient( $this->transient_prefix . $session, array( 'session' => $session ), $this->transient_expiration );
	}
	
	/** == Récupération du cache ==  **/
	public function transient_get( $session = null ){
		if( ! $session )
			$session = $this->session_get();
		
		if( ! $session )
			return false;
		
		return $this->master->functions->parse_options( get_transient( $this->transient_prefix . $session ), $this->master->forms->get_options( ) );
	}
	
	/** == Suppression du cache ==  **/
	public function transient_delete( $session = null ){
		if( ! $session )
			$session = $this->session_get();
		
		if( ! $session )
			return false;
		
		return delete_transient( $this->transient_prefix . $session );
	}
	
	/** == Définition du cache ==  **/
	public function transient_set( $args = array() ){
		// Bypass
		if( ! $session = $this->session_get() )
			return;
		
		if( ! $defaults = $this->transient_get( $this->transient_prefix . $session ) )
			$defaults = $this->master->forms->get_options( );

		$args = wp_parse_args( $args, $defaults );
		
		return set_transient( $this->transient_prefix . $session, $args, $this->transient_expiration );
	}	
	
		
	/** == Vérification du cache ==  **/
	public function transient_has( $session = null ){
		if( ! $session )
			$session = $this->session_get();
		
		if( ! $session )
			return false;
		
		return ! ( false === get_transient( $this->transient_prefix . $session ) );
	}
	
	/** == Nettoyage du cache arrivé à expiration ==  **/
	public function transient_sanitize_expired(){
		return tify_purge_transient( $this->transient_prefix, $this->transient_expiration );
	}
	
	/** == DONNÉES EMBARQUÉES PAR LA REQUETE == **/
	/*** === Récupération des données embarquées === ***/
	public function transport_get( $encode = true ){
		if( $encode )		
			return base64_encode( serialize( $this->transport ) );
		else
			return $this->transport;	
	}	
	
	/*** === Décodage de la chaîne de transport === ***/
	public function transport_decode( $datas ){
		return unserialize( ( base64_decode( $datas ) ) );		
	}
	
	/*** === Encodage de la chaîne de transport === ***/
	public function transport_encode( $datas ){
		return base64_encode( serialize( $datas ) );		
	}
	
	/* = CALLBACKS = */
	/** == Champs cachés du formulaire == **/
	public function cb_form_hidden_fields( &$output, $form ){
		$slug = $form['prefix']."-".$form['ID'];
		
		$output .= "\n\t\t<input type=\"hidden\" name=\"transport-{$slug}\" value=\"". esc_attr( $this->transport_get() ) ."\">";
		$output .= "\n\t\t<input type=\"hidden\" name=\"session-{$slug}\" value=\"". esc_attr( $this->session_get() ) ."\">";
	}
	
	/** == Traitement de la requête == **/
	public function cb_handle_parse_request( &$parsed_request, $original_request ){
		// Bypass
	 	if( ! $_form = $this->master->forms->get_current() )
			return;
		
		// Traitement des données embarquées
		$parsed_request['transport'] = $original_request[ 'transport-'.$_form['prefix'].'-'.$_form['ID']];
		$this->transport = $this->transport_decode( $parsed_request['transport'] );

		/// Ajout du transport à la requête traitée
		foreach( (array) $this->transport as $field_slug => $attrs ) :
			if( $this->master->handle->get_request( $attrs ) !== false )
				continue;
			if( $this->master->steps->get_referer() === $attrs['step'] )
				continue;		
			$parsed_request['fields'][$field_slug] = $attrs;
			$parsed_request['values'][$field_slug] = is_array( $attrs['value'] ) ? array_map( 'esc_attr', $attrs['value'] ) : esc_attr( $attrs['value'] );
		endforeach;			
	
		/// Redéfinition des données embarquées
		$this->transport = $parsed_request['fields'];
		$this->parsed_request['transport'] = $this->transport_get();
	}
}