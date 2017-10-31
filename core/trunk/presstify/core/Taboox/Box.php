<?php
namespace tiFy\Core\Taboox;

class Box extends \tiFy\Core\Taboox\Factory
{
    /**
     * CONTROLEURS
     */
    /**
     * Traitement des arguments de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    protected function parseAttrs($attrs = [])
    {
        $defaults = [
            'id'            => null,
            'title'         => '',
            'page'          => '',
            'object'        => null,
            'object_type'   => null
        ];
        return \wp_parse_args($attrs, $defaults);
    }
}