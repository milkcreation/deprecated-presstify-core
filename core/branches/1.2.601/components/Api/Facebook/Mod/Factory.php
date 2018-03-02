<?php

namespace tiFy\Components\Api\Facebook\Mod;

class Factory extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($callable = null)
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('tify_api_fb', (is_callable($callable) ? $callable : 'handler'), 10, 2);
    }
}