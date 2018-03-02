<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\CookieTransport;

class CookieTransport extends \tiFy\Core\Forms\Addons\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 							= 'cookie_transport';
	
	// Options par défaut de formulaire
	public $default_form_options 		= array(
		'expire' 	=> MONTH_IN_SECONDS
	);
	
	// Options de champ de formulaire par défaut
	public $default_field_options 		= array( 
		'ignore' => false 
	);
	
	// Paramètres
	/// Préfix de cookie
	private $CookiePrefix					= 'tiFyFormsCookieTransport_';
	
	/// Données de cookie
	private $CookieDatas					= null;						
		
	/* = CONSTRUCTEUR = */				
	public function __construct()
	{		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_set_value'					=> array( $this, 'cb_field_set_value' ),
			'handle_parse_query_fields_vars'	=> array( $this, 'cb_handle_parse_query_fields_vars' )
		);
	
        parent::__construct();			
    }
				
	/* = COURT-CIRCUITAGE = */
	/** == Translation de la valeur du champ si elle existe dans le cookie == **/
	public function cb_field_set_value( &$value, $field )
	{		
		if( $this->getFieldAttr( $field, 'ignore' ) )
			return;					
			
		$value = $this->getCookieData( $field->getSlug(), $value );
	}	
	
	/** == Enregistrement du cookie == **/
	public function cb_handle_parse_query_fields_vars( &$fields_vars, $fields, $handleObj )
	{
		$datas = array();
		
		foreach( (array) $fields as $field ) :
			if( $this->getFieldAttr( $field, 'ignore' ) )
				continue;
			
			$datas[ $field->getSlug() ] = $fields_vars[ $field->getName() ];
		endforeach;
	
		$this->setCookie( $datas );
	}
	
	/* = CONTRÔLEURS = */
	/** == Récupération du nom du cookie == **/
	public function getCookieName()
	{
		return $this->CookiePrefix . $this->form()->getUID();
	}
	
	/** == Recupération de l'expiration du cookie == **/
	public function getCookieExpire()
	{
		return (int) $this->getFormAttr( 'expire', 0 );
	}
	
	/** == Création du cookie == **/
	private function setCookie( $datas = array() )
	{
		setcookie( $this->getCookieName(), base64_encode( serialize( ' ' ) ), time() - $this->getCookieExpire(), SITECOOKIEPATH );
		setcookie( $this->getCookieName(), base64_encode( serialize( $datas ) ), time() + $this->getCookieExpire(), SITECOOKIEPATH );
	}
	
	/** == Récupération de la valeur du cookie == **/
	private function getCookie()
	{
		if( is_null( $this->CookieDatas ) ) :
			if( isset( $_COOKIE[ $this->getCookieName() ] ) ) :
				return $this->CookieDatas = unserialize( base64_decode( $_COOKIE[ $this->getCookieName() ] ) );
			else :
				return $this->CookieDatas = array();
			endif;
		else:
			return $this->CookieDatas;
		endif;
	}
	
	/** == == **/
	private function getCookieData( $data, $default = '' )
	{
		if( ! $datas = $this->getCookie() )
			return $default;
		
		if( isset( $datas[ $data ] ) )
			return $datas[ $data ];
		
		return $default;
	}
}
