<?php
namespace tiFy\Core\Taboox\Options\CustomHeader\Admin;

class CustomHeader extends \tiFy\Core\Taboox\Options\Admin
{
	/* = ARGUMENTS = */
	private $option_name = 'custom_header';
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		\register_setting( $this->page, $this->option_name );		
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_media();
		wp_enqueue_style( 'tify_taboox_custom_header', self::tFyAppUrl() . '/admin.css', array( 'tify_control-media_image' ), '150325' );
		wp_enqueue_script( 'tify_taboox_custom_header', self::tFyAppUrl() . '/admin.js', array( 'jquery', 'tify_control-media_image' ), '150325', true );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form()
	{
		$this->args['media_library_title'] 	= __( 'Personnalisation de l\'image d\'entête', 'tify' );
		$this->args['media_library_button']	= __( 'Utiliser comme image d\'entête', 'tify' );
		$this->args['name'] 				= $this->option_name;
		$this->args['value'] 				= get_option( $this->option_name, true );
				
		tify_control_media_image( $this->args );
	}
}