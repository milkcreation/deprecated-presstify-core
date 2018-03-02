<?php
namespace tiFy\Set\ContactToggle;

use tiFy\Core\Control\Control;

class ContactToggle extends \tiFy\App\Set
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
        $this->appAddAction('tify_control_register');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Déclaration de Control
     */
    final public function tify_control_register()
    {
        Control::register(
            'ContactToggle',
            'tiFy\Set\ContactToggle\Control\ContactToggle'
        );
    }
}