<?php 
namespace tiFy\Core\Ui\Admin\Templates\ListTable\Traits;

trait Views
{
    /**
     * Traitement des vues filtrées
     *
     * @param array $views Liste des vues filtrées
     *
     * @return array
     */
    public function parseViews($views = [])
    {
        $_views = [];
        foreach($views as $id => $attrs) :
            if(is_string($attrs)) :
                $_views[$id] = $attrs;
            else :
                if ($link = $this->parseView($attrs, [])) :
                    $_views[$id] = $link;
                endif;
            endif;
        endforeach;

        return $_views;
    }

    /**
     * Traitement d'une vue filtrée
     *
     * @param string $view_name Identifiant de qualification de la vue filtrée
     * @param array $custom_attrs Liste des attributs de configuration personnalisé de la vue filtrée
     *
     * @return string
     */
    public function parseView($view_name, $custom_attrs = [])
    {
        $defaults = [
            'content'           => $view_name,
            'title'             => '',
            'class'             => '',
            'attrs'             => [],
            'href'              => '',
            'query_args'        => [],
            'remove_query_args' => [], //['action', 'action2', 'filter_action', '_wp_nonce', '_wp_referer'],
            'count_items'       => 0,
            'current'           => false,
            'hide_empty'        => false,
            'show_count'        => false
        ];

        if (method_exists($this, "get_view_attrs_{$view_name}")) :
            $args = call_user_func([$this, "get_view_attrs_{$view_name}"], $custom_attrs);
            $args = \wp_parse_args($args, $defaults);
        else :
            $args = \wp_parse_args($custom_attrs, $defaults);
        endif;

        /**
         * @var string $content Contenu du lien de vue filtrée (chaîne de caractère ou éléments HTML)
         * @var string $title Intitulé de l'attribut title de la balise du lien de vue filtrée
         * @var string $class Classes CSS de l'attribut class de la balise du lien de vue filtrée
         * @var array $attrs Liste des attributs complémentaires de la balise du lien de vue filtrée
         * @var string $href Url de l'attribut href de la balise du lien de vue filtrée
         * @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien de vue filtrée
         * @var array $remove_query_args Tableau indexé des arguments supprimés de l'url de requête du lien de vue filtrée
         * @var int $count_items Nombre d'élément correspondant à la vue filtrée
         * @var bool $current Définie si la vue courante correspond à la vue filtrée
         * @var bool $hide_empty Masque le lien si aucun élément ne correspond à la vue filtrée
         * @var bool|string $show_count Affiche le nombre d'éléments correspondant dans le lien de la vue filtrée false|true|'(%d)' où %d correspond au nombre d'éléments
         */
        extract($args);

        if ($args['hide_empty'] && !$args['count_items']) :
            return '';
        endif;

        // Traitement de l'url
        if (!$href) :
            $href = $this->getParam('base_uri');
        endif;

        // Arguments de requête passés dans l'url
        if($query_args) :
            $href = \add_query_arg($query_args, $href);
        endif;

        // Arguments de requête supprimés de l'url
        if($remove_query_args) :
             $href = \remove_query_arg($remove_query_args, $href);
        endif;

        // Traitement des classes
        $classes = [];
        if ($current) :
            $classes[] = 'current';
        endif;
        if ($class) :
            $classes[] = $class;
        endif;
        if ($classes) :
            if (!isset($attrs['class'])) :
                $attrs['class'] = '';
            endif;
            $attrs['class'] .= join(' ', $classes);
        endif;

        // Traitement du titre du lien
        if ($title) :
            $attrs['title'] = esc_attr($title);
        endif;

        $output  = "";
        $output .= "<a href=\"{$href}\"";
        if($attrs) :
            foreach($attrs as $i => $j) :
                $output .= " {$i}=\"{$j}\"";
            endforeach;
        endif;
        $output .= ">{$content}";
        if ($show_count) :
            $output .= " <span class=\"count\">({$count_items})</span>";
        endif;
        $output .= "</a>";
        
        return $output;
    }
}