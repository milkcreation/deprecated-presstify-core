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

class Update extends \tiFy\App\Core
{
    /**
     * Classes de rappel
     * @var \tiFy\Core\Update\Factory[]
     */
    private static $Factories = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('init');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        foreach (self::tFyAppConfig() as $id => $attrs) :
            self::register($id, $attrs);
        endforeach;

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
     * @return \tiFy\Core\Update\Factory
     */
    final public static function register($id, $attrs = [])
    {
        if (isset(self::$Factories[$id])) :
            return;
        endif;

        $path = self::getOverridePath("\\tiFy\\Core\\Update\\" . self::sanitizeControllerName($id));
        $FactoryClass = self::getOverride('\tiFy\Core\Update\Factory', $path);

        return self::$Factories[$id] = new $FactoryClass($id, $attrs);
    }
}