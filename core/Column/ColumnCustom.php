<?php

namespace tiFy\Core\Column;

class ColumnCustom extends ColumnFactory
{
    /**
     * Type d'objet
     * @var string
     */
    protected $ObjectType = 'custom';

    /**
     * Affichage du contenu de la colonne
     *
     * @param string $content Contenu de la colonne
     * @param string $column_name Identification de la colonne
     * @param object $item Attributs de l'objet courant
     *
     * @return string
     */
    public function content($output, $column_name, $item = null)
    {
        _e('Pas de données à afficher', 'tify');
    }
}