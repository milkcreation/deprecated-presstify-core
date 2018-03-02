<?php

namespace tiFy\Core\Router\Taboox\Helpers;

use tiFy\Core\Router\Router;

class ContentHook extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des fonctions d'aide à la saisie
        $this->appAddHelper('tify_router_content_hook_is', 'is');
        $this->appAddHelper('tify_router_content_hook_is', 'get');
    }

    /**
     * Vérification d'existance (fonctionnel après init 25)
     *
     * @param $hook_id
     * @param int $post
     *
     * @return bool|void
     */
    public static function is($hook_id, $post = 0)
    {
        // Bypass
        if (! $post = get_post($post))
            return;
        if(! $router = Router::get($hook_id))
            return;

        return ($router->getSelected() === $post->ID);
    }

    /**
     * Récupération
     *
     * @param $hook_id
     * @param int $default
     *
     * @return int
     */
    public static function get($hook_id, $default = 0)
    {
        if(! $router = Router::get($hook_id))
            return $default;

        return $router->getSelected();
    }
}