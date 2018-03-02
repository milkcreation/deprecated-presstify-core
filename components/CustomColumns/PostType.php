<?php
namespace tiFy\Components\CustomColumns;

class PostType extends \tiFy\Components\CustomColumns\Factory
{
    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column Identification de la colonne
     * @param int $post_id Identifiant du post
     *
     * @return string
     */
    public function content($column, $post_id)
    {
        _e('Pas de données à afficher', 'tify');
    }
}