<?php
namespace tiFy\Components\DevTools\Tools\RestrictedAccess;

use tiFy\App\Factory;

class RestrictedAccess extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'template_redirect'
	);
	
	private $Options = array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( $opts = array() )
	{
		parent::__construct();
		
		$this->Options = wp_parse_args( 
			\tiFy\Components\DevTools\DevTools::getConfig( 'restricted_access' ),
			\tiFy\Components\DevTools\DevTools::getDefaultConfig( 'restricted_access' )
		);
		\tiFy\Components\DevTools\DevTools::setConfig(  'restricted_access', $this->Options );
	}
		
	/* = = */
	public function template_redirect()
	{
		// Vérification des habilitations d'accès au site
		if( ! $this->Active() )
			return;
		
		// Vérification des habilitations d'accès au site
		if( $this->Allowed() )
			return;
		
		extract( $this->Options );
		
		wp_die( $message, $title, $http_code );	
	}
	
	/* = = */
	private function Active()
	{
		return $this->Options['active'];
	}
	
	/* = = */
	private function Allowed()
	{
		if( is_user_logged_in() )
			return true;
		
		return false;
	}
}