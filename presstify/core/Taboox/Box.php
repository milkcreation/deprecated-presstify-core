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
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *
     * }
     * @return array
     */
    protected function parseAttrs($attrs = [])
    {
        $defaults = [
            'id'            => null,
            'title'         => '',
            'object_type'   => null,
            'object_name'   => null
        ];

        // Rétrocompatibilité
        if (isset($attrs['page'])) :
            $attrs['object_name'] = $attrs['page'];
        endif;

        return \wp_parse_args($attrs, $defaults);
    }
}