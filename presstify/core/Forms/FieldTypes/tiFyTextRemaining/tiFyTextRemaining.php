<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\tiFyTextRemaining;

class tiFyTextRemaining extends \tiFy\Core\Forms\FieldTypes\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'tify_text_remaining';
	
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
			tify_control_enqueue( 'text_remaining' );
		self::$Instance++;
		
		// Traitement des arguments
		$args = $this->field()->getOptions(); 
		
		/// Arguments imposés
		$args['id'] 				= $this->getInputID();
		$args['class']				= join( ' ', $this->getInputClasses() );
		$args['name']				= $this->field()->getDisplayName();
		$args['value']				= $this->field()->getValue();
		if( ! isset( $args['attrs'] ) ) $args['attrs'] = array();
		$args['attrs']['tabindex']	= $this->field()->getTabIndex();
		$args['echo'] 				= false;
		
		return tify_control_text_remaining( $args );		
	}
}