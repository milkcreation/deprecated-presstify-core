<?php
namespace tiFy\Components\CustomColumns;

use \tiFy\Environment\Component;

/** @Autoload */
final class CustomColumns extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des Actions à déclencher
	protected 		$CallActions		= array(
		'init',
		'current_screen'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'admin_init'	=> 99
	);
	
	/** == CONFIGURATION == **/
	public static 	$Factories			= array();
	
	/* = ACTIONS = */
	/** == Initialisation globale == **/
	public function __construct()
	{
		parent::__construct();
		
		foreach( array( 'post_type', 'taxonomy' ) as $env ) :
			
			foreach( (array) self::getConfig( $env ) as $type => $custom_columns ) :
				foreach( (array) $custom_columns as $Classname => $args ) :
					$args['env'] = $env; $args['type'] = $type;
					if( \class_exists( $Classname ) ) :						
						self::$Factories[$env][$type][] = new $Classname( $args );
						continue;
					else :
						$_env =  join( '', array_map( 'ucfirst', preg_split( '/_/', $env ) ) );
						
						$Classname = "\\tiFy\\Components\\CustomColumns\\{$_env}\\{$Classname}\\{$Classname}";
						if( ! \class_exists( $Classname ) )
							continue;
						self::$Factories[$env][$type][] = new $Classname( $args );	
					endif;
				endforeach;
			endforeach;
		endforeach;
	}
		
	/** == == **/
	final public function current_screen( $current_screen )
	{			
		// Bypass		
		switch( $current_screen->base ) :
			default:
				return;
				break;
			case 'edit' :
				if( ! isset( self::$Factories['post_type'][$current_screen->post_type] ) )
					return;
				foreach( (array) self::$Factories['post_type'][$current_screen->post_type] as $FactoryClass ) :
					call_user_func( array( $FactoryClass, 'current_screen' ), get_current_screen() );
					add_action( 'admin_enqueue_scripts', array( $FactoryClass, 'admin_enqueue_scripts' ) );
				endforeach;
				break;
			case 'edit-tags' :
				if( ! isset( self::$Factories['taxonomy'][$current_screen->taxonomy] ) )
					return;
				foreach( (array) self::$Factories['taxonomy'][$current_screen->taxonomy] as $FactoryClass ) :
					call_user_func( array( $FactoryClass, 'current_screen' ), get_current_screen() );
					add_action( 'admin_enqueue_scripts', array( $FactoryClass, 'admin_enqueue_scripts' ) );
				endforeach;
				break;
		endswitch;
			
	}
}