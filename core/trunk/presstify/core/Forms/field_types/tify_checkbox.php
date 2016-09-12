<?php
/*
FieldType Name: Tify Checkbox
FieldType ID: tify_checkbox
Callback: tiFy_Forms_FieldType_TifyCheckbox
Version: 1.160705
*/

/**
 * Configuration :
 	...
 	array(
		'ID' 		=> {form_id},
		'title' 	=> '{form_title}',
		'prefix' 	=> '{form_prefix}',
		'fields' 	=> array(
			...
			array(
				'slug'			=> '{field_slug}',
				'label' 		=> '{field_label}',
				'type' 			=> 'tify_checkbox',
			),
			...
		)
	)
	... 
 */

Class tiFy_Forms_FieldType_TifyCheckbox extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */

	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array(
			'slug'			=> 'tify_checkbox',
			'label' 		=> __( 'tiFy checkbox', 'tify' ),
			'section' 		=> 'misc',
			'supports'		=> array( 'label', 'choices', 'integrity-check', 'request' )
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_type_output_display' => array( $this, 'cb_field_type_output_display' )
		);
		
        parent::__construct( $master );	
	}
	
	/* = CALLBACKS = */	
	/** == Affichage du champ == **/
	function cb_field_type_output_display( &$output, $field ){
		// Bypass
		if( $field['type'] != 'tify_checkbox' )
			return;
		static $instance;
		
		if( ! $instance )
			tify_control_enqueue( 'checkbox' );
		$instance++;
		
		foreach( (array) $field['choices'] as $ovalue => $label ) :
			$checked = ( is_array( $field['value'] ) ) ? in_array( $ovalue, $field['value'] ) : $field['value'];
			$output .= tify_control_checkbox( array(
					'id'				=> "field-{$field['form_id']}-{$field['slug']}",
					'class'				=> rtrim( trim( sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']} tify_control_dropdown") ) ),
					'name'				=> $field['name'],
					'checked'			=> $checked,
					'value'				=> $ovalue,
					'label'				=> $label,
					'label_class'		=> 'choice-title',
					'echo'				=> false
				)
			);
		endforeach;
	}
}