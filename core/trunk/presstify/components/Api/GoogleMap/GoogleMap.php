<?php
/**
 * @see https://github.com/egeloen/ivory-google-map
 */
namespace tiFy\Components\Api\GoogleMap;

use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;

class GoogleMap extends \Ivory\GoogleMap\Map
{
    /**
     * Helper
     */
    private static $Helper  = null;
    
    /**
     * Liste des cartes déclarées
     */
    private static $Maps    = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     * @param array $attrs
     */
    public static function tiFyApiInit( $attrs = array() )
    {
        if( ! empty( $attrs['key'] ) ) :
            self::$Helper = MapHelperBuilder::create()->build();
        else :
            self::$Helper = ApiHelperBuilder::create()
                ->setKey($attrs['key'])
                ->build();
        endif;
        
        return self::register();
    }
    
    /**
     * Déclaration d'une carte
     */
    public static function register( $id = null )
    {
        return self::$Maps[$id] = new static();
    }
    
    /**
     * Récupération d'une carte déclarée
     */
    public static function get( $id = null )
    {
        if( isset( self::$Maps[$id] ) )
            return self::$Maps[$id];
    }
    
    /**
     * 
     */
    public static function render( $id = null )
    {
        // Bypass
        if( ! $helper = self::$Helper )
            return;
        
        if( ! $map = self::get( $id ) )
            $map = self::register( $id );
            
        return $helper->render( array( $map ) );
    }   
}