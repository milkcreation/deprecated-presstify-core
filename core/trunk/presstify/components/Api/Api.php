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

class Api extends \tiFy\App\Component
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
     * Liste des classes de rappel des API
     * @var object[]
     */
    private static $Factory         = [];
    
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
     * @param string $id Identifiant de qualification de l'Api. Doit faire parti des api permises.
     * @param array $attrs Attributs de configuration
     *
     * @return null|object
     */
    public static function register($id, $attrs = [])
    {
        // Bypass
        if (!in_array($id, self::$Allowed)) :
            return;
        endif;
            
        $classname = self::tFyAppUpperName($id);
        $class = "tiFy\\Components\\Api\\{$classname}\\{$classname}";

        if (class_exists($class)) :
            self::$Factory[$id] = self::tFyAppShareContainer($class, $class::create($attrs));
        endif;
    }
    
    /**
     * Récupération
     *
     * @param string $id Identifiant de qualification de l'Api. Doit faire parti des api permises.
     *
     * @return null|object
     */
    public static function get($id)
    {
        if(isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}