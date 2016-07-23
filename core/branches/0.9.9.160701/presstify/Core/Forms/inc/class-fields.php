<?php
Class tiFy_Forms_Fields{
	/* = ARGUMENTS = */
	public	// Configuration
			$default_attrs,
			
			// Paramètres			
			
			// Références
			$master;
	
	/* = CONSTRUCTEUR = */		
	public function __construct( tiFy_Forms $master ) {
        // Définition du contrôleur principal
        $this->master = $master;
		
		// Définition des attributs par défaut
		 $this->config();	
    }	
	
	/* = CONFIGURATION = */
	/** == Initialisation == **/
	public function config(){
		// Définition des attributs par défaut
		$this->default_attrs = array(			
			// Classes			
			'container_class' 	=> '%s',
			'label_class' 		=> '%s',
			'field_class' 		=> '%s',
			'group'				=> 0,			
			'order'				=> 0,
			// Attributs label
			'label'				=> true,
			// Attributs du champ de saisie
			'type'				=> 'text',	
			'name'				=> '%1$s[%2$s][%3$s]',				
			'tabindex'			=> 0,
			'readonly'			=> false,				
			'value'				=> '',
			'transport'			=> true,	
			'choices'			=> array(),
			'choice_none' 		=> '',
			'choice_all' 		=> '',			
			'integrity_cb' 		=> false, 	// string | array( 'function' => [function_name], 'args' => array( $arg1, $arg2, ... ), 'error' => 'message d'erreur personnalisé' )
			'autocomplete'		=> 'on',
			'onpaste'			=> true, 	// Autoriser le copier/coller
			'before'			=> '',		// Contenu HTML affiché avant le champs
			'after'				=> '',		// Contenu HTML affiché après le champs
			// Attributs HTML5 du champ de saisie
			'placeholder'		=> '',
			'required'			=> false,  // bool | string : Message d'erreur personnalisé /** @todo array( 'tagged' => true, 'check' => true, html5 => true, 'error' => 'message d'erreur perso' ); **/
			'pattern'			=> false,
			// Addons et options
			'step'				=> 1,
			'add-ons'			=> array(),
			'options'			=> array(),
			'echo'				=> false
		);
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation == **/
	public function init(){		
		$this->set_fields( $this->master->forms->current['fields'] );

		// Tri des champs
		$positions = array(); $groups = array(); $position_order = array(); $group_order = array();	
		/// Définition des valeurs maximum
		foreach ( (array) $this->master->forms->current['fields'] as $params ) :
			$positions[] = $params['order']; $groups[] = $params['group'];
		endforeach;
		$position_max = max( $positions );  $group_max = max( $groups );
		
		foreach ( (array) $this->master->forms->current['fields'] as $key => $params ) :
			if( ! $params['order'] ) $this->master->forms->current['fields'][$key]['order'] = ++$position_max;
			if( ! $params['group'] ) $this->master->forms->current['fields'][$key]['group'] = $group_max+1;
			$position_order[$key] = $this->master->forms->current['fields'][$key]['order']; 
			$group_order[$key] = $this->master->forms->current['fields'][$key]['group'];
		endforeach;			
		@array_multisort( $group_order, $position_order, $this->master->forms->current['fields'] );	
	}	

	/**
	 * Définition des tous les champs d'un formulaire
	 * 
	 * @param array $fields Tableau indexé des champs de formulaire
	 * @see $this->set_field pour connaître la syntaxe des champs de formulaire 
	 */
	public function set_fields( &$fields = array() ){
		foreach( $fields as $index => &$field ) :
			$field['index'] = $index;
			$field = $this->set_field( $field );	
		endforeach;
	}
	
	/**
	 * Définition d'un champ de formulaire
	 * 
	 * @param array $field Tableau dimensionné d'un champ de formulaire
	 */
	public function set_field( $field = array() ){				
		$field = $this->master->functions->parse_options( $field, $this->default_attrs );
	
		// Définition des options		
		$field['options'] = $this->set_options( $field );
		
		// Incrémentation des liste choix
		if( $field['choices'] && is_array( $field['choices'] ) ) :
			array_unshift( $field['choices'], null );
			unset( $field['choices'][0] ) ;
		endif;
		
		// Concaténation des attributs de champ protégés
		$field['form_id'] = $this->master->forms->current['ID'];
		$field['form_prefix'] = $this->master->forms->current['prefix'];			
		$field['slug'] = ! isset( $field['slug'] )? "field-slug_".$field['form_id']."-".$field['form_prefix']."-".$field['index'] : $field['slug'];
		$field['name'] = $this->get_name( $field );
		
		// Option par defaut des addons
		$field['add-ons'] = $this->master->addons->set_field_options( $field['add-ons'] );		
		
		$this->master->callbacks->call( 'field_set', array( &$field, $this->master ) );
		
		return $field;
	}

	/**
	 * Définition des options des champs
	 */
	public function set_options( $field ){
		if( ( $type_datas = $this->master->field_types->get_type_datas( $field['type'] ) ) && ( isset( $type_datas['options'] ) ) )
			$field['options'] = $this->master->functions->parse_options( $field['options'], $type_datas['options'] );
		
		return $field['options'];
	}
	
	/**
	 * Récupération des champs à afficher pour le formulaire courant
	 *  
	 * @return mixed Liste des champs 
	 */	
	public function get_fields_displayed( ){
		// Bypass
		if( ! $_form = $this->master->forms->get_current( ) )
			return;

		return $_form['_fields'];
	}
			
	/**
	 * Récupération de la liste des champs pour un formulaire
	 *
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Un tableau indexé de tous les champs et leurs attributs pour le formulaire requis 
	 */
	public function get_list( $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		if( isset( $_form['fields'] ) )
			return $_form['fields'];
	}	

	/**
	 * Récupération d'un champ selon son un attribut
	 * 
	 * @param string $attr attribut de champs @this->set_field pour connaître la liste
	 * @param string $value valeur de l'attribut
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Un tableau dimensionné du champ et de ses attributs pour le formulaire requis
	 */
	public function get_by( $attr, $value, $form = null ){
		// Bypass
		if( ! $_form = $this->master->forms->get( $form ) )
			return;
		
		foreach( $_form['fields'] as $field )
			if( isset( $field[$attr] ) && ( $field[$attr] == $value ) )
				return $field;
	} 
	
	/**
	 * Récupération d'un champ selon son slug
	 * 
	 * @param string $slug valeur du slug
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return array Un tableau dimensionné du champs et de ses attributs pour le formulaire requis
	 */
	public function get_by_slug( $slug, $form = null ){
		return $this->get_by( 'slug', $slug, $form );
	}
	
	/**
	 * Récupération de l'index d'un champs selon son slug
	 * 
	 * @param string $attr attribut de champs @see this->set_field pour connaître la liste
	 * @param string $value valeur de l'attribut
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return null|int Valeur de l'index du champ pour le formulaire requis
	 */
	private function get_index_by( $attr, $value, $form = null ){
		// Bypass
		if( ! $fields = $this->get_list( $form ) )
			return;	
		
		foreach( $fields as $index => $field )
			if( isset( $field[$attr] ) && ( $field[$attr] == $value ) )
				return $index;
	}
	
	/**
	 * Récupération de l'index d'un champs selon son slug
	 * 
	 * @param string $slug valeur du slug
	 * @param int|object|null $form ID ou objet formulaire. null correspont au formulaire courant
	 * 
	 * @return null|int Valeur de l'index du champ pour le formulaire requis
	 */
	private function get_index_by_slug( $slug, $form = null ){
		return $this->get_index_by( 'slug', $slug, $form );
	}
	
	/**
	 * Récupération du nom d'un champ
	 * 
	 * @param array $field Tableaux dimensionné de champ
	 * 
	 * @return string Valeur du champ pour le formulaire requis
	 */
	public function get_name( $field ){
		return sprintf( $field['name'], $field['form_prefix'], $field['form_id'], $field['slug'] );
	}
		
	/**
	 * Récupération de la valeur pour un champs
	 */
	public function get_value( $field ){
		if( ( $this->master->handle->parsed_request['form_id'] == $field[ 'form_id' ] ) && ( isset( $this->master->handle->parsed_request['values'][ $field['slug'] ] ) ) ) :
			$field_value = $this->master->handle->parsed_request['values'][ $field['slug'] ];
		else :
			$field_value = $field['value'];
		endif;		
		
		$this->master->callbacks->call( 'field_value', array( &$field_value, $field, $this->master ) );

		return $field_value;
	}
		
	/**
	 * Translation des valeurs pour les choix
	 */
	public function translate_value( $value, $choices, $field ){		
		foreach( (array) $choices as $index => $label ) :				
			if( ! empty( $field['choice_none'] ) && ( empty( $value ) ) ):
				$value = $field['choice_none'];
			elseif( ! empty( $field['choice_all'] ) && ( (int) $value == -1 ) ) :
				$value = $field['choice_all'];
			elseif( $value == $index ) :				
				$value = $label;
			endif;
		endforeach;	
		
		return $value;
	}
	
	/* = AFFICHAGE = */
	/** == Affichage d'un champs de formulaire == **/
	public function display( $field, $args = array() ){
		$field 	= $this->master->functions->parse_options( $args, $field );
		// Type d'affichage du champ
		$field['display'] = false;
		
		// Fonction de court-circuitage des attributs de champs avant l'affichage
		$this->master->callbacks->call( 'field_before_display', array( &$field ) );
		
		// Récupération des options de formulaire
		$form_options = $this->master->forms->get_options( $field[ 'form_id' ] );		
		
		// Gestion de l'instance d'erreur
		static $err_inst;
		if( ! $err_inst ) $err_inst = 0;
		$has_error = ( $this->master->errors->field_has( $field ) && ( $err_inst < $this->master->errors->showed ) ) ? 'has_error' : '';		
		
		$output  = "";
		
		// Pré-Affichage du formulaire	
		if( isset( $field['before'] ) )
			$output .= $field['before'];
		
		// Récupération de la valeur du champ		
		$field['value'] = $field['transport'] ? $this->get_value( $field ) : false;

		if( $field['type'] == 'hidden' )
			return 	"\n<input type=\"hidden\" name=\"". esc_attr( $this->get_name( $field ) ) ."\" class=\"field field-". $field['slug'] ."\" value=\"". esc_attr( stripslashes( $field['value'] ) )."\" />";	
		
		// Classe du container		
		$field_class = sprintf( $field['container_class'], "field-wrapper field-wrapper-".$field['type']." field-wrapper-".$field['slug']." field-wrapper-".$field['form_id']."-".$field['slug']." ".$has_error ); 
		if( $field['required'] )
			$field_class .= " field-required";
		
		// Ouverture du wrapper
		if( ! $this->master->field_types->type_supports( 'nowrapper', $field['type'] ) )
			$output .= "\n<div class=\"{$field_class}\">";
		switch( $field['type'] ) :
			case 'html' :
				$output .= sprintf( $field['html'], "{$field['form_prefix']}[{$field['form_id']}][".$field['slug']."]", $field['label'], $field['value'] );
				break;
			case 'string' :
				$output .= sprintf( $field['html'], "{$field['form_prefix']}[{$field['form_id']}][".$field['slug']."]", $field['label'], $field['value'] );
				break;
			case 'button' :
				$output .= $this->master->buttons->display_button( $field['value'], $field['options'] );
				break;				
			case 'textarea' :		
			case 'input' :
			case 'password' :
			case 'checkbox' :
			case 'radio' :
			case 'dropdown' :	
				$name = $this->get_name( $field );				
				// Intitulé (Label)
				if( $this->master->field_types->type_supports( 'label', $field['type'] ) && ! empty( $field['label'] ) ) :
					$label_class = sprintf( $field['label_class'], "field-label field-label-".$field['type']." field-label-".$field['slug']." field-label-".$field['form_id']."-".$field['slug'] );
					$output .= "\n\t<label for=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$label_class."\">";
					$output .= $field['label'];					
					if( $field['required'] ) 
						$output .= "<span class=\"required\">*</span>";
					$output .= "</label>";
				endif;
				
				// Encapsulation des choix
				if( in_array( $field['type'], array( 'radio', 'checkbox' ) ) )
					$output .= "<div class=\"choices-wrapper\">";
					
				//Ouverture de balise ouvrante du champ de saisie								
				switch( $field['type'] ) :
					case 'input' :
						$output .= "\n\t<input type=\"text\" value=\"". esc_attr( $field['value'] ) ."\"";
						break;
					case 'password' :
						$output .= "\n\t<input type=\"password\" value=\"". esc_attr( $field['value'] )."\"";
						break;
					case 'textarea' :
						$output .= "\n\t<textarea";
						break;
					case 'dropdown' :
						$output .= "\n\t<select";
						break;
				endswitch;
				// Attributs (name, id, class )
				if( ! in_array( $field['type'], array( 'checkbox', 'radio' ) ) ) :
					$field_class = rtrim( trim( sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']}") ) );
					$output .= " name=\"". esc_attr( $name ) ."\" id=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$field_class."\"";
				endif;
				
				if( $this->master->field_types->type_supports( 'placeholder', $field['type'] ) && $field['placeholder'] )
					if( is_bool($field['placeholder']) )
						$output .= " placeholder=\"".$field['label']."\"";
					elseif( is_string( $field['placeholder']) )
						$output .= " placeholder=\"".$field['placeholder']."\"";
				//Autocomplete des champs de saisie
				if( in_array( $field['type'], array( 'input', 'password', 'dropdown', 'textarea' ) ) )	
					$output .= " autocomplete=\"{$field['autocomplete']}\"";
				// Champs en lecture seule
				if( in_array( $field['type'], array( 'input', 'password', 'dropdown', 'textarea' ) ) && $field['readonly'] )
					$output .= " readonly=\"readonly\"";
				// Autoriser le copier/coller
				if( in_array( $field['type'], array( 'input', 'password', 'dropdown', 'textarea' ) ) && ! $field['onpaste'] )	
					$output .= " onpaste=\"return false;\""; 
				//Fermeture de balise ouvrante du champ de saisie
				if( in_array( $field['type'], array( 'input', 'password', 'dropdown' ) ) ) :
					$output .= "/>";		
				elseif( $field['type'] == 'textarea' ) :
					$output .= ">";
				endif;
				
				if( $this->master->field_types->type_supports( 'choices', $field['type'] ) ) :					
					if( $field['type'] =='dropdown' && $field['choice_all'] )
						$output .= "<option value=\"". esc_attr( -1 ) ."\" ".selected( empty($field['value']), true, false ).">{$field['choice_all']}</option>";
					if( $field['type'] =='dropdown' && $field['choice_none'] )
						$output .= "<option value=\"". esc_attr( 0 ) ."\" ".selected( empty($field['value']), true, false ).">{$field['choice_none']}</option>";					

					// Lecture seule des cases à cocher et boutons radio
					if( ( $field['type'] == 'checkbox' ) && $field['readonly'] ) :
						if( empty( $field['value'] ) )
							$output .= "<input type=\"hidden\" name=\"". esc_attr( $name ) ."[]\" value=\"\" />";
						else
							foreach( (array) $field['value'] as $val )
								$output .= "<input type=\"hidden\" name=\"". esc_attr( $name )."[]\" value=\"{$val}\" />";
					elseif( ( $field['type'] == 'radio' ) && $field['readonly'] ) :
						$output .= "<input type=\"hidden\" name=\"". esc_attr( $name ) ."\" value=\"". ( $field['value'] ? esc_attr( $field['value'] ) : "" )."\" />";
					endif;
					
					foreach( (array) $field['choices'] as $ovalue => $label ) :
						switch( $field['type'] ) :
							case 'dropdown' :
								$output .= "<option value=\"". esc_attr( $ovalue ) ."\" ".selected( $field['value'] == $ovalue, true, false ).">{$label}</option>";
								break;
							case 'checkbox' :								
								$output .= "<label class=\"choice-title\"><input type=\"checkbox\" value=\"". esc_attr( $ovalue ) ."\" name=\"". esc_attr( $name )."[]\" ".checked( ( is_array( $field['value'] ) && in_array( $ovalue, $field['value']) ), true, false )." autocomplete=\"{$field['autocomplete']}\"". ( $field['readonly'] ? " disabled=\"disabled\" " : "" ) ."/>$label</label>";
								break;
							case 'radio' :
								$output .= "<label class=\"choice-title\"><input type=\"radio\" value=\"". esc_attr( $ovalue ) ."\" name=\"". esc_attr( $name )."\" ".checked($field['value']==$ovalue, true, false )." autocomplete=\"{$field['autocomplete']}\"". ( $field['readonly'] ? " disabled=\"disabled\" " : "" ) ."/>$label</label>";
								break;			
						endswitch;
					endforeach;			
				endif;
				
				// Fermeture de l'encapsulation des choix
				if( in_array( $field['type'], array( 'radio', 'checkbox' ) ) )
					$output .= "</div>";
								
				//Balise fermante du champ de saisie				
				switch( $field['type'] ) :
					case 'textarea' : 						
						$field['value']	= trim( strip_tags( html_entity_decode( $field['value'] ) ), "\t\n\r\0\x0B." );
						$output .= esc_attr( $field['value'] ) ."</textarea>";
						break;
					case 'dropdown' :
						$output .= "</select>"; 
						break;
				endswitch;					
				break;				
			default :
				// Intitulé (Label)
				if( $this->master->field_types->type_supports( 'label', $field['type'] ) && ! empty( $field['label'] ) ) :
					$label_class = sprintf( $field['label_class'], "field-label field-label-".$field['type']." field-label-".$field['slug']." field-label-".$field['form_id']."-".$field['slug'] );
					$output .= "\n\t<label for=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$label_class."\">";
					$output .= $field['label'];
				
					if( $field['required'] ) 
						$output .= "<span class=\"required\">*</span>";
					$output .= "</label>";
				endif;
				
				$this->master->callbacks->call( 'field_type_output_display', array( &$output, $field, $this->master ) );
				break;					
		endswitch;
		
		// Affichage des erreurs de formulaire
		if( $has_error && $form_options['errors']['field'] )
			$output .= "<div class=\"field-error\">".$this->master->errors->field_display( $field )."</div>";		
		
		// Fermeture du wrapper
		if( ! $this->master->field_types->type_supports( 'nowrapper', $field['type'] ) )
			$output .= "\n</div>";		
		
		// Post-Affichage du formulaire		
		if( isset( $field['after'] ) )
			$output .=  $field['after'];
		
		// Incrémentation de l'instance d'erreur
		if( $this->master->errors->field_has( $field ) )
			$err_inst++;
				
		// Fonction de court-circuitage de l'affichage du champ
		$this->master->callbacks->call( 'field_output_display', array( &$output, $field ) );		
				
		if( $field['echo'] )
			echo $output;
		else
			return $output;
	}
	
	/**
	 * ACTIONS SUR LES TYPES DE CHAMPS
	 */
	/**
	 * Définition d'un nouveau type de champs
	 * 
	 * @param array $args Attributs du type
	 * @see tiFy_Forms_FieldTypes::set_type
	 */
	public function set_type( $args = array() ){
		$this->master->field_types->set_type( $args );
	}	
}