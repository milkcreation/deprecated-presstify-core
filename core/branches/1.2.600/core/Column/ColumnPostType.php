<?php

namespace tiFy\Core\Column;

class ColumnPostType extends ColumnFactory
{
    /**
     * Type d'objet
     * @var string
     */
    protected $ObjectType = 'post_type';

    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column_name Identifiant de qualification de la colonne
     * @param int $post_id Identifiant du post
     *
     * @return string
     */
    public function content($column_name, $post_id)
    {
        _e('Pas de données à afficher', 'tify');
    }
}