<?php
namespace tiFy\Core\Taboox\PostType\TaxonomySelect\Admin;

use tiFy\Lib\Walkers\Taxonomy_RadioList;
use tiFy\Lib\Walkers\Taxonomy_CheckboxList;

class TaxonomySelect extends \tiFy\Core\Taboox\PostType\Admin
{
    // Instance
    private static $Instance;

    /**
     * DECLENCHEURS
     */
    /**
     * Chargement de la page courante
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        // Traitement des arguments
        $this->args = wp_parse_args($this->args, [
            'id'               => 'tify_taboox_taxonomy_select-' . ++self::$Instance,
            'selector'         => 'checkbox',
            'taxonomy'         => '',
            'show_option_none' => __('Aucun', 'tify'),
            'col'              => 4
        ]);
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('tiFyTabooxPostTaxonomySelectAdmin', self::tFyAppUrl(get_class()) . '/TaxonomySelect.css');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     *
     * @param \WP_Post $post
     *
     * @return string
     */
    public function form($post)
    {
        extract($this->args);

        $taxonomies = get_terms([
            'taxonomy'   => $taxonomy,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key'     => '_order',
                    'value'   => 0,
                    'compare' => '>=',
                    'type'    => 'NUMERIC'
                ],
                [
                    'key'     => '_order',
                    'compare' => 'NOT EXISTS'
                ]
            ],
            'orderby'    => 'meta_value_num',
            'order'      => 'ASC',
            'get'        => 'all'
        ]);

        if (is_wp_error($taxonomies)) {
            return;
        }

        if ($selector === 'radio') :
            $walker = new Taxonomy_RadioList;
        else :
            $walker = new Taxonomy_CheckboxList;
        endif;

        $args = [
            "taxonomy"  => $taxonomy,
            "disabled"  => false,
            "list_only" => false
        ];
        $args['selected_cats'] = wp_get_object_terms($post->ID, $taxonomy, array_merge($args, ['fields' => 'ids']));

        $output = "";
        $output .= "<div id=\"{$id}\" class=\"tify_taboox_taxonomy_select tify_taboox_taxonomy_select-{$taxonomy}\">\n";
        $output .= "\t<ul class=\"list-{$col}-items-by-row\">\n";
        $output .= call_user_func_array([$walker, 'walk'], [$taxonomies, 0, $args]);
        $output .= "\t</ul>\n";
        $output .= "</div>\n";

        echo $output;
    }
}