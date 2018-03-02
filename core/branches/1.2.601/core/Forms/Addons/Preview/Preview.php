<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\Preview;

class Preview extends \tiFy\Core\Forms\Addons\Factory
{
	/* = ARGUMENTS = */
	public	// Configuration
			
			// Paramétrage
			$preview_step = 0;
	
	/* = CONSTRUCTEUR = */				
	public function __construct()
	{
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			'display_callback'		=> '__return_false', // Fonction de rappel d'affichage de la prévisualisation. Arguments passés : $parsed_request['values'], $tify_form_class
			'preview_form_options'	=> array(),			 // Options de formulaire de la page de preview (à tester)
			'preview_form_buttons'	=> array()			 // Boutons de formulaire de la page de preview
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( );
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'form_set_options'				=> array( $this, 'cb_form_set_options' ),
			'form_set_current'				=> array( $this, 'cb_form_set_current' ),			
			'form_before_display'			=> array( $this, 'cb_form_before_display' ),
			'form_before_output_display'	=> array( $this, 'cb_form_before_output_display' ),
			'form_buttons_before_display'	=> array( 'function' => array( $this, 'cb_form_buttons_before_display' ), 'order' => 1 )
		);
		
        parent::__construct();
		
		// Actions et Filtres Wordpress
		add_filter( 'query_vars', array( $this, 'wp_query_vars' ), 1 );			
    }
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	public function wp_query_vars( $vars ){
		$vars[] = 'tify_forms_preview';
		
		return $vars;
	}
				
	/* = CALLBACKS = */
	/** == == **/
	public function cb_form_set_options( &$options ){
		$step = ( ! empty( $options['step'] ) ) ? (int) $options['step']++ : 2;	
		
		$this->preview_step = $step;
		
		if( $this->master->steps->get() === $this->preview_step )
			$this->master->functions->parse_options( $this->get_form_option( 'preview_form_options' ), $options );
		
		$options['step'] = $step;
	}
	
	/** == == **/
	public function cb_form_set_current( &$current, $form ){
		if( $this->master->steps->get() === $this->preview_step ) :
			set_query_var( 'tify_forms_preview', $this->preview_step );		
		else :
			set_query_var( 'tify_forms_preview', 0 );
		endif;
	}	
	
	/** == == **/
	public function cb_form_before_display( &$_form ){
		if( $this->master->steps->get() !== $this->preview_step )
			return;
		if( $buttons = $this->get_form_option( 'preview_form_buttons' ) ) 
				$_form['buttons'] = $buttons;
		
		$_form['container_class'] .= ' tify_form_preview';
	}
	
	/** == == **/
	public function cb_form_before_output_display( &$output ){
		// Bypass
		if( $this->master->steps->get() !== $this->preview_step )
			return;
		
		$callback = $this->get_form_option( 'display_callback' );
		if( ! is_callable( $callback ) )
			return;
		
		$output .= call_user_func_array( $callback, array( $this->master->handle->parsed_request['values'], $this->master ) );
	}
	
	/** == == **/
	public function cb_form_buttons_before_display( &$buttons ){
		if( $this->master->steps->get() !== $this->preview_step )
			return $buttons;
		if( $_buttons = $this->get_form_option( 'preview_form_buttons' ) ) 
				$buttons = $_buttons;
	}
}