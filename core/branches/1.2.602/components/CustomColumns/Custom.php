<?php
namespace tiFy\Components\CustomColumns;

class Custom extends \tiFy\Components\CustomColumns\Factory
{
    /**
     * Affichage du contenu de la colonne
     *
     * @param string $content Contenu de la colonne
     * @param string $column_name Identification de la colonne
     * @param object $item Attributs de l'objet courant
     *
     * @return string
     */
    public function content($output, $column_name, $item)
    {
        _e('Pas de données à afficher', 'tify');
    }
}