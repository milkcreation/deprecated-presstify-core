<?php
/**
 * @name TakeOver
 * @desc Prise de controle de compte utilisateur
 * @package presstiFy
 * @namespace tiFy\Core\User\TakeOver
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\TakeOver;

use tiFy\Core\Control\Control;

class TakeOver extends \tiFy\App
{
    /**
     * Liste des classes de rappel de prise de contrôle de compte utilisateur
     * @return \tiFy\Core\User\TakeOver\Factory[]
     */
    private static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Activation des permissions de prises de contrôle de comptes utilisateurs
        if ($take_over = self::tFyAppConfig('take_over', [], 'tiFy\Core\User\User')) :
            foreach ($take_over as $id => $attrs) :
                self::register($id, $attrs);
            endforeach;
        endif;

        // Déclaration des événements de déclenchement
        $this->tFyAppAddAction('tify_control_register');
        $this->tFyAppAddAction('init');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration de controleur
     *
     * @return void
     */
    final public function tify_control_register()
    {
        Control::register(
            'TakeOverActionLink',
            'tiFy\Core\User\TakeOver\ActionLink\ActionLink'
        );
        Control::register(
            'TakeOverAdminBar',
            'tiFy\Core\User\TakeOver\AdminBar\AdminBar'
        );
        Control::register(
            'TakeOverSwitcherForm',
            'tiFy\Core\User\TakeOver\SwitcherForm\SwitcherForm'
        );
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        do_action('tify_user_take_over_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return \tiFy\Core\User\TakeOver\Factory
     */
    public static function register($id, $attrs = [])
    {
        return self::$Factory[$id] = new Factory($id, $attrs);
    }

    /**
     * Récupération des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $id Identifiant de qualification
     *
     * @return \tiFy\Core\User\TakeOver\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}
