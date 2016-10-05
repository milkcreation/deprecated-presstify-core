<?php
namespace tiFy\Core\Forms\FieldTypes\TouchTime;

use tiFy\Core\Forms\FieldTypes\Factory;

class TouchTime extends Factory
{
	/* = CONSTRUCTEUR = */				
	public function __construct()
	{
		// Définition de l'identifiant
		$this->ID = 'touchtime';
		
		// Définition du type de champ
		$this->attrs = array( 
			'slug'			=> 'touchtime',
			'label' 		=> __( 'Date', 'tify' ),
			'section' 		=> 'misc',
			'supports'		=> array( 'label', 'request' )
		);	
		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_type_output_display' 		=> array( $this, 'cb_field_type_output_display' ),
			'handle_parse_request_field' 				=> array( $this, 'cb_handle_parse_request_field' )
		);
		
		parent::__construct();				
	}
	
	/* = CALLBACKS = */
	/** == Affichage du champ == **/	 
	public function display()
	{
	 	$output .= mk_touch_time( 
			array(
				'echo' 		=> false, 
				'name' 		=> $this->master->fields->get_name( $field ),
				'selected' 	=> esc_attr( $this->date2mysql( $field['value'] ) )
			) 
		);
	}
	
	/** == Fonction de court-circuitage de la valeur de requête - Translation de la valeur au format SQL == **/
	function cb_handle_parse_request_field( &$field ){
		// Bypass
		if( $field['type'] != 'touchtime' )
			return;
		
		$field['value'] = esc_attr( $this->date2mysql( $field['value'] ) );
	}

	/* = CONTROLEURS = */
	/** == Translation des données de date au format SQL == **/
	function date2mysql( $date ){
		if( is_array( $date ) && isset( $date['jj'] ) && isset( $date['mm'] ) && isset( $date['aa'] ) && isset( $date['hh'] ) && isset( $date['mn'] ) && isset( $date['ss'] ) )
			return $date['aa']."-".$date['mm']."-".$date['jj']." ".$date['hh'].":".$date['mn'].":".$date['ss'];
		return $date;
	}
}
