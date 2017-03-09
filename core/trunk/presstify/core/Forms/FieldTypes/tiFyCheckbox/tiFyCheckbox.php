<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\tiFyCheckbox;

class tiFyCheckbox extends \tiFy\Core\Forms\FieldTypes\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'tify_checkbox';
	
	// Support
	public $Supports 	= array(
		'integrity',
		'label', 
		'request',
		'wrapper'
	);
	
	// Instance
	private static $Instance;
		
	/* = CALLBACKS = */	
	/** == Affichage du champ == **/
	public function display()
	{		
		if( ! self::$Instance ) :
			tify_control_enqueue( 'checkbox' );
		endif;
		
		self::$Instance++;
			
		$selected = $this->field()->getValue();		
		$output = "";
		foreach( (array) $this->field()->getAttr( 'choices' ) as $value => $label ) :
			$checked = ( is_array( $selected ) ) ? in_array( $value, $selected ) : $selected;
			$output .= tify_control_checkbox( 
				array(
					'id'				=> $this->getInputID(),
					'class'				=> join( ' ', $this->getInputClasses() ),
					'name'				=> $this->field()->getDisplayName(),
					'checked'			=> $checked,
					'value'				=> $value,
					'label'				=> $label,
					'label_class'		=> 'choice-title',
					'echo'				=> false
				)
			);
		endforeach;
		
		return $output;
	}
}