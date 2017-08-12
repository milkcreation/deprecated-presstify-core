<?php
namespace tiFy;

final class tiFy
{
    /**
     * Version de PresstiFy
     * @var string
     */ 
    public static $Version            = '1.0.369';
    
    /**
     * Chemin absolu vers la racine de l'environnement
     * @var string
     */
    public static $AbsPath;
    
    /**
     * Chemin absolu vers la racine de presstiFy
     * @var string
     */
    public static $AbsDir;
    
    /**
     * Url absolue vers la racine la racine de presstiFy
     * @var string
     */
    public static $AbsUrl;
    
    /**
     * Paramètres de configuration des éléments de presstiFy
     * @var mixed
     */
    public static $Params;
    
    /**
     * Classe de chargement automatique
     */ 
    private static $ClassLoader        = null;
    
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct( $AbsPath = null )
    {
        if( defined( 'WP_INSTALLING' ) && ( WP_INSTALLING === true ) )
            return;

        global $tiFy;
        $tiFy = $this;
        
        // Définition des constantes d'environnement
        /// Attributs de configuration
        if( ! defined( 'TIFY_CONFIG_DIR' ) )
            define( 'TIFY_CONFIG_DIR', get_template_directory() .'/config' );
        if( ! defined( 'TIFY_CONFIG_EXT' ) )
            define( 'TIFY_CONFIG_EXT', 'yml' );        
        /// Répertoire des plugins
        if( ! defined( 'TIFY_PLUGINS_DIR' ) )
            define( 'TIFY_PLUGINS_DIR', dirname( __DIR__ ) .'/presstify-plugins' );
                    
        // Instanciation de l'environnement
        self::classLoad( 'tiFy\Environment', __DIR__ .'/env' );        
        
        // Instanciation des librairies proriétaires
        self::classLoad( 'tiFy\Lib', __DIR__ .'/lib', 'Autoload' );
  
        // Chargement des librairies tierces
        require_once __DIR__ .'/vendor/autoload.php';
        
        // Définition des chemins
        self::$AbsPath = ( $AbsPath ) ? $AbsPath : ABSPATH;
        self::$AbsDir = dirname( __FILE__ );
        self::$AbsUrl = \tiFy\Lib\File::getFilenameUrl( self::$AbsDir, self::$AbsPath );

        // Instanciation du coeur
        self::classLoad( 'tiFy\Core', __DIR__ .'/core', 'Autoload' );
        
        // Instanciation des composants
        self::classLoad( 'tiFy\Components', __DIR__ .'/components', 'Autoload' );

        // Instanciation des fonctions d'aides au développement
        self::classLoad( 'tiFy\Helpers', __DIR__ .'/helpers', 'Autoload' );      
        
        // Chargement des Must-Use    
        $this->loadMustUse();
        
        // Chargement des traductions
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après le chargement des plugins
     * 
     * @return bool
     */
    public function plugins_loaded()
    {
        // Chargement des traductions
        return load_textdomain( 'tify', self::$AbsDir .'/languages/tify-' . get_locale() . '.mo' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Chargement automatique des classes
     */
    public static function classLoad( $namespace, $base_dir, $bootstrap = null )
    {
        if( is_null( self::$ClassLoader ) ) :
            require_once __DIR__ .'/lib/ClassLoader/Psr4ClassLoader.php';
            self::$ClassLoader = new \Psr4ClassLoader;
        endif;
        
        if( ! $base_dir )
            $base_dir = dirname( __FILE__ );
        
        self::$ClassLoader->addNamespace( $namespace, $base_dir, false );
        self::$ClassLoader->register();
            
        if( $bootstrap ) :
            $class_name = "\\". ltrim( $namespace, '\\' ) ."\\". $bootstrap;
            if( class_exists( $class_name ) ) :
                new $class_name;
            endif;
        endif;
    }
    
    /**
     * Récupération d'une liste de paramètres
     */
    public static function getParams($type = null)
    {
        if(is_null($type))
            return self::$Params;
        $type = strtolower($type);
        if(isset(self::$Params[$type]))
            return self::$Params[$type];
    }
    
    /**
     * Récupération des attributs de configuration
     * @param string $index
     * @param string $default
     * 
     * @return mixed | $default
     */
    public static function getConfig( $index, $default = '' )
    {
        if( isset( self::$Params['config'][$index] ) ) :
            return self::$Params['config'][$index];
        endif;
        
        return $default;
    }
    
    /**
     * Récupération de la liste des plugins
     */
    public static function getPlugins()
    {
        if( ! empty( self::$Params['plugins'] ) )
            return array_keys( self::$Params['plugins'] );
    }
    
    /**
     * Récupération de la liste des sets
     */
    public static function getSets()
    {
        if( ! empty( self::$Params['set'] ) )
            return array_keys( self::$Params['plugins'] );
    }
    
    /**
     * Chargement des must-use
     */
    protected function loadMustUse()
    {
        foreach( glob( TIFY_PLUGINS_DIR .'/*', GLOB_ONLYDIR ) as $plugin_dir ) :
            if( ! file_exists( $plugin_dir .'/MustUse' ) )
                continue;
            if ( ! $dh = opendir( $plugin_dir .'/MustUse' ) )
                continue;
            while ( ( $file = readdir( $dh ) ) !== false ) :
                if ( substr( $file, -4 ) == '.php' ) :
                    include_once( $plugin_dir .'/MustUse/' . $file );
                endif;
            endwhile;
        endforeach;
    }
}
