<?php
Class tiFy_Forms_Forms{
	/* = ARGUMENTS = */
	public	// Configuration
	 		$default_attrs, 	// Attributs optionnels par défaut des formulaires
		
		 	// Paramètres
			$forms,				// Liste des formulaires 
			$current, 			// Formulaire courant
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Initialisation de la configuration
		$this->config();				
    }
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	private function config(){
		// Définition des attributs optionnels par défaut
		$this->default_attrs = array(
			'method'				=> 'post',
			'container_id' 			=> '%s',		/** @todo **/
			'container_class' 		=> '',
			'form_id' 				=> '%s',		/** @todo **/
			'form_class' 			=> '%s',
			'before' 				=> '',
			'after' 				=> '',
			'fields' 				=> array(),
			'buttons' 				=> array(),
			'add-ons'				=> array(),
			'options' 				=> array()			
		);
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation == */
	public function init( ){
		$this->preset_forms( $this->master->registred_form );
		$this->set_forms( $this->master->registred_form );
	}
	
	/** == Prédéfinition des champs de formulaire ==  
	 * @param $forms (requis) Tableau indexé des formulaires
	 */
	private function preset_forms( &$forms ){
		foreach( $forms as &$form ) :
			// Attributs requis
			$instance = uniqid();
			$defaults = array(
				'ID' 		=> $instance,				
				'prefix' 	=> sprintf( 'tify_form-%s', $instance )
			);
			$defaults += $this->default_attrs;			
			$form = $this->master->functions->parse_options( $form, $defaults );
			// Stockage du formulaire
			$this->forms[ $form['ID'] ] = $form;
		endforeach;
	}	
	
	/** == Définition des formulaires ==  
	 * @param array $forms Tableau indexé des formulaires
	 * @see $this->set_form pour connaître la syntaxe d'un formulaire 
	 */
	public function set_forms( $forms = array() ){
		// Bypass	
		if( empty( $forms ) )
			return;
	
		foreach( $forms as $form )						
			$this->set_form( $form );				
	}
	
	/** == Définition d'un formulaire ==
	 * @param array $form Tableau dimensionné d'un formulaire et ses attributs
	 */
	public function set_form( $form = array() ){		
		$this->set_current( $form );		
		// Initialisation des boutons
		$this->master->buttons->set_form();
		// Initialisation des add-ons
		$this->master->addons->set_form();
		// Initialisation des options du formulaire
		$this->init_options( $form );
		// Initialisation des champs de formulaire
		$this->master->fields->init();	
		// Mise à jour des attributs des données de formulaire avec les données du formulaire courant
		$this->update();		
		// Réinitialisation du formulaire courant
		$this->reset_current();	
	}
	
	/** == Initialisation des options de formulaire == **/
	 public function init_options( $form = null ){
		// Attributs par défaut des options de formulaires
		$defaults = array(
			// Affichage des notifications (erreur et succès) dans le formulaire
			'notifications'	=>  true,
			// Erreurs
			'errors' 	=> array(
				'title' 	=> false,   //__( 'Le formulaire contient des erreurs :', 'tify' ), // Intitulé de la liste des erreurs. Mettre à false pour masquer 
				'show'		=> -1, 		// Affichage des erreurs. -1 : Toutes | 0 : Aucune | n : Nombre maximum à afficher
				'teaser' 	=> '...', 	// Affiché seulement si toutes les erreurs ne sont pas visible. Mettre à false pour masquer 
				'field'		=> false 	// Affiche les erreurs relative à chaque champs
			),
			// Succès	
			'success'	=> array(
				'message'	=> __( 'Votre demande a bien été prise en compte et sera traitée dès que possible', 'tify' ),
				'display' 	=> false // Défaut false | Affichage du formulaire 'form' | Affichage du récapitulatif 'summary'
			),
			'anchor' 	=> "tify_form_container_". $form['ID'],
			'enctype'	=> false, 		// Attributs du formulaire en cas de présence de champs type fichier
			'step'		=> 0,			// Gestion de formulaires par étape
			'preview'	=> false 		// Affiche un résumé des soummissions au formulaire avant le traitement définitif		
		);
		$this->current['options'] = $this->master->functions->parse_options( $this->current['options'], $defaults );
		// Post traitement de la définition des options de formulaire
		$this->master->callbacks->call( 'form_set_options', array( &$this->current['options'], $this->master ) );
	}
	
	/* = CONTROLEURS = */
	/** == GLOBAL == **/
	/*** === Récupération de la liste complète des formulaires déclarés === 
	 * @return array|null Tableau indexé de la liste complète des formulaires déclarés
	 ***/
	public function get_list(){
		return $this->forms;
	}
	
	/*** === Récupération d'un formulaire ===
	 * @param int|object|null (requis) $form ID ou objet formulaire
	 * @return array Un tableau dimensionné d'un formulaire et de ses attributs
	 ***/
	public function get( $form = null ){
		if( ! $form )
			return $this->get_current();
		elseif( is_array( $form ) && isset( $form['ID'] ) )
			return $this->forms[ $form['ID'] ];
		elseif( isset( $this->forms[$form] ) )
			return $this->forms[$form];
	}	
	
	/** == FORMULAIRE COURANT == **/
	/*** === Récupération du formulaire courant ===  
	 * @return array|null Le formulaire déclaré courant
	 ***/
	public function get_current(){
		return $this->current;
	}
	
	/*** === Récupération du formulaire courant ===  
	 * @return array|null Le formulaire déclaré courant
	 ***/
	public function get_current_id(){
		if( isset( $this->current['ID'] ) )
			return $this->current['ID'];
	}
	
	
	/*** === Définition du formulaire courant ===  
	 * @param int|object (requis) $form ID ou objet formulaire.
	 * @return array Tableau dimensionné du formulaire courant
	 ***/
	public function set_current( $form ){
		$this->reset_current();
		// Bypass	
		if( ! $_form = $this->get( $form ) )
			return;	
		
		$this->current = $_form;		
		$this->master->callbacks->call( 'form_set_current', array( &$this->current, $_form, $this->master ) );		
				
		return $this->current;
	}
		
	/*** === Réinitialisation du formulaire courant === ***/
	public function reset_current( ){
		$this->current = null;
	}
				
	/** == ATTRIBUTS DE FORMULAIRE == **/
	/*** === Récupération d'un attribut de formulaire === 
	 * @param string $attr Attribut du formulaire à récupérer. Par défaut, préfixe du formulaire 
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant	 
	 * @return mixed La valeur de l'attribut requis pour un formulaire donné
	 ***/
	public function get_attr( $attr = 'ID', $form = null ){
		// Bypass
		if( ! $_form = $this->get( $form ) )
			return;
		
		if( isset( $_form[$attr] ) )
			return $_form[$attr];
	}
	
	/*** === Mise à jour des attributs d'un formulaire === 
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 ***/
	public function update( $form = null ){
		if( ! $form )
			$form = $this->get_current();

		if( isset( $form['ID'] ) )
			$this->forms[ $form['ID'] ] = $form;
	}
	
	/*** === Récupération de l'ID du formulaire ===
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 * @return mixed La valeur du prefixe pour le formulaire requis 
	 ***/
	public function get_ID( $form = null ){
		return $this->get_attr( );
	}
	
	/*** === Récupération du prefixe du formulaire === 
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 * @return mixed La valeur du prefixe pour le formulaire requis 
	 ***/
	public function get_prefix( $form = null ){
		return $this->get_attr( 'prefix', $form );
	}
	
	/*** === Récupération du titre du formulaire ===  
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant 
	 * @return mixed La valeur du titre pour le formulaire requis 
	 ***/
	public function get_title( $form = null ){
		return $this->get_attr( 'title', $form );
	}
	
	/** == OPTIONS DE FORMULAIRE == **/
	/*** === Récupération des options d'un formulaire === 
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 * @return array Tableau dimensionné des options 
	 ***/
	public function get_options( $form = null ){
		// Bypass
		if( ! $_form = $this->get( $form ) )
			return;
		
		if( isset( $_form['options'] ) )
			return $_form['options'];
	}
	
	/*** === Récupération d'une option de formulaire ===  
	 * @param string $option (requis) Attribut de l'option requise 
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 * @return mixed Valeur de l'option requise 
	 ***/
	public function get_option( $option, $form = null ){
		// Bypass
		if( ! $options = $this->get_options( $form ) )
			return;

		if( isset( $options[ $option ] ) )
			return $options[ $option ];
	}
	
	/*** === Définition d'une option de formulaire ===
	 * @param string $key (requis) Attribut de l'option
	 * @param string $value (requis) Valeur de l'option
	 * @param int|object|null $form ID ou objet formulaire. null correspond au formulaire courant
	 * @return mixed Valeur de l'option requise 
	 ***/
	public function set_option( $key, $value, $form = null ){
		// Bypass
		if( ! $_form = $this->get( $form ) )
			return;	
		$this->forms[ $_form['ID'] ]['options'][$key] = $value;
		if( $this->current['ID'] == $_form['ID'] )
			$this->current['options'][$key] = $value; 
	}	
	
	/** == AFFICHAGE == **/		
	/*** === Affichage d'un formulaire ===
	 * @param int|object $form ID ou objet formulaire. requis
	 * @param array $args Options d'affichage du formulaire 
	 * @return HTML Affiche ou retourne le formulaire requis
	 ***/
	public function display( $form, $echo = false )
	{
		// Bypass et Initialisation de l'élément courant
		if( ! $_form = $this->set_current( $form ) )
			return;
		
		// Fonction de court-circuitage des attributs de formulaire post-affichage
		$this->master->callbacks->call( 'form_before_display', array( &$_form, $this->master ) );
		
		// Génération de la sortie HTML du formulaire
		$output = "";		
		$this->master->callbacks->call( 'form_before_output_display', array( &$output, $_form, $this->master ) );		

		$output .= "\n<div id=\"tify_form_container_{$_form['ID']}\" class=\"tify_form_container". ( $_form['container_class'] ? ' '. $_form['container_class'] : '' ) ."\">";
		
		// Message en cas de succès de soumission du formulaire
		$success = $this->get_option( 'success' );
		
		if( $this->get_option( 'notifications' ) ) :
			$output .= "\n\t<div class=\"success notification\" style=\"display:". ( $this->master->handle->check_success() ? 'inherit' : 'none' ) ."\">";
			$output .= "\n\t\t<p class=\"core_message\">";
			
			$output .= ( ( $cache = $this->master->datas->transient_get() ) && ! empty( $cache['success']['message'] ) ) ? $cache['success']['message'] : $success['message'];		 
			$output .= "\n\t\t</p>";
			$output .= "\n\t</div>";
		endif;
			
		// Pré-affichage HTML
		$output .= $_form['before'];
		
		// Définition de l'action
		$action  = "";
		$action .= $this->get_option( 'anchor' ) ? '#'. $this->get_option( 'anchor' ) : '';
		$this->master->callbacks->call( 'form_parse_action', array( &$action, $_form, $this->master ) );
		
		// Balise d'ouverture du formulaire
		
		if( ! $this->master->handle->check_success() || ( $success['display'] === 'form' ) ) :
			$output .= "\n\t<form method=\"{$_form['method']}\" id=\"tify_form_{$_form['ID']}\" class=\"".sprintf( $_form['form_class'], "tify_form" )."\" action=\"{$action}\"";
			if( $this->get_option( 'enctype' ) )
				$output .= " enctype=\"multipart/form-data\"";
			$output .= "\">";		
			
			// Champs cachés requis 
			$output .= $this->hidden_fields( $_form );
			
			// Affichage des erreurs
			$error = $this->master->errors->has();
			if( $this->get_option( 'notifications' ) ) :
				$output .= "\n\t\t<div class=\"error notification\" style=\"display:".( ( $error )? 'inherit' : 'none' )."\" >";
				$output .= $this->master->errors->display();
				$output .= "\n\t\t</div>";
			endif;
		
			// Affichage des champs de formulaire
			if( $this->master->fields->get_fields_displayed() )
				$output .= "\n\t\t<div class=\"fields-wrapper\">";
			$current_group = false;	
	
			foreach( (array) $this->master->fields->get_fields_displayed() as $field ) :
				if( $field['group'] && $current_group != $field['group'] ) :
					if( $current_group )
						$output .= "\n\t\t\t</div>";
					$current_group = $field['group'];
					$output .= "\n\t\t\t<div class=\"fields-wrapper-group fields-wrapper-group-{$field['group']} fields-wrapper-order-{$field['order']}\">";
				endif;
				$output .= $this->master->fields->display( $field );
			endforeach;
			if( $current_group )
				$output .= "\n\t\t\t</div>";
			
			if( $this->master->fields->get_fields_displayed() )
				$output .= "\n\t\t</div>";
			
			// Affichage des boutons
			$buttons = $this->master->buttons->display( $_form );
			$this->master->callbacks->call( 'form_buttons_display', array( &$buttons, $this->master ) );		
			$output .= "\n\t\t<div class=\"buttons-wrapper\">";				
			$output .= $buttons;		
			$output .= "\n\t\t</div>";
			
			// Balise de fermeture du formulaire	
			$output .= "\n\t</form>";
		endif;
		
		// Post-affichage HTML
		$output .= $_form['after'];		
		$output .= "\n</div>";
			
		$this->master->callbacks->call( 'form_after_output_display', array( &$output, $_form, $this->master ) );	

		// Fonction de court-circuitage de l'affichage du formulaire
		$this->master->callbacks->call( 'form_output_display', array( &$output, $_form, $this->master ) );
		
		// Réinitialisation de l'élément courant
		$this->reset_current();
		
		if( $echo )
			echo $output;
		else 
			return $output;	
	}

	/*** === Champs cachés de soumission de formulaire === ***/
	function hidden_fields( $form = null ){
		if( ! $_form = $this->get( $form ) )
			return;
		// Definition de l'identifiant de formulaire
		$slug = $_form['prefix']."-".$_form['ID'];	

		$output  = "";
		$output .= "\n\t\t<input type=\"hidden\" name=\"{$_form['prefix']}-form_id\" value=\"". esc_attr( $_form['ID'] ) ."\">";		
		$output .= wp_nonce_field( 'submit_'.$slug, '_'.$_form['prefix'].'_nonce', true, false );
		
		
		$success = $this->get_option( 'success' );
		if( $this->master->handle->check_success() && ( $success['display'] !== 'form' ) ) :
			$output .= "\n\t\t<input type=\"hidden\" name=\"tify_forms_results-". esc_attr( $_form['ID'] ) ."\" value=\"". $this->master->datas->session_get() ."\">";
		endif;
		
		$this->master->callbacks->call( 'form_hidden_fields', array( &$output, $_form, $this->master ) );
		
		return $output;
	}
}