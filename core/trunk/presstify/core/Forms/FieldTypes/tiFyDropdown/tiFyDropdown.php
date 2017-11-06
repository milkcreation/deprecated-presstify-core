<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\tiFyDropdown;

class tiFyDropdown extends \tiFy\Core\Forms\FieldTypes\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'tify_dropdown';
	
	// Support
	public $Supports 	= array(
		'integrity',
		'label', 
		'request',
		'wrapper'
	);
	
	// Instance
	private static $Instance;
	
	/* = CONTROLEURS = */	
	/** == Affichage du champ == **/
	public function display()
	{
		if( ! self::$Instance )
			tify_control_enqueue( 'dropdown' );
		self::$Instance++;
		
		// Traitement des arguments
		$args = wp_parse_args( 
			$this->field()->getOptions(), 
			array(
				'option_none_value' => 0
			)
		);
		
		/// Arguments imposés
		$args['id'] 				= $this->getInputID();
		$args['class']				= join( ' ', $this->getInputClasses() );
		$args['show_option_none'] 	= ( $show_option_none = $this->field()->getAttr( 'choice_none' ) ) ? $show_option_none : false;
		$args['name']				= $this->field()->getDisplayName();
		$args['selected']			= $this->field()->getValue();
		$args['choices'] 			= $this->field()->getAttr( 'choices' );
		if( ! isset( $args['attrs'] ) ) $args['attrs'] = array();
		$args['attrs']['tabindex']	= $this->field()->getTabIndex();
		if( ! isset( $args['picker']['class'] ) )
			$args['picker']['class'] = 'tiFyForm-FieldInputPicker tiFyForm-FieldPickerInput--'. $this->getID() .' tiFyForm-FieldPickerInput--'. $this->field()->getSlug();
		$args['echo'] 				= false;

		return tify_control_dropdown( $args );		
	}
}