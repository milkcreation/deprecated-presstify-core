<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\Dropdown;

use tiFy\Core\Forms\FieldTypes\Factory;

class Dropdown extends Factory
{
	/* = ARGUMENTS = */
	// Support
	public $Supports = array(
		'integrity',
		'label', 
		'placeholder', 
		'request',
		'wrapper'
	);	
		
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{
					/*
		if( $field['type'] == 'hidden' )
			return 	"\n<input type=\"hidden\" name=\"". esc_attr( $this->get_name( $field ) ) ."\" class=\"field field-". $field['slug'] ."\" value=\"". esc_attr( stripslashes( $field['value'] ) )."\" />";	
		/*
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
								$field['value'] = (array) $field['value'];
								$output .= "<label class=\"choice-title\"><input type=\"checkbox\" value=\"". esc_attr( $ovalue ) ."\" name=\"". esc_attr( $name )."[]\" ". checked( in_array( $ovalue, $field['value'] ), true, false ) ." autocomplete=\"{$field['autocomplete']}\"". ( $field['readonly'] ? " disabled=\"disabled\" " : "" ) ."/>$label</label>";
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
		*/	
	}
}