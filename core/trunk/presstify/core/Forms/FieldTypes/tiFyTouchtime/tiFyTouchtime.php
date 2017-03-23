<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\FieldTypes\tiFyTouchtime;

class tiFyTouchtime extends \tiFy\Core\Forms\FieldTypes\Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'tify_touchtime';
	
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
			tify_control_enqueue( 'touch_time' );
		self::$Instance++;
		
		// Traitement des arguments
		$args = wp_parse_args( 
			$this->field()->getOptions(), 
			array()
		);
		
		/// Arguments imposés
		$args['container_id'] 				= $this->getInputID();
		$args['container_class']	= join( ' ', $this->getInputClasses() );
		$args['name']				= $this->field()->getDisplayName();
		$args['value']				= $this->field()->getValue();
		$args['attrs']['tabindex']	= $this->field()->getTabIndex();
		$args['echo'] 				= false;

		return tify_control_touch_time( $args );		
	}
}
