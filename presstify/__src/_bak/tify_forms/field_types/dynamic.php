<?php
/*
FieldType Name: Dynamic
FieldType ID: dynamic
Callback: tiFy_Forms_FieldType_Dynamic
Version: 1.150817
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

Class tiFy_Forms_FieldType_Dynamic extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array( 
			'slug'			=> 'dynamic',
			'label' 		=> __( 'Champ dynamique', 'tify' ),
			'section' 		=> 'misc',
			'order'			=> 99,
			'supports'		=> array( 'request', 'nowrapper' ),
			'options'		=> array(
			'type' 				=> 'html',
				'init' 			=> 1,
				'start' 		=> 0,
				'add_by'		=> 1,
				'buttons' 		=> array(
					'add' => array(
						'title' => __( 'Ajouter', 'tify' )
					)
				)
			)
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_set' 				=> array( $this, 'cb_field_set' ),
			'form_buttons_display' 		=> array( $this, 'cb_field_set' ),
			'handle_parse_submit' 		=> array( $this, 'cb_handle_parse_submit' ),
		);
		
        parent::__construct( $master );		
	}
	
	/* = CALLBACKS = */
	/** == Définition du champ ==
	 * @TODO Conservation des valeurs soumises ??
	 */
	function cb_field_set( &$field ){
		if( $field['type'] != 'dynamic' )
			return;
		
		// Définition de la quantité de champ à afficher
		$amount = ( ( $cache = $this->master->handle->get_cache() ) && isset( $cache['fields']['dynamic'][$field['slug']]['amount'] ) ) ? $cache['fields']['dynamic'][$field['slug']]['amount'] : $field['options']['init'];
		$i = $field['options']['start'];
		while( $i < ( $amount + $field['options']['start'] ) ) :
			$_fields[] = array_merge(
				$field,
				array(
					'type' => $field['options']['type'],
					'label' => sprintf( $field['label'], $i ),
					'slug' => $field['slug'].'-'.$i,
					'name' => '%s',
					'value' => $this->master->fields->get_value( $field ),
					'dynamic' => $field['slug']
				)
			);
			$i++;				
		endwhile;
		$this->master->handle->set_cache( array(
				'dynamic-'.$field['slug'] => $field['options']['add_by']
			)			
		);
	}
	
	/** == Affichage du bouton d'ajout de champs == **/
	function cb_form_buttons_display( &$output ){
		$has_field = false;
		$dynamics = array();
		foreach( ( array ) $this->master->fields->get_fields_displayed() as $f ) :
			if( ! isset( $f['dynamic'] ) ) continue;
			if( in_array( $f['dynamic'], $dynamics ) ) continue;
			$dynamics[] = $f['dynamic'];
			$has_field = true;
		endforeach;
		
		if( ! $has_field )
			return;
		
		$_form['ID'] = $this->master->forms->get_ID();
		$_form['prefix'] = $this->master->forms->get_prefix();
		$button = "";
	
		foreach( $dynamics as $dynamic ) :
			$button .= "<div class=\"buttons-group dynamic-buttons\">\n";
			$button .= "\t<button type=\"submit\" id=\"dynamic_add-{$_form['prefix']}-{$_form['ID']}\" class=\"dynamic\" name=\"submit-{$_form['prefix']}-{$_form['ID']}\" value=\"dynamic_add:{$dynamic}\">".__( 'Ajouter', 'tify')."</button>\n";
			$button .= "\t</div>\n";
		endforeach;
	
		$output = $button . $output;
	}

	/** == == **/
	function cb_handle_parse_submit( $continue, $submit ){
		// Bypass
		if( ! preg_match( '/dynamic_add:/', $submit ) )
			return $continue;
		if( ! preg_match( '/dynamic_add:(.*)/', $submit, $matches ) )
			return $continue;
		if( ! $field = $this->master->fields->get_by_slug( $matches[1] ) )
			return $continue;
		
		$amount = ( ( $cache = $this->master->handle->get_cache() ) && isset( $cache['fields']['dynamic'][$field['slug']]['amount'] ) ) ? $cache['fields']['dynamic'][$field['slug']]['amount'] : $field['options']['init'];
		$args['fields']['dynamic'][$field['slug']]['amount'] = $amount + $field['options']['add_by'];
		$this->master->handle->set_cache( $args );
		$this->master->forms->step_form_datas();
		
		return false;	
	}
}