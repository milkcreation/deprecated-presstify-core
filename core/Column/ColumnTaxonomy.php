<?php

namespace tiFy\Core\Column;

class ColumnTaxonomy extends ColumnFactory
{
    /**
     * Type d'objet
     * @var string
     */
    protected $ObjectType = 'taxonomy';

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
        _e('Pas de données à afficher', 'tify');
    }
}