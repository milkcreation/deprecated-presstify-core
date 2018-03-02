<?php
namespace tiFy\Core\Formss;

class Steps
{
	/* = ARGUMENTS = */
	public	// Configuration
			
			// Paramètres
			$referer	= array(),			
			$current	= array(),
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( $master )
	{
		// Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();
		
		// Déclaration des boutons de contrôle
		$this->master->buttons->register( 'step', array( $this, 'display_buttons' ) );
		
		// Callbacks
		$this->master->callbacks->core_set( 'form_set_current', 'steps', array( $this, 'cb_form_set_current' ), 1 );
		$this->master->callbacks->core_set( 'form_hidden_fields', 'steps', array( $this, 'cb_form_hidden_fields' ) );
		$this->master->callbacks->core_set( 'handle_parse_submit', 'steps', array( $this, 'cb_handle_parse_submit' ) );
		$this->master->callbacks->core_set( 'handle_parse_request', 'steps', array( $this, 'cb_handle_parse_request' ) );			
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){}
		
	/* = CONTROLEURS = */
	/** == Initialisation de l'étape courante ==   
	 * @return int étape courante 
	 */	
	public function init( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		if( ! empty( $this->current[$_form['ID']] ) ) :
			$current = $this->current[$_form['ID']];
		elseif( isset( $_REQUEST[ 'step-'.$_form['prefix'].'-'.$_form['ID']] ) ) :			
			$current = (int) $_REQUEST[ 'step-'.$_form['prefix'].'-'.$_form['ID']];
			$this->referer[$_form['ID']] = $current;
		else :
			$current = (int) 1;
		endif;
		
		$this->set( $current );
		
		return $current;
	}	
	
	/** == Définition de l'étape courante du formulaire courant ==  
	 * @return int étape courante 
	 */	
	public function set( $step ){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;
		
		$this->current[$_form['ID']] = $step;		
		
		$this->form_datas();
		
		return $step;
	}
	
	/** == Récupération de l'étape courante du formulaire courant ==   
	 * @return int étape courante 
	 */	
	public function get(){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;
		
		if( ! empty( $this->current[$_form['ID']] ) )
			return (int) $this->current[$_form['ID']];
	}
	
	/** == Passage à l'étape suivante du formulaire courant ==  
	 * @return int étape courante 
	 */	
	public function next(){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;		
		if( ! $max = (int) $this->master->forms->get_option( 'step' ) )
			return;		
		if( ! $step = $this->get() )
			return;	
		if( ( ++$step > $max ) )
			return;
		
		$step = $this->set( $step );
		$this->master->forms->set_current( $_form );

		return $step;
	}
	
	/** == Retour à l'étape précédente du formulaire courant ==  
	 * @return int étape courante 
	 */	
	public function prev( ){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;		
		if( ! $max = (int) $this->master->forms->get_option( 'step' ) )
			return;				
		if( ! $step = $this->get() )
			return;		
		if( ( --$step <= 0 ) )
			return;		
		
		$step = $this->set( $step );		
		$this->master->forms->set_current( $_form );

		return $step;
	}
	
	/** == Récupération de l'étape d'origine ==  
	 * @return int étape courante 
	 */	
	public function get_referer( ){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;		
		
		return ( isset( $this->referer[$_form['ID']] ) ) ? $this->referer[$_form['ID']] : null;
	}
	
	/** == Définition des données de formulaire selon l'étape == **/
	public function form_datas(){
		// Bypass
		if( ! $_form = $this->master->forms->get_current() )
			return;

		$this->master->forms->current['_fields'] = array();
		if( ! $this->master->forms->get_option( 'step' ) ) :
			$this->master->forms->current['_fields'] = $_form['fields'];
		else :
			foreach( ( array ) $this->master->forms->current['fields'] as $f )
				if( $f['step'] == $this->current[$_form['ID']] )
					array_push( $this->master->forms->current['_fields'], $f );
		endif;
		$this->master->forms->update();
	}
	
	/* = AFFICHAGES = */
	/** == Retour à l'étape précédente == **/
	public function display_buttons( $form = null, $args = array() ){
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		$output  = ""; 
		$output .= "<div class=\"buttons-group step-buttons\">\n";
		// Bouton de retour à l'étape précédente
		if( $this->get() > 1 ) :
			$output .= "\t<button type=\"submit\" id=\"step_backward-{$_form['prefix']}-{$_form['ID']}\" class=\"backward button-backward\" name=\"submit-{$_form['prefix']}-{$_form['ID']}\" value=\"backward\">\n";
			$output .= ! empty( $args['label'] ) ? $args['label'] : ( is_string( $args ) ? $args : __( 'Précédent', 'tify' ) );
			$output .= "\t</button>\n";
		endif;
		$output .= "</div>\n";
		
		return $output;
	}
	
	/* = CALLBACKS = */
	/** == == **/
	public function cb_form_set_current( &$current, $_form ){
		$this->init();
	}
	
	/** == == **/
	public function cb_form_hidden_fields( &$output, $form ){
		$slug = $form['prefix']."-".$form['ID'];
		
		$output .= "\n\t\t<input type=\"hidden\" name=\"step-$slug\" value=\"". esc_attr( $this->master->steps->get() ) ."\">";
	}
	
	/** == == **/
	public function cb_handle_parse_submit( &$continue, $submit ){
		// Bypass
	 	if( ! $_form = $this->master->forms->get_current() )
			return;
		if( $submit !== 'backward' )
			return;

		$this->prev( );
		$continue = false;
	}
	
	/** == Traitement de la requête == **/
	public function cb_handle_parse_request( &$parsed_request, $original_request ){
		// Bypass
	 	if( ! $_form = $this->master->forms->get_current() )
			return;
		
		$parsed_request['step'] = $original_request[ 'step-'.$_form['prefix'].'-'.$_form['ID']];
	}
}