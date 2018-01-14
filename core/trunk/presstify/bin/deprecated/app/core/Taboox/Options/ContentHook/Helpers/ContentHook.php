<?php

namespace tiFy\Core\Taboox\Options\ContentHook\Helpers;

use tiFy\Core\Taboox\Options\ContentHook\Admin\ContentHook as Admin;

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
        $this->appAddHelper('tify_taboox_content_hook_is', 'is');
        $this->appAddHelper('tify_taboox_content_hook_get', 'get');
    }

    /* = VÉRIFICATION (après init 25) = */
    public static function Is($hook_id, $post = 0)
    {
        // Bypass
        if (!$post = get_post($post)) :
            return;
        endif;

        return (isset(Admin::$Registered[$hook_id]['selected']) && (Admin::$Registered[$hook_id]['selected'] === $post->ID));
    }

    /* = Récupération = */
    public static function get($hook_id, $default = 0)
    {
        // Bypass
        if (!empty(Admin::$Registered[$hook_id]['selected'])) :
            return Admin::$Registered[$hook_id]['selected'];
        endif;

        return $default;
    }
}