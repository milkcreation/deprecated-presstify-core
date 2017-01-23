<?php
namespace tiFy\Set\ContactToggle\Core\Control;

use tiFy\Lib\Modal\Modal;

class ContactToggle extends \tiFy\Core\Control\Factory
{
	/* = ARGUMENTS = */
	// Identifiant de la classe		
	protected $ID = 'contact_toggle';
	
	// Instance Courante
	static $Instance = 0;
	
	/* = INITIALISATION DE WORDPRESS = */
	public function init()
	{
		wp_register_script( 'tiFySetContactToggleControlContactToggle', static::getUrl( get_class() ) .'/ContactToggle.js', array( 'jquery' ), 170301, true );
	
		add_action( 'wp_ajax_tify_control_contact_toggle', array( $this, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_control_contact_toggle', array( $this, 'ajax' ) );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function enqueue_scripts()
	{
		wp_enqueue_script( 'tiFySetContactToggleControlContactToggle' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $echo = true )
	{
		self::$Instance++;
		
		$defaults = array(
			// Conteneur
			'id'				=> 'tify_control_contact_toggle-'. self::$Instance,
			'class'				=> '',	
			'type'				=> 'mail',	// mail | phone,
			'nonce'				=> 'tify_control_contact_toggle',
			'text'				=> __( 'Contacter', 'Theme' ),
			'query_args'		=> array(),
			'modal'				=> array()
		);		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$modal = wp_parse_args( 
			$modal,
			array(
				'id'				=> 'tiFyModal-tify_control_contact_toggle-'. self::$Instance,
				'target'			=> 'tify_control_contact_toggle-'. self::$Instance,
				'options'			=> array(
					'backdrop' 			=> true, // false | 'static'
					'keyboard'			=> true,
					'show'				=> false
				),
				'dialog'			=> array(
					'size'				=> null,
					'title'				=> __( 'Prendre contact', 'tify' ),
					'header_button'		=> true
				)
			)
		);	
		
		$query_args = htmlentities( json_encode( $query_args ) );
		
		$output  = "";
		$output .= "<a href=\"#\" class=\"tiFyControlContactToggle". ( $class? " ". (string) $class : '' ) ."\" data-tify_control=\"contact_toggle\" data-nonce=\"". wp_create_nonce( $nonce ) . "\" data-target=\"". $modal['target'] ."\" data-query_args=\"{$query_args}\">". $text ."</a>";
		Modal::display( $modal ); 
		
		if( $echo )
			echo $output;
	
		return $output;
	}
	
	/* = ACTION AJAX = */
	public function ajax()
	{
		check_ajax_referer( 'tify_control_contact_toggle' );
		
		$data = null; $query_args = $_POST['query_args'];
		
		if( $data = get_option( 'admin_email' ) ) :
			wp_send_json_success( $this->success( $data, $query_args ) );
		else :
			wp_send_json_error( $this->error( $query_args ) );
		endif;
	}
	
	/* = REPONSE = */
	/** == == **/
	protected function success( $data, $query_args = array() )
	{	
		return $data;
	}
	
	/** == == **/
	protected function error( $query_args = array() )
	{	
		return false;
	}
}