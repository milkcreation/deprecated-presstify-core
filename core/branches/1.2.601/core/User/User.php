<?php
/**
 * @name User
 * @desc Gestion des utilisateurs
 * @package presstiFy
 * @namespace tiFy\Core\User
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User;

use tiFy\Core\User\Login\Login;
use tiFy\Core\User\Role\Role;
use tiFy\Core\User\Session\Session;
use tiFy\Core\User\SignUp\SignUp;
use tiFy\Core\User\TakeOver\TakeOver;

class User extends \tiFy\App\Core
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Chargement des controleurs
        new Login;
        new Role;
        new Session;
        new SignUp;
        new TakeOver;
    }
}
