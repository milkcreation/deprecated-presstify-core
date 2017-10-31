<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Icon;

class Icon extends \tiFy\Components\CustomColumns\Taxonomy
{
    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [
            'title'    => __('Icone', 'tify'),
            'position' => 1,
            'name'     => '_icon',
            'dir'      => \tiFy\tiFy::$AbsDir . '/vendor/Assets/svg'
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
        if (($icon = get_term_meta($term_id, $this->getAttr('name'), true)) && file_exists($this->getAttr('dir') . "/{$icon}") && ($data = file_get_contents($this->getAttr('dir') . "/{$icon}"))) :
            echo "<img src=\"data:image/svg+xml;base64," . base64_encode($data) . "\" width=\"80\" height=\"80\" />";
        endif;
    }
}