<?php
/**
 * @deprecated
 */
namespace tiFy\Environment;

abstract class Addon extends \tiFy\App\Factory
{	
	/* = ARGUMENTS = */
	// Chemin vers la classe principale du plugin (requis)
	protected static $PluginPath			= null;
	
	// Identifiant de l'addon (requis)
	protected static $AddonID 				= null;
	
	// Options de l'addon
	protected static $Options				= array();
			
	/* = CONSTRUCTEUR */
	public function __construct()
	{
		parent::__construct();
		
		// Traitement de la configuration
		$pluginPath = static::$PluginPath;
		$options = $pluginPath::tFyAppConfig( static::$AddonID );
		
		static::$Options = ( is_bool( $options ) && $options ) ? static::defaultOptions() : wp_parse_args( (array) $options, static::defaultOptions() );
	}	
	
	/* = OPTIONS = */
	/** == Par défaut == **/
	public static function defaultOptions()
	{
		return array();
	}	
	
	/** == Récupération == **/
	final public static function getOption( $option = null, $default = null )
	{
		if( ! $option ) :
			return static::$Options;
		elseif( isset( static::$Options[$option] ) ) :
			return static::$Options[$option];
		else :
			return $default;
		endif;
	}
	
	/** == Définition d'une option == **/
	final public static function setOption( $option = null, $value = '' )
	{
		static::$Options[$option] = $value;
	}
	
	/** == Définition de plusieurs options == **/
	final public static function setOptions( $options = array() )
	{
		foreach( $options as $name => $value )
		static::$Options[$name] = $value;
	}
	
	/** == Destruction == **/
	final public static function unsetOption( $option = null )
	{
		if( ! $option ) :
			static::$Options = array();
		elseif( isset( static::$Options[$option] ) ) :
			unset( static::$Options[$option] );
		endif;
	}
}