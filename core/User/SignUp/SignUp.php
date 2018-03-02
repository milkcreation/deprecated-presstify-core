<?php
/**
 * @name SignUp
 * @desc Interface d'inscription d'un nouvel utilisateur
 * @package presstiFy
 * @namespace tiFy\Core\User\SignUp
 * @version 1.1
 * @subpackage Core
 * @since 1.2.571
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\SignUp;

final class SignUp extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('init', null, 0);
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisatio globale
     *
     * @return void
     */
    public function init()
    {
        do_action('tify_user_signup_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un formulaire d'inscription
     *
     * @param string $name Identifiant de qualification
     * @param array $attrs
     *
     * @return null|\tiFy\Core\User\SignUp\Factory
     */
    public static function register($name, $attrs = [])
    {
        if (!self::tFyAppHasContainer("tify.core.user.signup.{$name}")) :
            $factory = new Factory($name, $attrs);
            self::tFyAppShareContainer("tify.core.user.signup.{$name}", $factory);

            return $factory;
        endif;
    }

    /**
     * Récupération d'un formulaire d'inscription
     *
     * @param string $name Identifiant de qualification
     *
     * @return null|\tiFy\Core\User\SignUp\Factory
     */
    public static function get($name)
    {
        if (self::tFyAppHasContainer("tify.core.user.signup.{$name}")) :
            return self::tFyAppGetContainer("tify.core.user.signup.{$name}");
        endif;
    }
}