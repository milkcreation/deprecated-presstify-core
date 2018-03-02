<?php

namespace tiFy\Core\Taboox\Taxonomy\Icon\Helpers;

class Icon extends \tiFy\App
{
    /**
     * Liste des attributs de configuration par défaut
     * @var array
     */
    public static $DefaultArgs = [];

    /**
     * Liste des attributs de configuration courant
     * @var array
     */
    protected static $Args = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Définition des attributs de configuration par défaut
        static::$DefaultArgs = [
            'name'  => '_icon',
            'dir'   => \tiFy\tiFy::$AbsDir . '/vendor/Assets/svg',
            'attrs' => [],
        ];

        // Déclaration des fonctions d'aide à la saisie
        $this->appAddHelper('tify_taboox_term_icon_get', 'get');
        $this->appAddHelper('tify_taboox_term_icon_display', 'display');
    }

    /**
     * Récupération de l'élément
     *
     * @param int|\WP_Term Identifiant de qualification ou objet Term Wordpress
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function get($term, $args = [])
    {
        $term_id = 0;
        if (is_int($term)) :
            $term_id = $term;
        elseif (is_object($term)) :
            $term_id = $term->term_id;
        endif;

        // Bypass
        if (!$term_id) :
            return;
        endif;

        static::$Args = wp_parse_args($args, static::$DefaultArgs);

        return get_term_meta($term_id, static::$Args['name'], true);
    }

    /**
     * Affichage de l'élément
     *
     * @param int|\WP_Term Identifiant de qualification ou objet Term Wordpress
     * @param array $args Liste des attributs de récupération
     *
     * @return array Liste des identifiants de qualification des posts en relation
     */
    public static function display($term, $args = [], $echo = true)
    {
        // Bypass
        if (!$icon = static::get($term, $args)) :
            return;
        endif;

        if (file_exists(static::$Args['dir'] . "/{$icon}") && ($content = file_get_contents(static::$Args['dir'] . "/{$icon}"))) :
        else :
            return;
        endif;

        $ext = pathinfo(static::$Args['dir'] . "/{$icon}", PATHINFO_EXTENSION);
        if (!in_array($ext, ['svg', 'png', 'jpg', 'jpeg'])) :
            return;
        endif;

        switch ($ext) :
            case 'svg' :
                $data = 'image/svg+xml';
                break;
            default :
                $data = 'image/' . $ext;
                break;
        endswitch;

        $output = "<img src=\"data:{$data};base64," . base64_encode($content) . "\" alt=\"{$icon}\"";
        foreach ((array)static::$Args['attrs'] as $k => $v) :
            $output .= " {$k}=\"{$v}\"";
        endforeach;
        $output .= "/>";

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}

