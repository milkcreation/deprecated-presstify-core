<?php
namespace tiFy\Lib\Facebook;

use tiFy\Lib\Facebook\JSInit\JSInit;

class Facebook
{
	/* = ARGUMENTS = */
	private $AppId				= null;
	private $AppSecret			= '';
	private $GraphVersion 		= 'v2.4';
	private $SDK;
	
	// Tailles d'image optimal de l'Opengraph FB
	public static $OGImageSizes	= array(
		/* Optimal */ array( 1200, 630 ), /* Moyenne */ array( 600, 315 ), /* Minimal */ array( 200, 200 )	
	);
	
	
	/* = CONSTRUCTEUR = */
	public function __construct( $app_id = null, $app_secret = '', $graph_version = 'v2.4', $args = array() )
	{
		$this->AppId 		= $app_id;
		$this->AppSecret 	= $app_secret;
		$this->GraphVersion	= $graph_version;		
	}
	
	/* = = */
	/** == == **/
	public function PHPInit()
	{
		// Instanciation du SDK PHP
		session_start();

		$this->SDK = new \Facebook\Facebook(
			array(
		  		'app_id' 				=> $this->app_id,
		  		'app_secret' 			=> $this->app_secret,
		  		'default_graph_version' => $this->default_graph_version
		  	)
		);
	}
	
	/** == == **/
	public function JSInit( $options = array() )
	{
		new JSInit( $this->AppId, $this->GraphVersion, $options );
	}
}