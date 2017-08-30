<?php
/**
 * @name Update
 * @package PresstiFy
 * @subpackage Core
 * @namespace tiFy\Components\Update
 * @desc Mise à jour
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.1.369
 */
namespace tiFy\Core\Update;

class Update extends \tiFy\Environment\Core
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions              = array(
        'init'
    ); 
    
    /**
     * Classes de rappel
     * @var \tiFy\Core\Update\Factory[]
     */
    private static $Factories           = array();

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     * 
     * @return void
     */
    final public function init()
    {
        do_action('tify_update_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     * @param string $id Identifiant unique
     * @param mixed $attrs Attributs de mise à jour
     * 
     * @return object \tiFy\Core\Update\Factory
     */
    final public static function register($id, $attrs = array())
    {
        if( isset(self::$Factories[$id]))
            return;
        
        $path = self::getOverridePath("\\tiFy\\Core\\Update\\". self::sanitizeControllerName($id));
        $FactoryClass = self::getOverride('\tiFy\Core\Update\Factory', $path);
        
        return self::$Factories[$id] = new $FactoryClass($id, $attrs);
    }
}