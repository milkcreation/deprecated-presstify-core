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
     * @param string $name Identifiant de qualification de l'Api. Doit faire parti des api permises.
     * @param array $attrs Attributs de configuration
     *
     * @return null|object
     */
    public static function register($name, $attrs = [])
    {
        // Bypass
        if (!in_array($name, self::$Allowed)) :
            return;
        endif;
            
        $classname = self::tFyAppUpperName($name);
        $class = "tiFy\\Components\\Api\\{$classname}\\{$classname}";

        if (class_exists($class)) :
            $factory = $class::create($attrs);
            self::tFyAppShareContainer($class, $factory);

            return $factory;
        endif;
    }
    
    /**
     * Récupération
     *
     * @param string $id Identifiant de qualification de l'Api. Doit faire parti des api permises.
     *
     * @return null|object
     */
    public static function get($name)
    {
        $Name = self::tFyAppUpperName($name);
        if (self::tFyAppHasContainer("tiFy\\Components\\Api\\{$Name}\\{$Name}")) :
            return self::tFyAppGetContainer("tiFy\\Components\\Api\\{$Name}\\{$Name}");
        endif;
    }
}