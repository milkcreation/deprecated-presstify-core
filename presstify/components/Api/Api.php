<?php
/**
 * @name API
 * @package PresstiFy
 * @subpackage Components
 * @namespace tiFy\Components\Api
 * @desc Gestion d'API
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.369
 */
namespace tiFy\Components\Api;

class Api extends \tiFy\Environment\Component
{
    /**
     * Liste des api autorisées
     * @var string[]
     */
    private static $Allowed         = array(
        //'google',
        //'google-analytics',
        //'google-map',
        'recaptcha',
        'youtube',
        'vimeo',
        'facebook'
    );
    
    /**
     * Liste des classes de rappel
     * @var object[]
     */
    private static $Api             = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration
        if( $apis = self::tFyAppConfig() ) :
            foreach( $apis as $api => $attrs ) :
                self::register( $api, $attrs );
            endforeach;
        endif;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     */
    public function register( $api, $attrs = array() )
    {
        // Bypass
        if( ! in_array( $api, self::$Allowed ) )
            return;
            
        $ClassName = self::sanitizeControllerName($api);
        $ClassName = "tiFy\\Components\\Api\\{$ClassName}\\". $ClassName;

        return self::$Api[$api] = $ClassName::tiFyApiInit($attrs);
    }
    
    /**
     * Récupération
     */
    public static function get( $api )
    {
        if( isset( self::$Api[$api] ) )
            return self::$Api[$api];
    }
}