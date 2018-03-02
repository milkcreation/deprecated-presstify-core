<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Color;

class Color extends \tiFy\Components\CustomColumns\Taxonomy
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Couleur', 'tify'),
            'position' => 1
        ];
    }

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
        if ($color = get_term_meta($term_id, '_color', true)) :
            echo "<div style=\"width:80px;height:80px;display:block;border:solid 1px #CCC;background-color:#F4F4F4;position:relative;\"><div style=\"position:absolute;top:5px;right:5px;bottom:5px;left:5px;background-color:{$color}\"></div></div>";
        endif;
    }
}