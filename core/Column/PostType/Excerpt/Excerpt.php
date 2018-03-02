<?php

namespace tiFy\Core\Column\PostType\Excerpt;

use tiFy\Core\Column\ColumnPostType;

class Excerpt extends ColumnPostType
{
    /**
     * Récupération de l'intitulé de la colonne
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Title ? : __('Extrait', 'tify');
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
        if ($post = get_post($post_id)) :
            echo $post->post_excerpt;
        endif;
    }
}