<?php
namespace tiFy\Components\DevTools\RestrictedAccess;

use tiFy\Environment\App;

class RestrictedAccess extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'template_redirect'
	);
	
	private $Options = array();
	
	/* = CONSTRUCTEUR = */
	public function __construct( $opts = array() )
	{
		parent::__construct();
		
		$this->Options = $opts;
	}
		
	/* = = */
	public function template_redirect()
	{
		// Vérification des habilitations d'accès au site
		if( $this->Allowed() )
			return;
		
		extract( $this->Options );
		
		wp_die( $message, $title, $http_code );	
	}
	
	/* = = */
	private function Allowed()
	{
		if( is_user_logged_in() )
			return true;
		
		return false;
	}
}