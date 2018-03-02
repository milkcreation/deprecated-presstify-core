<?php
namespace tiFy\Core\Forms\Form;

class Transport
{
	/* = ARGUMENTS = */
	// Paramètres
	/// Formulaire de référence
	private $Form					= null;
	
	/// Transient
	//// Prefixe du cache
	private $TransientPrefix		= 'tify_form_';
	
	//// Délai d'expiration du cache MINUTE_IN_SECONDS | HOUR_IN_SECONDS | DAY_IN_SECONDS | WEEK_IN_SECONDS | YEAR_IN_SECONDS
	private $TransientExpiration	= HOUR_IN_SECONDS;
	
	/// Attributs privés 
	private	$TransientPrivate		= array( 'session' );
	
	/// Sessions
	//// Identifiant de session
	private $Session				= null;
		
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\Forms\Form\Form $Form )
	{			
		// Définition du formulaire de référence
		$this->Form = $Form;
		
		// Nettoyage du cache
		$this->cleanTransient();
		
		// Fonctions de rappel
		//Callbacks::setCore( 'form_hidden_fields', 'transport', array( $this, 'cb_form_hidden_fields' ) );
		//Callbacks::setCore( 'handle_parse_request', 'transport', array( $this, 'cb_handle_parse_request' ) );
	}
		
	/* = CONTRÔLEURS = */
	/** == SESSION == **/
	/** == Initialisation de la session == **/
	public function initSession()
	{
		if( $this->getSession() ) :
		/*elseif( ! empty( $_REQUEST[ 'tify_forms_results-'. $_form['ID'] ] ) ) :
			$this->transient_delete( $_REQUEST[ 'tify_forms_results-'.$_form['ID'] ] );
			return $this->session[ $_form['ID'] ] = $_REQUEST[ 'tify_forms_results-'.$_form['ID'] ];*/
		elseif( $session = $this->Form->handle()->getQueryVar( 'session_'. $this->Form->getUID() ) ) :
			$this->Session = $session;			
		else :
			$this->Session = $this->generateSession();
			$this->initTransient(); 
		endif;
				
		return $this->Session;
	}
	
	/** == Création d'un indentifiant de session == **/
	private function generateSession()
	{
		return Helpers::hash( uniqid() . $this->Form->getUID() );
	}
		
	/** == Récupération l'identifiant de session == **/
	public function getSession()
	{
		return $this->Session;
	}
		
	/** == GESTION DU CACHE EN BASE DE DONNEES == **/	
	/*** === Récupération du préfix === ***/
	public function getTransientPrefix()
	{
		return $this->TransientPrefix;
	}
	
	/*** === Initialisation du cache === ***/
	public function initTransient()
	{
		return $this->setTransient();
	}
	
	/*** === Récupération d'attributs du cache ===  ***/
	public function getTransient( $attr = null )
	{
		// Bypass
		if( ! $session = $this->getSession() )
			return;
		
		$transient = get_transient( $this->getTransientPrefix() . $this->getSession() );
		
		if( is_null( $attr ) ) :
			return $transient;
		elseif( isset( $transient[ $attr ] ) ) :	
			return $transient[ $attr ];
		endif;
	}
		
	/*** === Suppression du cache ===  ***/
	public function deleteTransient( $session = null )
	{
		// Bypass
		if( ! $session = $this->getSession() )
			return;
		
		return delete_transient( $this->getTransientPrefix() . $this->getSession() );
	}
	
	/*** === Définition du cache === ***/
	public function setTransient( $data = array() )
	{
		// Bypass
		if( ! $session = $this->getSession() )
			return;
		
		foreach( (array) $this->TransientPrivate as $attr ) :
			if( isset( $data[$attr] ) )
				unset( $data[$attr] );
		endforeach;
			
		$data = wp_parse_args( $data, array( 'ID' => $this->Form->getID(), 'session' => $this->getSession() ) );
		
		return set_transient( $this->getTransientPrefix() . $this->getSession(), $data, $this->TransientExpiration );
	}
	
	/*** === Mise à jour du cache === ***/
	public function updateTransient( $data = array() )
	{
		// Bypass
		if( ! $session = $this->getSession() )
			return;
		
		foreach( (array) $this->TransientPrivate as $attr ) :
			if( isset( $data[$attr] ) )
				unset( $data[$attr] );
		endforeach;
			
		$data = wp_parse_args( $data, $this->getTransient() );
		
		return set_transient( $this->getTransientPrefix() . $this->getSession(), $data, $this->TransientExpiration );
	}	
	
	/*** === Nettoyage du cache arrivé à expiration ===  ***/
	public function cleanTransient()
	{
		return tify_purge_transient( $this->getTransientPrefix(), $this->TransientExpiration );
	}
		
	/** == DONNÉES EMBARQUÉES PAR LA REQUETE == **/
	/*** === Récupération des données embarquées === ***/
	public function transport_get( $encode = true )
	{
		if( $encode )		
			return base64_encode( serialize( $this->transport ) );
		else
			return $this->transport;	
	}	
	
	/*** === Décodage de la chaîne de transport === ***/
	public function transport_decode( $datas )
	{
		return unserialize( ( base64_decode( $datas ) ) );		
	}
	
	/*** === Encodage de la chaîne de transport === ***/
	public function transport_encode( $datas )
	{
		return base64_encode( serialize( $datas ) );		
	}
	
	/* = CALLBACKS = */	
	/** == Traitement de la requête == **/
	public function cb_handle_parse_request( &$parsed_request, $original_request )
	{
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