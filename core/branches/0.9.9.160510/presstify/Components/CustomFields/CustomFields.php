<?php
namespace tiFy\Components\CustomFields;

use \tiFy\Environment\Component;

/** @Autoload */
final class CustomFields extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des Actions à déclencher
	protected 		$CallActions		= array(
		'current_screen'
	);

	/** == CONFIGURATION == **/
	public static 	$Factories			= array();
	
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	public function __construct()
	{
		parent::__construct();
		
		foreach( array( 'post', 'taxonomy' ) as $env ) :
			foreach( (array) self::getConfig( $env ) as $type => $custom_fields ) :
				foreach( (array) $custom_fields as $cfk => $cfv ) :
					if( is_int($cfk) ) :
						$ClassName 	= $cfv;
						$args		= array();
					else :
						$ClassName 	= $cfk;
						$args		= $cfv;
					endif;				
					
					if( \class_exists( $ClassName ) ) :						
						new $ClassName( $type, $args );
						continue;
					else :
						$ClassName = "\\tiFy\\Components\\CustomFields\\". ucfirst( $env ) ."\\". $ClassName ."\\". $ClassName;
						if( ! \class_exists( $ClassName ) )
							continue;
						new $ClassName( $type, $args );	
					endif;
				endforeach;
			endforeach;
		endforeach;
	}
}