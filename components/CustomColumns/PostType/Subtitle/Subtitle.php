<?php
namespace tiFy\Components\CustomColumns\PostType\Subtitle;

class Subtitle extends \tiFy\Components\CustomColumns\PostType
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Sous-titre', 'tify'),
            'position' => 3
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
        if ($subtitle = get_post_meta($post_id, '_subtitle', true)) :
            echo $subtitle;
        else :
            echo "<em style=\"color:#AAA;\">" . __('Aucun', 'tify') . "</em>";
        endif;
    }
}