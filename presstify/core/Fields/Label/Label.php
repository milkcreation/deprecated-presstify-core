<?php
namespace tiFy\Core\Fields\Label;

class Label extends \tiFy\Core\Fields\Factory
{
    /**
     * Affichage
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args {
     *      Liste des attributs de configuration du champ
     *
     *      @param string $before Contenu placé avant le champ
     *      @param string $after Contenu placé après le champ
     *      @param string $content Contenu de la balise champ
     *      @param array $attrs {
     *          Liste des attributs de balise
     *
     *      }
     * }
     *
     * @return string
     */
    public static function display($id = null, $args = [])
    {
        static::$Instance++;

        $defaults = [
            'before'       => '',
            'after'        => '',
            'content'      => '',
            'attrs'        => [
                'id'    => 'tiFyCoreFields-Label--' . static::$Instance,
            ]
        ];
        $args = \wp_parse_args($args, $defaults);

        // Instanciation
        $field = new static($id, $args);

        ?><?php $field->before(); ?><label <?php $field->htmlAttrs(); ?>/><?php $field->tagContent(); ?></label><?php $field->after(); ?><?php
    }
}