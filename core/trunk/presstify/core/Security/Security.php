<?php
namespace tiFy\Core\Security;

class Security extends \tiFy\Environment\Core
{
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
        new LoginRedirect;
    }
}