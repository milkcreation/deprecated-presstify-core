<?php

namespace tiFy\Core\Taboox\PostType\CustomHeader\Helpers;

class CustomHeader extends \tiFy\App
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_custom_header_get', 'get');

        // Déclaration d'événements
        $this->appAddFilter('theme_mod_header_image');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Court-circuitage de l'url de l'image d'entête
     *
     * @param string $url
     *
     * @return string
     */
    final public function theme_mod_header_image($url)
    {
        return static::get($url);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'url de l'image d'entête
     *
     * @param null|string $url
     *
     * @return string
     */
    public static function get($url = null)
    {
        if ($global = get_option('custom_header', false)) :
            if (is_numeric($global) && ($image = wp_get_attachment_image_src($global, 'full'))) :
                $url = $image[0];
            elseif (is_string($global)) :
                $url = $global;
            endif;
        endif;

        if (is_home() && get_option('page_for_posts')) :
            $header = get_post_meta(get_option('page_for_posts'), '_custom_header', true);
            if ($header && is_numeric($header) && ($image = wp_get_attachment_image_src($header, 'full'))) :
                $url = $image[0];
            elseif ($header && is_string($header)) :
                $url = $header;
            endif;
        else :
            $header = get_post_meta(get_the_ID(), '_custom_header', true);
            if ($header && is_numeric($header) && ($image = wp_get_attachment_image_src($header, 'full'))) :
                $url = $image[0];
            elseif ($header && is_string($header)) :
                $url = $header;
            endif;
        endif;

        return $url;
    }
}