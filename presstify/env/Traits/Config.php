<?php
namespace tiFy\Environment\Traits;

use \tiFy\tiFy;

trait Config
{
    /**
     * Nom de la classe
     */
    private $_ConfName;
    
    /**
     * Nom court de la classe
     */
	private $_ConfShortName;
	
	/**
	 * Espace de Nom
	 */
    private $_ConfNamespaceName;	
	
    // Namespace
    protected $Namespace;
    
    // 
    protected $SubDir;
    
    // Schema
    protected $Schema                      = false;
    
    // Configuration par défaut
    protected static $DefaultConfig;
    
    // Configuration
    protected static $_ConfParams;

    /* = CONSTRUCTEUR = */
    public function __construct()
    {        
        // Définition des Arguments
        $this->initNames();
        $this->initConfig();
        
        if( $this->Schema )
            $this->initSchema();
    }
    
    /** == Définition des attributs de noms d'appel == **/
    private function initNames()
    {
        $reflection = new \ReflectionClass( get_called_class() );
        $this->_ConfName = $reflection->getName();
        $this->_ConfShortName = $reflection->getShortName();
        $this->_ConfNamespaceName = $reflection->getNamespaceName();
    }
    
    /** == Définition de la configuration == **/
    protected function initConfig()
    {
        $filename = self::getDirname() .'/config/config.yml';
        $class = get_called_class();

        $defaults = self::$DefaultConfig[$this->_ConfName] = ( file_exists( $filename ) ) ? \tiFy\Core\Params::parseAndEval( $filename ) : array();    
        
        // Configuration personnalisée
        if( ! empty( tiFy::$Params[$this->SubDir][$this->_ConfName] ) ) :
            $conf = self::$_ConfParams[$this->_ConfName] = wp_parse_args( tiFy::$Params[$this->SubDir][$this->_ConfName], $defaults );           
        elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', $this->_ConfName )  && ! empty( tiFy::$Params[$this->SubDir][$this->_ConfShortName] ) ) :            
            $conf = self::$_ConfParams[$this->_ConfName] = wp_parse_args( tiFy::$Params[$this->SubDir][$this->_ConfShortName], $defaults );                        
        else :
            $conf = self::$_ConfParams[$this->_ConfName] = $defaults;
        endif;
        
        // Surchage de la configuration
        if( ( $overrideNamespace = self::getOverrideNamespace() ) && preg_match( "/^\\\?tiFy\\\((Components|Core|Plugins|Set)\\\.*)/", $this->_ConfNamespaceName, $matches ) ) :
            $overrideClass = $overrideNamespace .'\\'. $matches[1] .'\\Config' ; $abstractClass = '\\tiFy\\Environment\\Config'; 
            if( class_exists( $overrideClass ) && is_subclass_of( $overrideClass, $abstractClass ) ) :
                $overrideConf = new $overrideClass;
                $conf = self::$_ConfParams[$this->_ConfName] = wp_parse_args( $overrideConf->get(), $conf );
            endif;                
        endif;
        
        return $conf;
    }
    
    /** == Définition du schema == **/
    protected function initSchema()
    {
        $dirname    = self::getDirname() .'/config/';
        $schema     = array();
        
        // Récupération du paramétrage natif
        $_dir = @ opendir( $dirname );
        if( $_dir ) :
            while ( ( $file = readdir( $_dir ) ) !== false ) :
                if ( substr( $file, 0, 1 ) == '.' )
                        continue;
                $basename = basename( $file, ".yml" );
                if( $basename !== 'schema' )
                    continue;           
                
                $schema += \tiFy\Core\Params::_parseFilename( "{$dirname}/{$file}", array(), 'yml', array( 'eval' => true ) );
            endwhile;
            closedir( $_dir );
        endif;
        
        // Récupération du paramétrage personnalisé
        if( ! empty( tiFy::$Params[$this->SubDir][$this->_ConfName]['schema'] ) ) :
            $schema = wp_parse_args( tiFy::$Params[$this->SubDir][$this->_ConfName]['schema'], $schema ); 
        elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', $this->_ConfName )  && ! empty( tiFy::$Params[$this->SubDir][$this->_ConfShortName]['schema'] ) ) :
            $schema = wp_parse_args( tiFy::$Params[$this->SubDir][$this->_ConfShortName]['schema'], $schema ); 
        endif;
    
        // Traitement du parametrage
        foreach( (array) $schema as $id => $entity ) :
            /// Classe de rappel des données en base
            if( isset( $entity['Db'] ) ) :
                \tiFy\Core\Db\Db::Register( $id, $entity['Db'] );
            endif;
            
            /// Classe de rappel des intitulés
            \tiFy\Core\Labels\Labels::Register( $id, ( isset( $entity['Labels'] ) ? $entity['Labels'] : array() ) );
            
            /// Gabarits de l'interface d'administration
            if( isset( $entity['Admin'] ) ) :
                foreach( (array) $entity['Admin'] as $i => $tpl ) :
                    if( ! isset( $tpl['db'] ) )
                        $tpl['db'] = $id;
                    if( ! isset( $tpl['labels'] ) )
                        $tpl['labels'] = $id;
                        
                    \tiFy\Core\Templates\Templates::Register( $i, $tpl, 'admin' );
                endforeach;
            endif;
            
            /// Gabarits de l'interface utilisateur
            if( isset( $entity['Front'] ) ) :
                foreach( (array) $entity['Front'] as $i => $tpl ) :
                    if( ! isset( $tpl['db'] ) )
                        $tpl['db'] = $id;
                    if( ! isset( $tpl['labels'] ) )
                        $tpl['labels'] = $id;
                    
                    \tiFy\Core\Templates\Templates::Register( $i, $tpl, 'front' );
                endforeach;
            endif;          
        endforeach;
    }   
    
    /**
     * Récupération de la configuration
     */
    public static function getConfig( $index = null, $class = null )
    {
        if( ! $class )
            $class = get_called_class();
        
        if( ! $index ) :
            return isset( static::$_ConfParams[$class] ) ? static::$_ConfParams[$class] : array();
        elseif( isset( static::$_ConfParams[$class][$index] ) ) :
            return static::$_ConfParams[$class][$index];
        endif;
    }
    
    /**
     * Récupération de la configuration par défaut
     */
    public static function getDefaultConfig( $index = null )
    {
        $class = get_called_class();
        
        if( ! $index ) :
            return static::$DefaultConfig[$class];
        elseif( isset( static::$DefaultConfig[$class][$index] ) ) :
            return static::$DefaultConfig[$class][$index];
        endif;
    }
    
    /**
     * Définition d'un attribut de configuration
     */
    public static function setConfig( $index, $value )
    {
        $class = get_called_class();
        
        return static::$_ConfParams[$class][$index] = $value;
    }
}