<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Order;

use tiFy\Components\CustomColumns\Factory;

class Order extends Factory
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Ordre d\'affich.', 'tify'),
            'position' => 3
        ];
    }

    /**
     * Affichage du contenu de la colonne
     *
     * @param string $content Contenu de la colonne
     * @param string $column_name Identification de la colonne
     * @param int $term_id Identifiant du terme
     *
     * @return string
     */
    public function content($content, $column_name, $term_id)
    {
        echo (int)get_term_meta($term_id, '_order', true);
    }
}