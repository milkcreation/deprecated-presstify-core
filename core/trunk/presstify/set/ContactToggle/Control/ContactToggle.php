<?php
namespace tiFy\Set\ContactToggle\Control\ContactToggle;

class ContactToggle extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */	
	// Identifiant de la classe		
	protected $ID = 'contact_toggle';
	
	// Instance Courante
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_script( 'tiFy\Set\ContactToggle\Control\ContactToggle', self::getUrl() .'/ContactToggle.js', array( 'jquery' ), 170301, true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_script( 'tiFy\Set\ContactToggle\Control\ContactToggle' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $echo = true )
	{
		self::$Instance++;
		
		$defaults = array(
			// Conteneur
			'id'				=> 'tify_control_contact_toggle-'. self::$Instance,
			'class'				=> '',	
			'type'				=> 'mail',	// mail | phone
		);		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Selecteur de traitement
		$output  = "";		
		
		if( $echo )
			echo $output;
	
		return $output;
	}
}