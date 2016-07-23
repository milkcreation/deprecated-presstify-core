<?php
Class tiFy_Forms_Buttons{
	/* = ARGUMENTS = */
	public	// Configuration
			$defaults = array(),
			
			// Paramètres
	
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */		
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
		
		// Déclaration des boutons natifs
		$this->register( 'submit', array( $this, 'submit' ) );
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){
		$this->defaults =  array(
			'submit' 	=> array(
				'label' 			=> __( 'Envoyer', 'tify' ), // Intitulé du bouton
				'before' 			=> '', // Code HTML insérer avant le bouton
				'after' 			=> '', // Code HTML insérer après le bouton
				'container_id'		=> '',
				'container_class'	=> '',
				'class'				=> '',
				'order'				=> 2
			)			
		);		
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init( ){}
	
	/** == == **/
	public function set_form(){
		if( ! $this->master->forms->current['buttons'] )
			$this->master->forms->current['buttons'] = $this->defaults;
	}
	
	/* = CONTROLEURS = */
	/** == Déclaration d'un bouton == **/
	public function register( $button_id, $callback ){
		if( array_keys( $this->master->registred_button, $button_id ) )
			return;
	
		if ( ! is_callable( $callback ) )
			return;
		
		$this->master->registred_button[$button_id] = $callback;
	}
	
	/* = AFFICHAGES = */
	/** == Affichage des boutons du fomulaire == **/
	public function display( $form = null ){
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		$this->master->callbacks->call( 'form_buttons_before_display', array( &$_form['buttons'], $this->master ) );
		
		$output = "";
		foreach( ( array ) $_form['buttons'] as $button_id => $args ) :
			if( ! in_array( $button_id, array_keys( $this->master->registred_button ) ) )
				continue;
			if( is_bool( $args ) && ! $args )
				return;
			$output .= $this->display_button( $button_id, $args, $_form );
		endforeach;
		
		return $output;
	}
	
	/** == Affichage d'un bouton == **/
	public function display_button( $button_id, $args = array(), $form = null ){
		if( ! isset( $this->master->registred_button[$button_id] ) )
			return;
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		if( is_callable( $this->master->registred_button[$button_id] ) )
			return call_user_func( $this->master->registred_button[$button_id], $_form, $args );
	}
	
	/** == Bouton de soumission == **/
	public function submit( $form = null, $args = array() ){				
		$class = ! empty( $args['class'] ) ? "submit button-submit {$form['prefix']}-submit ". $args['class'] : "submit button-submit {$form['prefix']}-submit";
		
		$output  = "";
		if( isset( $args['before'] ) )
			$output .= $args['before'];		
		$output .= "<div class=\"buttons-group submit-button\">\n";
		$output .= "<input type=\"hidden\" name=\"submit-{$form['prefix']}-{$form['ID']}\" value=\"submit\"/>";
		$output .= "\t<button type=\"submit\" id=\"submit-{$form['prefix']}-{$form['ID']}\" class=\"$class\" >\n";
		$output .= ! empty( $args['label'] ) ? $args['label'] : ( is_string( $args ) ? $args : __( 'Envoyer', 'tify' ) );
		$output .= "\t</button>\n";
		$output .= "</div>\n";
		if( isset( $args['after'] ) )
			$output .= $args['after'];
		
		return $output;
	}
}