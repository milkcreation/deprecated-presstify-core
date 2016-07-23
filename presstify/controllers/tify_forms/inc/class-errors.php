<?php
Class tiFy_Forms_Errors{
	/* = ARGUMENTS = */
	public	// Configuration
			
			// Paramètres
			$last_error,
			$errors,
			$showed = 0,
			
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
	private function config(){}	
	
	/* = PARAMETRAGES = */
	
	/* = FONCTIONS = */
	/**
	 * Vérification de l'existance d'erreur pour un formulaire
	 * 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return boolean true|false Résultat du test d'existance
	 */
	public function has( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;

		return ! empty( $this->errors[ $_form['ID'] ] );
	}
	 
	/**
	 * Récupération des erreurs d'un formulaire
	 * 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Tableau dimensionné de la liste des erreurs pour un formulaire
	 */ 
	public function get( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		if( isset( $this->errors[ $_form['ID'] ] ) )
			return $this->errors[ $_form['ID'] ];		
	}
	
	/**
	 * Compte les erreurs d'un formulaire
	 * 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return int Nombre d'erreur pour un formulaire
	 */
	 public function count( $form = null ){
		return count( $this->get( $form ) );		
	}
	
	/**
	 * Affichage des erreurs d'un formulaire
	 * 
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return HTML Affichage de la liste des erreurs
	 */ 
	public function display( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		if( ! $errors = $this->get( $_form ) )
			return;
				
		$error_opts = $this->master->forms->get_option( 'errors', $_form );
		
		$output  = "";
		if( $error_opts['show'] ) :
			// Affichage du titre
			if( $error_opts['title'] )
				$output .= "<h3>".$error_opts['title']."</h3>";			
			$count = $this->count( $_form );
			$output .= "<ol>\n";			
			foreach( $errors as $key => $_errors ) :				
				foreach( $_errors as $error ) :
					if( ( $error_opts['show'] > 0 ) && ( $error_opts['show'] <= $this->showed ) )
						break 2;
					$output .= "\t<li>".$error."</li>";
					$this->showed++;
				endforeach;
			endforeach;	
			$output .= "</ol>\n";
			
			// Affichage du teaser
			if( ( $error_opts['show'] > 0 ) && ( $this->showed < $count ) && $error_opts['teaser'] ) :
				$output .= "<p>". $error_opts['teaser'] ."</p>";
			endif;
		else :
			$this->showed = $this->count( $_form );
		endif;
		
		return $output;			
	}
	
	/**
	 * Définition des messages d'erreurs pour un champ
	 * 
	 * @param array $errors (requis) Tableau indexé des erreurs
	 * @param array $field (requis) Tableau dimensionné d'un champ de formulaire
	 */
	public function field_set( $errors, $field ){		
		$this->errors[ $field['form_id'] ][ $field['slug'] ] = $errors;
	}

	/**
	 * Vérification de l'existance d'erreur pour un formulaire
	 * 
	 * @param array $field (requis) Tableau dimensionné d'un champ de formulaire
	 * 
	 * @return boolean true|false Résultat du test d'existance
	 */
	public function field_has( $field ){
		if( isset( $this->errors[ $field['form_id'] ][ $field['slug'] ] ) )				
			return !empty( $this->errors[ $field['form_id'] ][ $field['slug'] ] );
	}
	
	/**
	 * Récupération des erreurs 
	 * 
	 * @param array $field (requis) Tableau dimensionné d'un champ de formulaire
	 * 
	 * @return array Tableau indexéde la liste des erreurs pour un formulaire
	 */ 
	public function field_get( $field ){
		if( isset( $this->errors[ $field['form_id'] ][ $field['slug'] ] ) )
			return $this->errors[ $field['form_id'] ][ $field['slug'] ];		
	}
	
	/**
	 * Affichage des erreurs d'un formulaire
	 * 
	 * @param array $field (requis) Tableau dimensionné d'un champ de formulaire
	 * 
	 * @return HTML Affichage de la liste des erreurs
	 */ 
	public function field_display( $field ){
		if( ! $errors = $this->field_get( $field ) )
			return;
				
		$error_opts = $this->master->forms->get_option( 'errors', $field['form_id'] );
		
		$output  = "";
		$output .= "<ul>\n";
		foreach( $errors as $key => $error )				
			$output .= "\t<li>".$error."</li>";	
		$output .= "</ul>\n";
		
		return $output;			
	}	
}