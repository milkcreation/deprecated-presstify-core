<?php
/**
 * @name Users
 * @desc Gestion des utilisateurs
 * @package presstiFy
 * @namespace tiFy\Core\Users
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Users;

use tiFy\Core\Users\Factory\TakeOver\TakeOver;

use tiFy\Core\Control\Control;

class Users extends \tiFy\App\Core
{
    /**
     * Liste des classes de rappel de prise de contrôle de compte utilisateur
     * @return \tiFy\Core\Users\Factory\TakeOver\TakeOver[]
     */
    private static $TakeOver = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Activation des permissions de prises de contrôle de comptes utilisateurs
        if ($take_over = self::tFyAppConfig('take_over')) :
            foreach ($take_over as $id => $attrs) :
                self::registerTakeOver($id, $attrs);
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
            'tiFy\Core\Users\Factory\TakeOver\ActionLink\ActionLink'
        );
        Control::register(
            'tiFy\Core\Users\Factory\TakeOver\AdminBar\AdminBar'
        );
        Control::register(
            'tiFy\Core\Users\Factory\TakeOver\SwitcherForm\SwitcherForm'
        );
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        do_action('tify_users_take_over_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $id Identifiant de qualification
     * @param arra $attrs Attributs de configuration
     *
     * @return \tiFy\Core\Users\Factory\TakeOver\TakeOver
     */
    public static function registerTakeOver($id, $attrs = [])
    {
        return self::$TakeOver[$id] = new TakeOver($id, $attrs);
    }

    /**
     * Récupération des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $id Identifiant de qualification
     *
     * @return \tiFy\Core\Users\Factory\TakeOver\TakeOver
     */
    public static function getTakeOver($id)
    {
        if (isset(self::$TakeOver[$id])) :
            return self::$TakeOver[$id];
        endif;
    }
}
