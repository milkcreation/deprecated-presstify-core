<?php
	/**
	 * Affichage d'un formulaire
	 *
	 * @param int|object $form ID ou objet formulaire. requis
	 * @param array $args Options d'affichage du formulaire
	 * 
	 * @return HTML Affiche ou retourne le formulaire requis
	 */
	public function display_summary( $form, $args = array() ){	
		// Bypass et Initialisation de l'élément courant
		if( ! $_form = $this->set_current( $form ) )
			return;
		
		$_form['display'] = "summary";
		
		// Traitement des options d'affichage
		$args = $this->master->options->parse( $args, array( 'echo' => true ) );
				
		// Fonction de court-circuitage des attributs de formulaire post-affichage
		if( isset( $this->before_display_callback[ $_form['ID'] ] ) )		
			foreach( (array) $this->before_display_callback[ $_form['ID'] ] as $callback )
				call_user_func_array( $callback, array( &$_form ) );
		
		// Génération de la sortie HTML du formulaire
		$output = "";
		
		// Pré-affichage HTML
		$output .= $_form['before'];
		
		$output .= "\n<div id=\"mkcf_container_{$_form['ID']}\" class=\"".sprintf( $_form['container_class'], "mkcf_container" )."\">";
			
		// Affichage des champs de formulaire
		$output .= "\n\t<div class=\"fields-wrapper\">";
		foreach( (array) $_form['fields'] as $field )
			$output .= $this->master->fields->display_summary( $field, $_form['field_display_args'] );
		$output .= "\n\t</div>";
		$output .= "\n</div>";
		
		// Post-affichage HTML
		$output .= $_form['after'];	
		
		// Fonction de court-circuitage de l'affichage du formulaire
		if( isset( $this->output_display_callback[ $_form['ID'] ] ) )		
			foreach( (array) $this->output_display_callback[ $_form['ID'] ] as $callback )
				call_user_func_array( $callback, array( &$output, $_form ) );
		
		// Réinitialisation de l'élément courant
		$this->reset_current();
		
		if( $args['echo'] )
			echo $output;
		else 
			return $output;	
	}

	
	
	/**
	 * Affichage d'un champs récapitulatif.
	 */
	public function display_summary( $field, $args = array() ){
		
		$field 	= $this->mkcf->options->parse( $args, $field );
		// Type d'affichage du champ
		$field['display'] = "summary";
					
		// Fonction de court-circuitage des attributs de champs avant l'affichage
		if( isset( $this->before_display_callback[ $field['form_id'] ] ) )
			foreach( (array) $this->before_display_callback[ $field['form_id'] ] as $callback )
				call_user_func_array( $callback, array( &$field ) );
		
		$output  = "";
		
		// Pré-Affichage du formulaire	
		if( isset( $field['options']['before'] ) )
			$output .= $field['options']['before'];
		
		// Récupération de la valeur du champ		
		$field['value'] = $this->get_value( $field );

		// Classe du container		
		$field_class = sprintf( $field['container_class'], "field-wrapper field-wrapper-".$field['type']." field-wrapper-".$field['slug']." field-wrapper-".$field['form_id']."-".$field['slug'] ); 
		if( $field['required'] )
			$field_class .= " field-required";

		$output .= "\n<div class=\"$field_class\">";
		switch( $field['type'] ) :
			case 'html' :
				$output .= sprintf( $field['html'], "{$field['form_prefix']}[{$field['form_id']}][".$field['slug']."]", $field['label'], $field['value'] );
				break;
			case 'string' :
				$output .= sprintf( $field['html'], "{$field['form_prefix']}[{$field['form_id']}][".$field['slug']."]", $field['label'], $field['value'] );
				break;
			case 'textarea' :		
			case 'input' :
			case 'password' :
			case 'checkbox' :
			case 'radio' :
			case 'dropdown' :	
				$name = $this->get_name( $field );				
				// Intitulé (Label)
				if ( ! $field['label_hide'] ) :
					if( $this->types->type_supports( 'label', $field['type'] ) ) :
						$label_class = sprintf( $field['label_class'], "field-label field-label-".$field['type']." field-label-".$field['slug']." field-label-".$field['form_id']."-".$field['slug'] );
						$output .= "\n\t<label for=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$label_class."\">";
						$output .= $field['label'];
					endif;	
					if( $field['required'] ) 
						$output .= "<span class=\"required\">*</span>";
					if( $field['info'] )	
						$output .= "<em class=\"info\">{$field['info']}</span>";					
					if( $field['label'] )
						$output .= "</label>";
				endif;
				//Ouverture de balise ouvrante du champ de saisie								
				switch( $field['type'] ) :
					case 'input' :
						$output .= "\n\t<input type=\"text\" value=\"{$field['value']}\" disabled=\"disabled\"";
						break;
					case 'password' :
						$output .= "\n\t<input type=\"password\" value=\"{$field['value']}\" disabled=\"disabled\"";
						break;
					case 'file' :	
						$output .= "\n\t<input type=\"text\" value=\"{$field['value']['name']}\" disabled=\"disabled\" ";
					break;
					case 'textarea' :
						$output .= "\n\t<textarea disabled=\"disabled\"";
						break;
					case 'dropdown' :
						$output .= "\n\t<select disabled=\"disabled\"";
						break;
				endswitch;
				// Attributs (name, id, class )
				if( ! in_array( $field['type'], array('checkbox', 'radio') ) ) :
					$field_class = sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']}");
					$output .= " name=\"$name\" id=\"field-{$field['form_id']}-{$field['slug']}\" class=\"".$field_class."\"";
				endif;
				
				if( $this->types->type_supports( 'placeholder', $field['type'] ) ) :
					if( $field['placeholder'] ):
						if( is_bool($field['placeholder']) ) :
							$output .= "placeholder=\"".$field['label']."\"";
						elseif( is_string( $field['placeholder']) && empty( $field['value']) ) :
							$output .= "placeholder=\"".__("-- non renseigné --","mktzr")."\"";
						endif;
					else :
						$output .= "placeholder=\"".__("-- non renseigné --","mktzr")."\"";
					endif;
				endif;
				
				//Autocomplete des champs de saisie
				if( in_array( $field['type'], array( 'input', 'password', 'dropdown', 'textarea' ) ) )	
					$output .= "autocomplete=\"{$field['autocomplete']}\"";
				//Fermeture de balise ouvrante du champ de saisie
				if( in_array( $field['type'], array( 'input', 'password','file', 'dropdown' ) ) ) :
					$output .= "/>";				
				elseif( $field['type'] == 'textarea' ) :
					$output .= ">";
				endif;
				
				if( $this->types->type_supports( 'choices', $field['type'] ) ) :
					if( $field['type'] =='dropdown' && $field['choice_all'] )
						$output .= "<option value=\"-1\" ".selected(empty($field['value']), true, false ).">{$field['choice_all']}</option>";
					if( $field['type'] =='dropdown' && $field['choice_none'] )
						$output .= "<option value=\"0\" ".selected(empty($field['value']), true, false ).">{$field['choice_none']}</option>";					
											
					$i=0; 
					foreach( (array) $field['choices'] as $label ) :
						$ovalue = ++$i;
						switch( $field['type'] ) :
							case 'dropdown' :
								$output .= "<option value=\"{$ovalue}\" ".selected($field['value']==$ovalue, true, false ).">$label</option>";
								break;
							case 'checkbox' :								
								$output .= "<label class=\"choice-title\"><input type=\"checkbox\" value=\"{$ovalue}\" name=\"{$name}[]\" ".checked( ( is_array($field['value']) && in_array( $ovalue, $field['value']) ), true, false )." disabled=\"disabled\" autocomplete=\"{$field['autocomplete']}\"/>$label</label>";
								break;
							case 'radio' :
								$output .= "<label class=\"choice-title\"><input type=\"radio\" value=\"{$ovalue}\" name=\"{$name}\" ".checked($field['value']==$ovalue, true, false )." disabled=\"false\" autocomplete=\"{$field['autocomplete']}\"/>$label</label>";
								break;			
						endswitch;
					endforeach;			
				endif;	
				
				//Balise fermante du champ de saisie				
				switch( $field['type'] ) :
					case 'textarea' : 
						$field['value']	= trim( strip_tags( $field['value'] ), "\t\n\r\0\x0B." );
						$output .= "{$field['value']}</textarea>";
						break;
					case 'dropdown' :
						$output .= "</select>"; 
						break;
				endswitch;					
					
				break;
			default :
				if ( ! $field['label_hide'] )
					// Intitulé (Label)
					if( $this->types->type_supports( 'label', $field['type'] ) ) :
						$output .= "\n\t<label for=\"field-{$field['form_id']}-{$field['slug']}\" class=\"field-label field-label-".$field['type']." field-label-".$field['slug']." field-label-".$field['form_id']."-".$field['slug']."\">";
						$output .= $field['label'];
					endif;
					if( $field['required'] ) 
						$output .= "<span class=\"required\">*</span>";
					if( $field['info'] )	
						$output .= "<em class=\"info\">{$field['info']}</span>";					
					if( $this->types->type_supports( 'label', $field['type'] ) ) 
						$output .= "</label>";
					if( isset( $this->output_type_display_callback[ $field['type'] ] ) )
						foreach( $this->output_type_display_callback[ $field['type'] ] as $callback )
							call_user_func_array( $callback , array( &$output, $field ) );
				break;					
		endswitch;
		
		$output .= "\n</div>";		
		
		// Post-Affichage du formulaire		
		if( isset( $field['options']['after'] ) )
			$output .= "<div class=\"field-error\">". $field['options']['after'] ."</div>";
		
		if( $field['echo'] )
			echo $output;	
		else
			return $output;	
	}