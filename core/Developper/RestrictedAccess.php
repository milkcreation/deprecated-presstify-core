<?php

namespace tiFy\Components\DevTools\Tools\RestrictedAccess;

class RestrictedAccess extends \tiFy\App
{
    private $Options = [];

    /* = CONSTRUCTEUR = */
    public function __construct($opts = [])
    {
        parent::__construct();

        $this->Options = wp_parse_args(
            \tiFy\Components\DevTools\DevTools::getConfig('restricted_access'),
            \tiFy\Components\DevTools\DevTools::getDefaultConfig('restricted_access')
        );
        \tiFy\Components\DevTools\DevTools::setConfig('restricted_access', $this->Options);

        // Déclaration des événements
        $this->appAddAction('template_redirect');
    }

    /* = = */
    public function template_redirect()
    {
        // Vérification des habilitations d'accès au site
        if (!$this->Active()) {
            return;
        }

        // Vérification des habilitations d'accès au site
        if ($this->Allowed()) {
            return;
        }

        extract($this->Options);

        wp_die($message, $title, $http_code);
    }

    /* = = */
    private function Active()
    {
        return $this->Options['active'];
    }

    /* = = */
    private function Allowed()
    {
        if (is_user_logged_in()) {
            return true;
        }

        return false;
    }
}