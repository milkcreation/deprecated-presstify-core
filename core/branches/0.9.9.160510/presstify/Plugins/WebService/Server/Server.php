<?php
/**
 * @see http://www.sitepoint.com/wordpress-json-rest-api/
 */
namespace tiFy\Plugins\WebService\Server;

class Server
{	
	/* = ARGUMENTS = */
	// Configuration
	private static $Config		= array();
			
	/* = CONSTRUCTEUR */
	public function __construct()
	{
		// Traitement de la configuration
		$config = \tiFy\Plugins\WebService\WebService::getConfig( 'server' );
		
		$defaults = array(
			'namespace'	=> 'tiFyAPI/v1'	
		);
		self::$Config = ( is_bool( $config ) ) ? $defaults : wp_parse_args( (array) $config, $defaults );					
		
		add_action( 'init', array( $this, 'init' ) );	
	}
	
	/* = = */
	/** == == **/
	public static function getConfig( $attr = null )
	{
		if( ! $attr ) :
			return self::$Config;
		elseif( isset( self::$Config[$attr] ) ) :
			return self::$Config[$attr];
		endif;
	}
	
	/* = DECLENCHEURS = */
	/** == Initialisation global == **/
	final public function init()
	{
		// Chargement des contrôleurs
		if ( ! class_exists( 'WP_REST_Controller' ) )
			require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-controller.php';
		if ( ! class_exists( 'WP_REST_Posts_Controller' ) )
			require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-posts-controller.php';
		
		// Instanciation des contrôleurs
		foreach( (array) self::getConfig( 'post_type' ) as $k => $v ) :
			if( is_int( $k ) ) :
				new Posts( $v );
			else :
				if( class_exists( $v ) ) :
					new $v( $k );
				else :
					new Posts( $k );
				endif;
			endif;
		endforeach;
	}	
}