<?php
namespace tiFy\Core\Router\Taboox\Helpers;

use tiFy\Core\Router\Router;

class ContentHook extends \tiFy\Core\Taboox\Helpers
{
    /**
     * Intitulés des prefixes des fonctions
     * @var string
     */
     protected $Prefix          = 'tify_router';

    /**
     * Identifiant des fonctions
     * @var string
     */
    //
    protected $ID               = 'content_hook';

    /**
     * Liste des methodes à translater en fonction d'aide à la saisie
     * @var array
     */
    protected $Helpers          = ['is', 'get'];

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