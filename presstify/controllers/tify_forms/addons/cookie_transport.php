<?php
/*
Addon Name: Cookie Transport
Addon ID: cookie_transport
Callback: tiFy_Forms_Addon_CookieTransport
Version: 1.150815
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

Class tiFy_Forms_Addon_CookieTransport extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			'expire' 	=> 30 * DAY_IN_SECONDS,
			'name'		=> 'tify_forms_cookie_transport_%s'
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( 
			'ignore' => false 
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_value' 				=> array( $this, 'cb_field_value' ),
			'handle_before_redirect'	=> array( 'function' => array( $this, 'cb_handle_before_redirect' ), 'order' => 9 )
		);
		
        parent::__construct( $master );			
    }
				
	/* = CALLBACKS = */
	/** == Translation de la valeur du champ si elle existe dans le cookie == **/
	public function cb_field_value( &$field_value, $field ){
		// Bypass
		if( $field['type'] == 'file' )
			return;
		
		$name = sprintf( $this->master->addons->get_form_option( 'name', 'cookie_transport' ), $this->master->functions->hash( $this->master->forms->get_prefix() .'_'. $this->master->forms->get_ID() ) );
		if( $datas = $this->cookie_get( $name ) )			
			if(  ! $this->master->addons->get_field_option( 'ignore', 'cookie_transport', $field ) && isset( $datas[ $field['slug'] ] ) )
				$field_value = $datas[ $field['slug'] ];
	}	
	
	/** == Enregistrement du cookie == **/
	public function cb_handle_before_redirect( $parsed_request, $original_request ){
		$datas 		= array();
		$options 	= $this->master->addons->get_options( 'cookie_transport' );
		$expire 	= $this->master->addons->get_form_option( 'expire', 'cookie_transport' );
		$name 		= sprintf( $this->master->addons->get_form_option( 'name', 'cookie_transport' ), $this->master->functions->hash( $this->master->forms->get_prefix() .'_'. $this->master->forms->get_ID() ) );
	
		foreach( (array) $parsed_request['values'] as $k => $r )
			if( $parsed_request['fields'][$k]['type'] == 'file' )
				continue;
			elseif( ! $parsed_request['fields'][$k]['add-ons']['cookie_transport']['ignore'] )
				$datas[$k] = $r;
		
		$this->cookie_set( $name, $datas, $expire );
	}
	
	/* = CONTRÔLEURS = */
	/** == Création du cookie == **/
	function cookie_set( $name, $datas, $expire ){
		setcookie( $name, base64_encode( serialize( ' ' ) ), time() - $expire, SITECOOKIEPATH );
		setcookie( $name, base64_encode( serialize( $datas ) ), time() + $expire, SITECOOKIEPATH );
	}
	
	/** == Récupération de la valeur du cookie == **/
	function cookie_get( $name, $type = 'datas' /* | raw */ ){
		if( ! isset( $_COOKIE[ $name ] ) )
			return;
		switch( $type ) :
			default :
				return unserialize( base64_decode( $_COOKIE[ $name ] ) );
				break;
			case 'raw' :
				return ;
				break;
		endswitch;
	}
}