<?php
/*
FieldType Name: Tify Dropdown
FieldType ID: tify_dropdown
Callback: tiFy_Forms_FieldType_TifyDropdown
Version: 1.150817
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
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
				'type' 			=> 'tify_dropdown',
			),
			...
		)
	)
	... 
 */

Class tiFy_Forms_FieldType_TifyDropdown extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */

	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array(
			'slug'			=> 'tify_dropdown',
			'label' 		=> __( 'tiFy Dropdown', 'tify' ),
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
		if( $field['type'] != 'tify_dropdown' )
			return;
		static $instance;
		
		if( ! $instance )
			tify_control_enqueue( 'dropdown' );
		$instance++;
		
		// Traitement des arguments
		$args = wp_parse_args( 
			$field['options'], 
			array(					
				'echo'				=> false
			)
		);
		/// Arguments imposés
		$args['id'] 				= "field-{$field['form_id']}-{$field['slug']}";
		$args['class']				= rtrim( trim( sprintf( $field['field_class'], "field field-{$field['form_id']} field-{$field['slug']} tify_control_dropdown") ) );
		$args['show_option_none'] 	= ! empty( $field['choice_none'] ) ? $field['choice_none'] : false;
		$args['name']				= $field['name'];
		$args['selected']			= $field['value'];
		$args['choices'] 			= $field['choices'];
		
		$output .= tify_control_dropdown( $args );		
	}
}