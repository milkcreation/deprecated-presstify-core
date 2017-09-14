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
    private static $Allowed         = [
        //'google',
        //'google-analytics',
        //'google-map',
        'recaptcha',
        'youtube',
        'vimeo',
        'facebook'
    ];
    
    /**
     * Liste des classes de rappel
     * @var object[]
     */
    private static $Api             = [];
    
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration
        if ($apis = self::tFyAppConfig()) :
            foreach ($apis as $api => $attrs) :
                self::register($api, $attrs);
            endforeach;
        endif;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     *
     * @param string $api Nom de l'api. Doit faire parti des api permises.
     * @param array $attrs Attributs de configuration
     *
     * @return null|object
     */
    public function register($api, $attrs = [])
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
    public static function get($api)
    {
        if(isset(self::$Api[$api])) :
            return self::$Api[$api];
        endif;
    }
}