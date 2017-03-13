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
		'admin_init',
		'current_screen'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'admin_init'	=> 99
	);
	
	/** == CONFIGURATION == **/
	// Liste des colonnes  personnalisée
	public static 	$CustomColumns		= array();
	
	// Classes de rappel
	public static 	$Factories			= array();
	
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	public function admin_init()
	{
		// Récupération des colonnes personnalisées déclarées dans les fichiers de configuration		
		foreach( array( 'post_type', 'taxonomy' ) as $env ) :
			foreach( (array) self::getConfig( $env ) as $type => $custom_columns ) :
				foreach( (array) $custom_columns as $cb => $args ) :					
					self::Register( $cb, $args, $env, $type );			
				endforeach;
			endforeach;
		endforeach;
		
		// Récupérations des colonnes personnalisées déclarées en action
		do_action( 'tify_custom_columns_register' );
		
		// Instanciation des colonnes personnalisées déclarées
		foreach( array( 'post_type', 'taxonomy' ) as $env ) :
			if( ! isset( self::$CustomColumns[$env] ) )
				continue;
			foreach( (array) self::$CustomColumns[$env] as $type => $custom_columns ) :
				foreach( (array) $custom_columns as $cb => $args ) :
				    $FactoryClass = new $cb( $args );
					self::$Factories[$env][$type][] = $FactoryClass;	
			        call_user_func( array( $FactoryClass, 'admin_init' ) );
				endforeach;
			endforeach;
		endforeach;
	}
		
	/** == Affichage de l'écran courant == **/
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
	
	/** == Déclaration d'un colonne personnalisée == **/
	public static function Register( $cb, $args = array(), $env, $type )
	{
		$args['env'] = $env; $args['type'] = $type;

		if( \class_exists( $cb ) ) :
			self::$CustomColumns[$env][$type][$cb] = $args;
		else :
			$_env =  join( '', array_map( 'ucfirst', preg_split( '/_/', $env ) ) );			
			$tiFyCb = "\\tiFy\\Components\\CustomColumns\\{$_env}\\{$cb}\\{$cb}";
			if( \class_exists( $tiFyCb ) ) :
				self::$CustomColumns[$env][$type][$tiFyCb] = $args;	
			endif;
		endif;
	}
}