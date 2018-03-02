<?php

namespace tiFy\Core\Column\PostType\Subtitle;

use tiFy\Core\Column\ColumnPostType;

class Subtitle extends ColumnPostType
{
    /**
     * Récupération de l'intitulé de la colonne
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Title ? : __('Sous-titre', 'tify');
    }

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
        if ($subtitle = get_post_meta($post_id, '_subtitle', true)) :
            echo $subtitle;
        else :
            echo "<em style=\"color:#AAA;\">" . __('Aucun', 'tify') . "</em>";
        endif;
    }
}