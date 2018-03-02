<?php

namespace tiFy\Core\Column\PostType\MenuOrder;

use tiFy\Core\Column\ColumnPostType;

class MenuOrder extends ColumnPostType
{
    /**
     * Récupération de l'intitulé de la colonne
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Title ?: __('Ordre d\'affich.', 'tify');
    }

    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column_name Identifiant de qualification de la colonne
     * @param int $post_id Identifiant du post
     *
     * @return string
     */
    public function content($column, $post_id)
    {
        $level = 0;
        $post = get_post($post_id);

        if (0 == $level && (int)$post->post_parent > 0) :
            $find_main_page = (int)$post->post_parent;
            while ($find_main_page > 0) :
                $parent = get_post($find_main_page);

                if (is_null($parent)) :
                    break;
                endif;

                $level++;
                $find_main_page = (int)$parent->post_parent;
            endwhile;
        endif;
        $_level = "";

        for ($i = 0; $i < $level; $i++) :
            $_level .= "<strong>&mdash;</strong> ";
        endfor;

        echo $_level . $post->menu_order;
    }
}