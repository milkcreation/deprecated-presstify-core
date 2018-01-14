<?php

namespace tiFy\Core\Taboox\Options\MiscLinks\Helpers;

class MiscLinks extends \tiFy\App
{
    /**
     * Liste des attributs de configuration par défaut
     * @var array
     */
    public static $DefaultAttrs = [
        'name'    => 'tify_taboox_misclinks',
        'title'   => true,
        'caption' => false,
        'image'   => false,
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_misclinks_has', 'has');
        $this->appAddHelper('tify_taboox_misclinks_get', 'get');
        $this->appAddHelper('tify_taboox_misclinks_display', 'display');
    }

    /**
     * Vérification d'existance d'éléments
     *
     * @param array $args Liste des attributs de récupération
     *
     * @return bool
     */
    public static function has($args = [])
    {
        return self::get($args);
    }

    /**
     * Récupération de la liste des éléments
     *
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($args = [])
    {
        $args = wp_parse_args($args, self::$DefaultAttrs);

        return get_option($args['name'], false);
    }

    /**
     * Affichage de la liste des éléments
     * @maintenance Passage en template
     *
     * @param array $args Liste des attributs de récupération
     * @param bool $echo Activation de l'affichage
     *
     * @return string Gabarit d'affichage de la liste des éléments.
     */
    public static function display($args = [], $echo = true)
    {
        // Bypass
        if (!$links = self::Get($args)) :
            return;
        endif;

        $output = "<ul class=\"tify_taboox_misclinks\">\n";

        foreach ((array)$links as $link) :
            $url = (!empty($link['url'])) ? $link['url'] : '#';
            $title = (!empty($link['title'])) ? sprintf(__('Lien vers %s', 'tify'),
                $link['title']) : (!empty($link['url']) ? sprintf(__('Lien vers %s', 'tify'), $link['url']) : '');

            $output .= "\t<li>\n";
            $output .= "\t\t<a href=\"{$url}\"";
            if ($title) {
                $output .= " title=\"$title\"";
            }
            $output .= ">\n";

            if (!empty($link['image'])) {
                $output .= wp_get_attachment_image($link['image'], 'thumbnail');
            }

            if (!empty($link['caption'])) {
                $output .= "\t\t\t<div class=\"tify_taboox_misclinks_caption\">{$link['caption']}</div>";
            }

            $output .= "\t\t</a>\n";
            $output .= "\t</li>\n";
        endforeach;

        $output .= "</ul>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}