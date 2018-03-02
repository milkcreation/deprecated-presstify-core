<?php
namespace tiFy\Components\CustomColumns\PostType\Excerpt;

class Excerpt extends \tiFy\Components\CustomColumns\PostType
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Extrait', 'tify'),
            'position' => 2
        ];
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